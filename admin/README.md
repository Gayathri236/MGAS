# Microgreens Admin Panel

A modern, responsive web-based Admin Panel for managing a Microgreens Business.

## Features

- **Dashboard** - Business overview with key metrics and charts
- **Product Management** - CRUD operations with image upload
- **Customer Management** - View, edit, and block customers
- **Order Management** - Track orders, update status, generate tracking
- **Inventory Management** - Stock tracking with low stock alerts
- **Delivery Management** - Schedule and track deliveries
- **Reports & Analytics** - Sales reports, product analytics, customer insights

## Tech Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Charts**: Chart.js

## Installation

### 1. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE microgreens_admin;
```

2. Import the database schema:
```bash
mysql -u root -p microgreens_admin < database.sql
```

### 2. Configuration

Edit `config/database.php` to set your database credentials:
```php
private $host = 'localhost';
private $db_name = 'microgreens_admin';
private $username = 'root';
private $password = 'your_password';
```

### 3. Web Server

Point your web server (Apache/Nginx) to the project root directory.

For local development with PHP's built-in server:
```bash
php -S localhost:8000
```

### 4. Default Login

- **Email**: admin@microgreens.com
- **Password**: password

*Note: This is the bcrypt hash for "password". Change it after first login for security.*

## Project Structure

```
├── index.html           # Entry point (redirects to login)
├── login.html           # Admin login page
├── dashboard.html       # Main dashboard
├── products.html        # Product management
├── customers.html       # Customer management
├── orders.html         # Order management
├── inventory.html      # Inventory management
├── delivery.html        # Delivery management
├── reports.html         # Reports & analytics
├── css/
│   └── style.css        # Main stylesheet
├── js/
│   └── app.js           # Shared JavaScript functions
├── config/
│   ├── database.php     # Database connection
│   └── functions.php    # Helper functions
├── api/
│   ├── auth.php         # Authentication API
│   ├── dashboard.php    # Dashboard API
│   ├── products.php     # Products API
│   ├── customers.php    # Customers API
│   ├── orders.php       # Orders API
│   ├── inventory.php    # Inventory API
│   ├── delivery.php     # Delivery API
│   └── reports.php      # Reports API
├── uploads/             # Uploaded images
├── database.sql         # Database schema
└── SPEC.md              # Project specification
```

## Usage

1. Start your web server and database
2. Navigate to the application URL
3. Login with admin credentials
4. Start managing your microgreens business!

## API Endpoints

All API endpoints return JSON and require authentication (except login).

### Authentication
- `POST /api/auth.php?action=login`
- `POST /api/auth.php?action=logout`
- `GET /api/auth.php?action=check`

### Dashboard
- `GET /api/dashboard.php?action=stats`
- `GET /api/dashboard.php?action=recent_orders`
- `GET /api/dashboard.php?action=sales_chart`

### Products
- `GET /api/products.php?action=list`
- `GET /api/products.php?action=get&id=X`
- `POST /api/products.php?action=create`
- `PUT /api/products.php?action=update&id=X`
- `DELETE /api/products.php?action=delete&id=X`

### Orders
- `GET /api/orders.php?action=list`
- `GET /api/orders.php?action=get&id=X`
- `PUT /api/orders.php?action=status&id=X`

## Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- Session-based authentication
- Input sanitization
- Protected API endpoints

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

MIT License
