# Quick Reference Guide - RazaStar Project

## API Base URLs

### Customer API
```
Base URL: https://erestro.me/app/v1/api/{METHOD_NAME}
Example: https://erestro.me/app/v1/api/login
```

### Rider API
```
Base URL: https://erestro.me/rider/app/v1/api/{METHOD_NAME}
Example: https://erestro.me/rider/app/v1/api/login
```

### Partner API
```
Base URL: https://erestro.me/partner/app/v1/api/{METHOD_NAME}
```

---

## Admin Panel URLs

- **Admin Dashboard:** `/admin` → `admin/home`
- **Partner Panel:** `/partner` → `partner/home`
- **Rider Panel:** `/rider` → `rider/home`

---

## Key File Locations

### Controllers
- **Main Customer API:** `application/controllers/app/v1/Api.php` (7400+ lines)
- **Rider API:** `application/controllers/rider/app/v1/Api.php`
- **Partner API:** `application/controllers/partner/app/v1/Api.php`

### Configuration
- **Main Config:** `application/config/config.php`
- **Database:** `application/config/database.php`
- **Routes:** `application/config/routes.php`
- **App Config:** `application/config/erestro.php`

### Entry Point
- **Main Entry:** `index.php`

---

## Database Connection

```php
Host: localhost
Database: u320334719_razastar
Username: u320334719_razastar
Driver: MySQLi
Charset: utf8
```

---

## Payment Gateways Supported

1. PayPal
2. Stripe
3. Razorpay
4. Paytm
5. Paystack
6. Flutterwave
7. Midtrans
8. PhonePe
9. Cash on Delivery (COD)

---

## Order Status Flow

```
pending → confirmed → preparing → out_for_delivery → delivered
         ↓
      cancelled
```

---

## Key Models

- `Order_model.php` - Orders
- `Product_model.php` - Products
- `Customer_model.php` - Customers
- `Partner_model.php` - Restaurants/Partners
- `Rider_model.php` - Delivery Riders
- `Cart_model.php` - Shopping Cart
- `Transaction_model.php` - Payments
- `Rating_model.php` - Reviews/Ratings
- `Notification_model.php` - Push Notifications

---

## Common API Endpoints

### Authentication
- `POST /app/v1/api/login`
- `POST /app/v1/api/register_user`
- `POST /app/v1/api/verify_user`
- `POST /app/v1/api/verify_otp`

### Products
- `POST /app/v1/api/get_products`
- `POST /app/v1/api/get_categories`
- `POST /app/v1/api/get_partners`
- `POST /app/v1/api/search_product`

### Cart & Orders
- `POST /app/v1/api/manage_cart`
- `POST /app/v1/api/get_user_cart`
- `POST /app/v1/api/place_order`
- `POST /app/v1/api/get_orders`
- `POST /app/v1/api/re_order`

### User Management
- `POST /app/v1/api/update_user`
- `POST /app/v1/api/add_address`
- `POST /app/v1/api/get_address`

### Payments
- `POST /app/v1/api/payment_intent`
- `POST /app/v1/api/make_payments`
- `POST /app/v1/api/validate_promo_code`

---

## Environment Variables

- `ENVIRONMENT` - Set via `$_SERVER['CI_ENV']` (default: 'development')
- `base_url` - Configured in `config.php` as `https://admin.razastar.in/`

---

## Security

- **JWT Authentication** for protected API routes
- **Excluded Routes** defined in `$excluded_routes` array
- **CORS Headers** configured for API endpoints
- **OTP Verification** for user registration

---

## File Upload Types

Configured in `erestro.php`:
- **Images:** jpg, jpeg, png, gif, bmp, eps
- **Videos:** mp4, 3gp, avchd, avi, flv, mkv, mov, webm, wmv, mpg, mpeg, ogg
- **Documents:** doc, docx, txt, pdf, ppt, pptx
- **Spreadsheets:** xls, xsls
- **Archives:** zip, 7z, bz2, gz, gzip, rar, tar

---

## System Modules (Admin)

Configured in `erestro.php`:
- orders, categories, product, media, tax, attribute
- home_slider_images, promo_code, featured_section
- customers, rider, fund_transfer, send_notification
- client_api_keys, city, faq, support_tickets
- settings, partner, waiter, tags, payment_request

---

## Supported Locales

100+ locales supported with currency mapping (see `erestro.php`)

---

## Git Branches

- `main` - Main branch
- `cursor/analyse-project-structure-1b20` - Analysis branch

---

## Important Notes

1. **Large API Controller:** Main API controller (`app/v1/Api.php`) is 7400+ lines - consider splitting
2. **Database Credentials:** Stored in config file (should use environment variables)
3. **Environment:** Currently set to 'development' - change for production
4. **Cache:** Database query caching is disabled (`cache_on => FALSE`)
