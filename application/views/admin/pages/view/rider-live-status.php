<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Rider Live Status Monitor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('admin/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Rider Live Status</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="total-riders">0</h3>
                            <p>Total Riders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="active-riders">0</h3>
                            <p>Active Riders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="online-riders">0</h3>
                            <p>Online Now</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="active-orders">0</h3>
                            <p>Active Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map and List View Toggle -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary active" id="map-view-btn">
                            <i class="fas fa-map"></i> Map View
                        </button>
                        <button type="button" class="btn btn-secondary" id="list-view-btn">
                            <i class="fas fa-list"></i> List View
                        </button>
                    </div>
                    <button type="button" class="btn btn-success float-right" id="refresh-btn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <span class="float-right mr-2" id="last-update">Last updated: Never</span>
                </div>
            </div>

            <!-- Map View -->
            <div class="row" id="map-container">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-map-marked-alt"></i> Rider Locations Map</h3>
                        </div>
                        <div class="card-body">
                            <div id="rider-map" style="height: 600px; width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View -->
            <div class="row" id="list-container" style="display: none;">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Rider Status List</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="riders-table">
                                    <thead>
                                        <tr>
                                            <th>Rider</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                            <th>Active Orders</th>
                                            <th>Today Deliveries</th>
                                            <th>Rating</th>
                                            <th>Last Update</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="riders-tbody">
                                        <tr>
                                            <td colspan="8" class="text-center">Loading riders data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Rider Details Modal -->
<div class="modal fade" id="rider-details-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rider Details & Active Orders</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="rider-details-content">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
let map;
let markers = [];
let riderData = [];
let updateInterval;

$(document).ready(function() {
    // Initialize map
    initMap();
    
    // Load initial data
    loadRiderData();
    
    // Set up auto-refresh every 10 seconds
    updateInterval = setInterval(loadRiderData, 10000);
    
    // View toggle
    $('#map-view-btn').on('click', function() {
        $(this).addClass('active').removeClass('btn-secondary').addClass('btn-primary');
        $('#list-view-btn').removeClass('active').removeClass('btn-primary').addClass('btn-secondary');
        $('#map-container').show();
        $('#list-container').hide();
        if (map) {
            setTimeout(function() { map.invalidateSize(); }, 100);
        }
    });
    
    $('#list-view-btn').on('click', function() {
        $(this).addClass('active').removeClass('btn-secondary').addClass('btn-primary');
        $('#map-view-btn').removeClass('active').removeClass('btn-primary').addClass('btn-secondary');
        $('#map-container').hide();
        $('#list-container').show();
    });
    
    // Manual refresh
    $('#refresh-btn').on('click', function() {
        loadRiderData();
    });
});

function initMap() {
    const googleMapApiKey = '<?= isset($google_map_api_key) && !empty($google_map_api_key) ? $google_map_api_key : "" ?>';
    
    if (!googleMapApiKey) {
        $('#rider-map').html('<div class="alert alert-warning">Google Maps API key not configured. Please configure it in System Settings.</div>');
        return;
    }
    
    // Load Google Maps script
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=${googleMapApiKey}&callback=initGoogleMap`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    } else {
        initGoogleMap();
    }
}

function initGoogleMap() {
    map = new google.maps.Map(document.getElementById('rider-map'), {
        zoom: 12,
        center: { lat: 23.0225, lng: 72.5714 }, // Default to Ahmedabad, India
        mapTypeId: 'roadmap'
    });
}

function loadRiderData() {
    $.ajax({
        url: '<?= base_url("admin/riders/get_live_riders") ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.error === false) {
                riderData = response.data;
                updateStatistics(response);
                updateMap();
                updateList();
                $('#last-update').text('Last updated: ' + new Date().toLocaleTimeString());
            } else {
                console.error('Error loading rider data:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
}

function updateStatistics(data) {
    $('#total-riders').text(data.total_riders || 0);
    $('#active-riders').text(data.active_riders || 0);
    $('#online-riders').text(data.online_riders || 0);
    
    let totalActiveOrders = 0;
    riderData.forEach(function(rider) {
        totalActiveOrders += rider.active_orders_count || 0;
    });
    $('#active-orders').text(totalActiveOrders);
}

function updateMap() {
    if (!map) return;
    
    // Clear existing markers
    markers.forEach(function(marker) {
        marker.setMap(null);
    });
    markers = [];
    
    if (riderData.length === 0) return;
    
    // Calculate center based on rider locations
    let bounds = new google.maps.LatLngBounds();
    let hasLocation = false;
    
    riderData.forEach(function(rider) {
        if (rider.current_location && rider.current_location.latitude && rider.current_location.longitude) {
            const position = {
                lat: parseFloat(rider.current_location.latitude),
                lng: parseFloat(rider.current_location.longitude)
            };
            
            bounds.extend(position);
            hasLocation = true;
            
            // Create marker
            const marker = new google.maps.Marker({
                position: position,
                map: map,
                title: rider.name,
                icon: {
                    url: rider.is_online ? 'http://maps.google.com/mapfiles/ms/icons/green-dot.png' : 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
                    scaledSize: new google.maps.Size(32, 32)
                }
            });
            
            // Info window
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="min-width: 200px;">
                        <h6><strong>${rider.name}</strong></h6>
                        <p><strong>Mobile:</strong> ${rider.mobile}</p>
                        <p><strong>Status:</strong> <span class="badge badge-${rider.status === 'active' ? 'success' : 'secondary'}">${rider.status}</span></p>
                        <p><strong>Online:</strong> <span class="badge badge-${rider.is_online ? 'success' : 'danger'}">${rider.is_online ? 'Yes' : 'No'}</span></p>
                        <p><strong>Active Orders:</strong> ${rider.active_orders_count}</p>
                        <p><strong>Today Deliveries:</strong> ${rider.today_deliveries}</p>
                        <p><strong>Rating:</strong> ${rider.rating} (${rider.total_ratings} reviews)</p>
                        <button class="btn btn-sm btn-primary mt-2" onclick="showRiderDetails(${rider.rider_id})">View Details</button>
                    </div>
                `
            });
            
            marker.addListener('click', function() {
                infoWindow.open(map, marker);
            });
            
            markers.push(marker);
        }
    });
    
    // Fit bounds if we have locations
    if (hasLocation) {
        map.fitBounds(bounds);
    } else {
        // Default location if no riders have location
        map.setCenter({ lat: 23.0225, lng: 72.5714 });
        map.setZoom(12);
    }
}

function updateList() {
    const tbody = $('#riders-tbody');
    tbody.empty();
    
    if (riderData.length === 0) {
        tbody.append('<tr><td colspan="8" class="text-center">No riders found</td></tr>');
        return;
    }
    
    riderData.forEach(function(rider) {
        const locationText = rider.current_location 
            ? `${rider.current_location.latitude}, ${rider.current_location.longitude}`
            : 'Not available';
        
        const lastUpdate = rider.current_location && rider.current_location.last_update
            ? new Date(rider.current_location.last_update).toLocaleString()
            : 'Never';
        
        const row = `
            <tr>
                <td>
                    <img src="${rider.image}" alt="${rider.name}" class="img-circle" style="width: 40px; height: 40px; margin-right: 10px;">
                    <strong>${rider.name}</strong><br>
                    <small>${rider.mobile}</small>
                </td>
                <td>
                    <span class="badge badge-${rider.status === 'active' ? 'success' : 'secondary'}">${rider.status}</span><br>
                    <span class="badge badge-${rider.is_online ? 'success' : 'danger'}">${rider.is_online ? 'Online' : 'Offline'}</span>
                </td>
                <td>
                    ${locationText}<br>
                    <small class="text-muted">${rider.city}</small>
                </td>
                <td><span class="badge badge-info">${rider.active_orders_count}</span></td>
                <td><span class="badge badge-success">${rider.today_deliveries}</span></td>
                <td>
                    ${rider.rating} <i class="fas fa-star text-warning"></i><br>
                    <small>${rider.total_ratings} reviews</small>
                </td>
                <td><small>${lastUpdate}</small></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="showRiderDetails(${rider.rider_id})">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function showRiderDetails(riderId) {
    $.ajax({
        url: '<?= base_url("admin/riders/get_rider_tracking") ?>/' + riderId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.error === false) {
                let content = `
                    <div class="row">
                        <div class="col-md-4">
                            <img src="${response.rider.image}" alt="${response.rider.name}" class="img-circle" style="width: 100px; height: 100px;">
                            <h5>${response.rider.name}</h5>
                            <p><strong>Mobile:</strong> ${response.rider.mobile}</p>
                            <p><strong>Email:</strong> ${response.rider.email}</p>
                            <p><strong>City:</strong> ${response.rider.city}</p>
                        </div>
                        <div class="col-md-8">
                            <h6>Active Orders (${response.orders.length})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Status</th>
                                            <th>Customer</th>
                                            <th>Location</th>
                                            <th>Last Update</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;
                
                if (response.orders.length > 0) {
                    response.orders.forEach(function(order) {
                        const location = order.current_latitude && order.current_longitude
                            ? `${order.current_latitude}, ${order.current_longitude}`
                            : 'Not available';
                        
                        content += `
                            <tr>
                                <td>#${order.order_number || order.order_id}</td>
                                <td><span class="badge badge-info">${order.active_status}</span></td>
                                <td>${order.customer_name}<br><small>${order.customer_mobile}</small></td>
                                <td><small>${location}</small></td>
                                <td><small>${order.last_update ? new Date(order.last_update).toLocaleString() : 'Never'}</small></td>
                            </tr>
                        `;
                    });
                } else {
                    content += '<tr><td colspan="5" class="text-center">No active orders</td></tr>';
                }
                
                content += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#rider-details-content').html(content);
                $('#rider-details-modal').modal('show');
            }
        }
    });
}

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});
</script>
