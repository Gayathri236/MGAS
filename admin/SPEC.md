# Microgreens Admin Panel - Project Specification

## 1. Concept & Vision

A professional-grade admin panel for managing a microgreens business operations. The system embodies freshness and growth through its vibrant green color palette, while maintaining a clean, business-focused interface that prioritizes efficiency and clarity. The experience should feel modern, responsive, and empowering for business operators.

## 2. Design Language

### Aesthetic Direction
Fresh, organic, modern - inspired by the microgreens themselves. Clean lines with subtle organic touches.

### Color Palette
- **Primary Green**: `#22c55e` (vibrant leaf green)
- **Secondary Green**: `#16a34a` (deep forest)
- **Accent Yellow**: `#facc15` (sunny sprout)
- **Light Green**: `#dcfce7` (soft mint)
- **Background**: `#f0fdf4` (pale green tint)
- **Dark Text**: `#1e293b` (slate)
- **White**: `#ffffff`
- **Danger**: `#ef4444` (red for alerts)
- **Warning**: `#f59e0b` (orange for warnings)

### Typography
- **Headings**: 'Poppins', sans-serif (600-700 weight)
- **Body**: 'Inter', sans-serif (400-500 weight)
- **Monospace**: 'JetBrains Mono' for numbers/data

### Spatial System
- Base unit: 8px
- Card padding: 24px
- Section gaps: 32px
- Border radius: 12px (cards), 8px (buttons), 6px (inputs)

### Motion Philosophy
- Page transitions: fade-in 300ms ease-out
- Button hover: scale(1.02) + shadow lift
- Card hover: translateY(-2px) + shadow
- Sidebar: slide-in 250ms ease
- Modals: scale(0.95→1) + fade 200ms

## 3. Layout & Structure

### Overall Structure
- **Login Page**: Centered card on gradient background
- **Main Application**: Sidebar navigation (collapsible) + main content area
- **Header**: Logo, search bar, notifications, profile dropdown
- **Content Area**: Responsive grid layouts, cards for data display

### Responsive Strategy
- Desktop: Full sidebar visible (240px)
- Tablet: Collapsible sidebar (icon-only mode)
- Mobile: Bottom navigation or hamburger menu

## 4. Features & Interactions

### Authentication
- Secure login with email/password
- Session management with PHP sessions
- Password hashing with bcrypt
- Remember me functionality
- Logout with session destruction

### Dashboard
- KPI cards: Today's Sales, Total Orders, Customers, Low Stock Items
- Recent orders table (last 10)
- Sales chart (last 7 days)
- Top selling products
- Quick action buttons

### Product Management
- Product list with search/filter
- Add new product form: name, description, price, category, stock, image upload
- Edit product modal
- Delete with confirmation
- Image preview on upload
- Categories: Microgreens, Sprouts, Edible Flowers, Mixes

### Customer Management
- Customer list with search
- View customer details
- Edit customer info
- Block/Unblock customer
- Order history per customer

### Order Management
- Order list with filters (status, date)
- Order status workflow: Pending → Processing → Shipped → Delivered
- Generate tracking link
- Generate PDF bill
- Add manual order
- Order details modal

### Inventory Management
- Stock levels display
- Low stock alerts (< 10 units)
- Quick stock update
- Batch update capability
- Stock history log

### Delivery Management
- Delivery calendar/schedule view
- Assign delivery dates
- Delivery status tracking
- Driver assignment (optional)

### Reports
- Monthly sales report
- Best-selling products chart
- Customer analytics
- Export to CSV
- Date range selector

## 5. Component Inventory

### Cards
- Default: white bg, shadow-sm, rounded-xl
- Hover: shadow-md, translateY(-2px)
- Header variant: colored top border

### Buttons
- Primary: bg-green-600, text-white, hover:bg-green-700
- Secondary: bg-white, border, hover:bg-gray-50
- Danger: bg-red-500, hover:bg-red-600
- Icon buttons: 40x40px, rounded-lg

### Tables
- Striped rows, hover highlight
- Sortable columns
- Pagination controls
- Empty state message

### Forms
- Input: full-width, border-gray-300, focus:ring-green-500
- Labels: font-medium, text-gray-700
- Error states: border-red-500, error message below

### Modals
- Overlay: bg-black/50
- Content: white, max-w-lg, centered
- Close button: top-right
- Actions: bottom-right aligned

### Sidebar Navigation
- Active item: bg-green-100, border-left-green
- Hover: bg-gray-100
- Icons: 20px, inline

### Notifications/Alerts
- Success: bg-green-100, border-green
- Error: bg-red-100, border-red
- Warning: bg-yellow-100, border-yellow

## 6. Technical Approach

### Frontend
- HTML5 semantic markup
- CSS3 with custom properties
- Vanilla JavaScript (ES6+)
- Chart.js for analytics
- No framework dependencies

### Backend (PHP)
- PDO for MySQL connections
- RESTful API structure
- JSON responses
- Session-based auth
- Prepared statements (SQL injection prevention)

### Database (MySQL)
Tables:
- `admins` - Admin users
- `products` - Product catalog
- `categories` - Product categories
- `customers` - Customer accounts
- `orders` - Customer orders
- `order_items` - Order line items
- `inventory` - Stock tracking
- `deliveries` - Delivery schedules

### API Endpoints
```
POST /api/auth/login
POST /api/auth/logout
GET  /api/dashboard/stats

GET  /api/products
POST /api/products
PUT  /api/products/{id}
DELETE /api/products/{id}

GET  /api/customers
PUT  /api/customers/{id}
PUT  /api/customers/{id}/block

GET  /api/orders
PUT  /api/orders/{id}/status
POST /api/orders/{id}/tracking
GET  /api/orders/{id}/bill

GET  /api/inventory
PUT  /api/inventory/{id}

GET  /api/deliveries
PUT  /api/deliveries/{id}

GET  /api/reports/sales
GET  /api/reports/products
```

### File Structure
```
/
├── index.html (redirect to login or dashboard)
├── login.html
├── dashboard.html
├── products.html
├── customers.html
├── orders.html
├── inventory.html
├── delivery.html
├── reports.html
├── css/
│   └── style.css
├── js/
│   ├── app.js
│   ├── charts.js
│   └── modules/
├── config/
│   ├── database.php
│   └── functions.php
├── api/
│   ├── auth.php
│   ├── dashboard.php
│   ├── products.php
│   ├── customers.php
│   ├── orders.php
│   ├── inventory.php
│   ├── delivery.php
│   └── reports.php
├── uploads/
│   └── assets/images/
├── database.sql
└── SPEC.md
```
