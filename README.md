# PHP_Laravel12_Tracking_System

A simple and practical URL tracking and analytics system built with **Laravel 12**. This project allows you to create trackable URLs, record click details, and analyze traffic such as device, browser, platform, IP address, and geolocation.

This project is suitable for learning, interviews, college submissions, and portfolio use.

---

## Features

* Create trackable URLs with unique slugs
* Redirect users to original URLs
* Track total clicks per link
* Record click details:

  * IP address
  * Browser
  * Device type
  * Platform
  * Referrer
  * Country and city (IP-based)
* View click history with pagination
* Clean Bootstrap-based UI
* Copy-to-clipboard tracking links
* Dashboard-style statistics per link

---

## Tech Stack

* PHP 8.1+
* Laravel 12
* MySQL
* Bootstrap 5
* Browser Detect Package
* Free IP Geolocation API

---

## Installation Steps

### Step 1: Create New Laravel Project

```bash
composer create-project laravel/laravel LaravelTrackingSystem
cd LaravelTrackingSystem
```

---

### Step 2: Configure Database

Edit `.env` file:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tracking_system
DB_USERNAME=root
DB_PASSWORD=
```

---

### Step 3: Run Migrations

```bash
php artisan migrate
```

---

## Database Structure

### tracking_links Table

* id
* name
* original_url
* slug (unique)
* click_count
* created_at
* updated_at

### clicks Table

* id
* tracking_link_id (foreign key)
* ip_address
* user_agent
* referrer
* country
* city
* device
* browser
* platform
* created_at
* updated_at

---

## Models & Relationships

* **TrackingLink**

  * hasMany Clicks
  * generates tracking URL
  * increments click count

* **Click**

  * belongsTo TrackingLink

---

## Routes

```php
Route::get('/', function () {
    return view('welcome');
});

Route::resource('tracking-links', TrackingLinkController::class)
    ->except(['edit', 'update']);

Route::get('/track/{slug}', [ClickController::class, 'track'])
    ->name('tracking.click');
```

---

## How Tracking Works

1. User clicks the tracking URL
2. System finds the slug
3. Click data is recorded
4. Click count is incremented
5. User is redirected to original URL

---

## Click Analytics Captured

* Total clicks
* Unique IP addresses
* Browser statistics
* Device types
* Platform usage
* Country and city
* Date and time of clicks

---

## User Interface Pages

* Home page (project overview)
* Tracking links list
* Create new tracking link
* Tracking link detail page
* Click history table

---

## Browser Detection

This project uses:

```bash
composer require hisorange/browser-detect
```

To detect:

* Device type
* Browser name
* Platform

---

## Geolocation

IP-based location is fetched using a free API:

* ip-api.com

Note: Location data may not be available for localhost (127.0.0.1).

---

## Running the Application

```bash
php artisan key:generate
php artisan serve
```

Visit:

```
http://localhost:8000
```
---
## Screenshot
<img width="1572" height="678" alt="image" src="https://github.com/user-attachments/assets/9662f6bb-6ddf-451e-8a09-3a6a6cbea242" />
<img width="1633" height="629" alt="image" src="https://github.com/user-attachments/assets/1538cda5-af26-46ea-991d-7161987b6681" />
<img width="1627" height="622" alt="image" src="https://github.com/user-attachments/assets/719e69bb-8036-4f03-9d16-70cc8233a883" />


---

## Possible Enhancements

* Authentication (Admin dashboard)
* Charts using Chart.js
* URL expiration
* QR code generation
* CSV export of click data
* API-based tracking
* Rate limiting

---

## Learning Outcomes

* Laravel CRUD operations
* One-to-many relationships
* Request validation
* Middleware and routing
* Click tracking logic
* Analytics data modeling
* Real-world Laravel project structure

---

## Author

**Mihir Mehta**
PHP Laravel Developer

---

## License

This project is open-source and free to use for learning and educational purposes.

