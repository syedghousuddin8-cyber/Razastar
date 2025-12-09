<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rider_live_status extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library(['ion_auth', 'form_validation']);
        $this->load->helper(['url', 'language', 'function_helper']);
        
        if (!has_permissions('read', 'rider')) {
            $this->session->set_flashdata('authorize_flag', PERMISSION_ERROR_MSG);
            redirect('admin/home', 'refresh');
        }
    }

    /**
     * Rider Live Status Dashboard
     * Main page to monitor all riders in real-time
     */
    public function index()
    {
        if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
            $this->data['main_page'] = VIEW . 'rider-live-status';
            $settings = get_settings('system_settings', true);
            $this->data['title'] = 'Rider Live Status | ' . $settings['app_name'];
            $this->data['meta_description'] = 'Monitor Riders Live Status | ' . $settings['app_name'];
            $this->data['currency'] = get_settings('currency');
            $this->data['google_map_api_key'] = isset($settings['google_map_javascript_api_key']) ? $settings['google_map_javascript_api_key'] : '';
            $this->load->view('admin/template', $this->data);
        } else {
            redirect('admin/login', 'refresh');
        }
    }

    /**
     * Fetch all active riders with their live status
     * Returns JSON data for AJAX requests
     */
    public function get_live_riders()
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            $this->response['error'] = true;
            $this->response['message'] = "Unauthorized access";
            print_r(json_encode($this->response));
            return;
        }

        // Get all active riders
        $riders = $this->db->select('u.id, u.username, u.mobile, u.email, u.image, u.active, u.rating, u.no_of_ratings, u.balance, c.name as city_name')
            ->from('users u')
            ->join('users_groups ug', 'ug.user_id = u.id', 'left')
            ->join('cities c', 'c.id = u.serviceable_city', 'left')
            ->where('ug.group_id', '3')
            ->where_in('u.active', ['0', '1'])
            ->get()
            ->result_array();

        $rider_data = [];
        
        foreach ($riders as $rider) {
            // Get current active orders for this rider
            $active_orders = $this->db->select('o.id as order_id, o.active_status, o.final_total, o.date_added, o.address, o.latitude, o.longitude, 
                                                lt.latitude as current_latitude, lt.longitude as current_longitude, lt.order_status as tracking_status, lt.date_created as last_update')
                ->from('orders o')
                ->join('live_tracking lt', 'lt.order_id = o.id', 'left')
                ->where('o.rider_id', $rider['id'])
                ->where_in('o.active_status', ['confirmed', 'preparing', 'out_for_delivery', 'ready_for_pickup'])
                ->order_by('lt.date_created', 'DESC')
                ->get()
                ->result_array();

            // Get rider statistics
            $total_orders = $this->db->where('rider_id', $rider['id'])->where('active_status', 'delivered')->count_all_results('orders');
            $today_orders = $this->db->where('rider_id', $rider['id'])->where('active_status', 'delivered')->where('DATE(date_added)', date('Y-m-d'))->count_all_results('orders');
            
            // Get latest location from live tracking
            $latest_location = null;
            if (!empty($active_orders)) {
                foreach ($active_orders as $order) {
                    if (!empty($order['current_latitude']) && !empty($order['current_longitude'])) {
                        $latest_location = [
                            'latitude' => $order['current_latitude'],
                            'longitude' => $order['current_longitude'],
                            'last_update' => $order['last_update'],
                            'order_id' => $order['order_id']
                        ];
                        break; // Get the most recent one
                    }
                }
            }

            $rider_data[] = [
                'rider_id' => $rider['id'],
                'name' => $rider['username'],
                'mobile' => $rider['mobile'],
                'email' => $rider['email'],
                'image' => !empty($rider['image']) ? base_url() . USER_IMG_PATH . $rider['image'] : base_url() . NO_PROFILE_IMAGE,
                'status' => $rider['active'] == '1' ? 'active' : 'inactive',
                'city' => $rider['city_name'] ?? 'N/A',
                'rating' => $rider['rating'] ?? '0',
                'total_ratings' => $rider['no_of_ratings'] ?? '0',
                'balance' => $rider['balance'] ?? '0',
                'active_orders_count' => count($active_orders),
                'active_orders' => $active_orders,
                'total_deliveries' => $total_orders,
                'today_deliveries' => $today_orders,
                'current_location' => $latest_location,
                'is_online' => !empty($latest_location) && strtotime($latest_location['last_update']) > (time() - 300) // Online if updated in last 5 minutes
            ];
        }

        $this->response['error'] = false;
        $this->response['message'] = "Rider live status fetched successfully";
        $this->response['data'] = $rider_data;
        $this->response['total_riders'] = count($rider_data);
        $this->response['active_riders'] = count(array_filter($rider_data, function($r) { return $r['status'] == 'active'; }));
        $this->response['online_riders'] = count(array_filter($rider_data, function($r) { return $r['is_online']; }));
        
        print_r(json_encode($this->response));
    }

    /**
     * Get specific rider's live tracking details
     */
    public function get_rider_tracking($rider_id = null)
    {
        if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
            $this->response['error'] = true;
            $this->response['message'] = "Unauthorized access";
            print_r(json_encode($this->response));
            return;
        }

        if (empty($rider_id)) {
            $rider_id = $this->input->post('rider_id', true);
        }

        if (empty($rider_id)) {
            $this->response['error'] = true;
            $this->response['message'] = "Rider ID is required";
            print_r(json_encode($this->response));
            return;
        }

        // Get rider details
        $rider = $this->db->select('u.id, u.username, u.mobile, u.email, u.image, c.name as city_name')
            ->from('users u')
            ->join('users_groups ug', 'ug.user_id = u.id', 'left')
            ->join('cities c', 'c.id = u.serviceable_city', 'left')
            ->where('ug.group_id', '3')
            ->where('u.id', $rider_id)
            ->get()
            ->row_array();

        if (empty($rider)) {
            $this->response['error'] = true;
            $this->response['message'] = "Rider not found";
            print_r(json_encode($this->response));
            return;
        }

        // Get all active orders with live tracking
        $orders = $this->db->select('o.id as order_id, o.order_number, o.active_status, o.final_total, o.date_added, o.address, 
                                     o.latitude as delivery_latitude, o.longitude as delivery_longitude,
                                     lt.latitude as current_latitude, lt.longitude as current_longitude, 
                                     lt.order_status as tracking_status, lt.date_created as last_update,
                                     u.username as customer_name, u.mobile as customer_mobile')
            ->from('orders o')
            ->join('live_tracking lt', 'lt.order_id = o.id', 'left')
            ->join('users u', 'u.id = o.user_id', 'left')
            ->where('o.rider_id', $rider_id)
            ->where_in('o.active_status', ['confirmed', 'preparing', 'out_for_delivery', 'ready_for_pickup'])
            ->order_by('lt.date_created', 'DESC')
            ->get()
            ->result_array();

        $this->response['error'] = false;
        $this->response['message'] = "Rider tracking details fetched successfully";
        $this->response['rider'] = [
            'id' => $rider['id'],
            'name' => $rider['username'],
            'mobile' => $rider['mobile'],
            'email' => $rider['email'],
            'image' => !empty($rider['image']) ? base_url() . USER_IMG_PATH . $rider['image'] : base_url() . NO_PROFILE_IMAGE,
            'city' => $rider['city_name'] ?? 'N/A'
        ];
        $this->response['orders'] = $orders;
        
        print_r(json_encode($this->response));
    }
}
