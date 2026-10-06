🛒 Sales System

A lightweight PHP + MySQL sales management dashboard with product management, inventory tracking, and a session-based shopping cart — built with a clean and responsive Bootstrap 5 interface.

<p align="center"> <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 7.4+"> <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"> <img src="https://img.shields.io/badge/Bootstrap-5.3.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap 5"> <img src="https://img.shields.io/badge/License-Educational%2FPersonal-success?style=for-the-badge" alt="License"> </p> <p align="center"> <strong>Simple · Responsive · Secure · Lightweight</strong> </p>
📸 Preview

Add screenshots of your dashboard here to give visitors an immediate look at the application.

Dashboard	Shopping Cart
📷 Add screenshot here	📷 Add screenshot here
screenshots/dashboard.png	screenshots/cart.png
Suggested screenshots
screenshots/
├── dashboard.png
├── cart.png
├── login.png
└── product-form.png

✨ Features
🔐 Authentication

Session-based user authentication

Protected application pages

Login and logout functionality

Automatic redirect for unauthenticated users

📦 Product Management

Browse products in a responsive catalog

Add new products

Edit existing products

Product descriptions and pricing

Stock quantity management

Product image uploads

Placeholder image fallback

🛒 Shopping Cart

Add products directly to the cart

Quantity management

Stock-aware quantity validation

Live cart item count

User-specific carts

Prevent quantities from exceeding available stock

📊 Inventory & Stock

Real-time stock information

Out-of-stock indicators

Quantity limits based on inventory

Server-side stock validation

🎨 Responsive UI

Bootstrap 5.3.3

Responsive dashboard layout

Collapsible sidebar

Mobile slide-in navigation

Desktop sidebar toggle

Overlay support for mobile navigation

Bootstrap Icons

Poppins typography

🛡️ Security

Prepared statements for database queries

Session-based access control

HTML escaping to reduce XSS risks

Server-side quantity validation

Image upload restrictions

File-size limits for uploaded images

🧰 Tech Stack
Layer	Technology
Backend	PHP 7.4+
Database	MySQL / MariaDB
Database Driver	MySQLi
Frontend	HTML5, CSS3, JavaScript
UI Framework	Bootstrap 5.3.3
Icons	Bootstrap Icons
Font	Poppins
Authentication	PHP Sessions
Server	Apache / PHP Built-in Server
📁 Project Structure
sales-system/
│
├── index.php              # Product catalog / dashboard
├── login.php              # User login
├── logout.php             # Session logout
│
├── cart.php               # Shopping cart
├── add_to_cart.php        # Add product to cart
│
├── add_product.php        # Create product
├── edit_product.php       # Edit product
│
├── connection.php         # Database connection
│
├── uploads/               # Uploaded product images
│   └── placeholder.svg    # Default product image
│
└── README.md


If your project contains additional files such as register.php, remove_from_cart.php, or shared layout files, add them to the structure above.

🗄️ Database

The application uses four main tables:

users
  │
  └── cart_master
          │
          └── cart_details
                  │
                  └── products

Database Schema
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL
);

CREATE TABLE cart_master (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE cart_details (
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    cqty INT NOT NULL DEFAULT 1,
    FOREIGN KEY (cart_id) REFERENCES cart_master(cart_id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

💡 Recommended Database Improvements

For production use, consider adding:

ALTER TABLE cart_details
ADD UNIQUE KEY unique_cart_product (cart_id, product_id);


This prevents the same product from being inserted into the same cart multiple times as separate rows.

You may also want indexes on frequently queried columns:

CREATE INDEX idx_cart_master_user
ON cart_master(user_id);

CREATE INDEX idx_cart_details_product
ON cart_details(product_id);

🚀 Getting Started
1. Prerequisites

Make sure the following are installed:

PHP 7.4 or later

MySQL 5.7+ or MariaDB

PHP mysqli extension

PHP mysqlnd extension

Apache, XAMPP, WAMP, Laragon, or PHP's built-in server

2. Clone the Repository
git clone <repo-url> sales-system
cd sales-system


Or simply copy the project into your web server directory.

XAMPP
C:\xampp\htdocs\sales-system

WAMP
C:\wamp64\www\sales-system

Laragon
C:\laragon\www\sales-system

3. Create the Database

Create a database:

CREATE DATABASE sales_db;


Select it:

USE sales_db;


Then import the provided schema.

Alternatively:

mysql -u root -p sales_db < schema.sql

4. Configure the Database Connection

Open:

connection.php


Configure your MySQL credentials:

<?php

$conn = new mysqli(
    'localhost',
    'root',
    'your_password',
    'sales_db'
);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}


⚠️ For production environments, do not commit database passwords or other secrets directly into your repository.

5. Configure Uploads

Make sure the uploads/ directory exists and is writable by the web server.

Linux/macOS:

chmod 775 uploads/


Windows users generally do not need to run this command when using XAMPP, WAMP, or Laragon.

6. Start the Application
PHP Built-in Server

From the project directory:

php -S localhost:8000


Then open:

http://localhost:8000

XAMPP / Apache

Place the project inside:

htdocs/sales-system/


Then visit:

http://localhost/sales-system/

🔑 Authentication

Open:

/login.php


After successful authentication, the user is redirected to the main dashboard.

Protected pages verify the user's session:

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

🛒 Shopping Cart Flow

The cart follows a simple user-specific structure:

User
 │
 ▼
cart_master
 │
 ▼
cart_details
 │
 ▼
products


When a product is added:

The user's active cart is identified.

The product is checked.

Available stock is validated.

The requested quantity is validated.

The cart is updated.

A success or error flash message is displayed.

📦 Product Images

Product images are stored in:

uploads/


The application should restrict uploads to supported formats such as:

JPG
PNG
WebP


Recommended maximum file size:

2 MB


If a product does not have a valid image, the application falls back to:

uploads/placeholder.svg

⚠️ Server-Side Validation

Do not rely only on:

<input type="file" accept="image/*">


The server should independently validate:

MIME type

File extension

File size

Upload error status

Generated filename

Image validity

Client-side validation improves UX, but server-side validation is what provides security.

🔐 Security

The project includes several basic security practices.

Prepared Statements

Database queries should use prepared statements:

$stmt = $conn->prepare(
    "SELECT * FROM products WHERE id = ?"
);

$stmt->bind_param("i", $product_id);
$stmt->execute();


This helps prevent SQL injection.

Output Escaping

User-controlled values should be escaped before being rendered:

echo htmlspecialchars(
    $product['name'],
    ENT_QUOTES,
    'UTF-8'
);

Session Protection

Protected pages should verify authentication before displaying private data.

Server-Side Validation

Important values such as:

Product IDs

Cart quantities

Stock limits

Uploaded files

should always be validated on the server.

💬 Flash Messages

The application uses session-based flash messages for user feedback.

Typical messages include:

✓ Product added successfully.
✓ Product updated successfully.
✓ Item added to cart.
✕ Invalid product.
✕ Insufficient stock.
✕ Please log in first.


This keeps user feedback consistent across redirects.

📱 Responsive Sidebar

The dashboard uses a responsive sidebar that behaves differently depending on screen size.

Desktop
┌──────────────┬──────────────────────────┐
│              │                          │
│   SIDEBAR    │       DASHBOARD          │
│              │                          │
│   Products   │       Products           │
│   Cart       │       ┌────┐ ┌────┐      │
│   Logout     │       │    │ │    │      │
│              │       └────┘ └────┘      │
└──────────────┴──────────────────────────┘

Mobile
┌─────────────────────────┐
│ ☰  Sales System     🛒  │
├─────────────────────────┤
│                         │
│       DASHBOARD         │
│                         │
│       Products          │
│                         │
└─────────────────────────┘


The sidebar can be toggled using the application's body.toggled state, with an overlay used for mobile navigation.

🧪 Validation Checklist

Before deploying, verify the following:

Authentication

 Login works

 Logout destroys the session

 Protected pages redirect unauthenticated users

 Passwords are stored securely using password_hash()

Products

 Products can be created

 Products can be edited

 Invalid product IDs are rejected

 Negative prices are rejected

 Negative stock values are rejected

 Images are validated server-side

Cart

 Products can be added

 Quantity cannot exceed stock

 Zero/negative quantities are rejected

 Users cannot access another user's cart

 Cart count is correct

 Out-of-stock products cannot be added

Database

 Prepared statements are used

 Foreign keys are enabled

 Appropriate indexes exist

 Database credentials are not committed

🛠️ Recommended Production Improvements

This project is designed as a lightweight educational/personal sales system. Before using it in production, consider adding:

CSRF protection for POST requests

Password reset functionality

User registration and role management

Admin/user permissions

Order and checkout management

Transaction handling for stock updates

Stronger image upload validation

Unique generated filenames for uploads

Pagination for large product catalogs

Search and filtering

Product categories

Audit logging

Environment-based configuration

HTTPS

Database backups

Error logging instead of displaying database errors

Rate limiting for authentication endpoints

📌 Important Notes
Cart Count

The cart badge displays the total number of items in the current user's cart and is capped at:

99+

Stock Limits

Cart quantities should never exceed the currently available product stock.

Both client-side and server-side validation should enforce this rule.

Product Images

Missing or invalid product images should fall back to:

uploads/placeholder.svg

User Isolation

Each authenticated user should only be able to access their own cart data.

🗺️ Roadmap

Potential future improvements:

 Checkout system

 Order history

 Sales reports

 Dashboard analytics

 Product categories

 Product search

 Product filtering

 User registration

 Admin roles

 Low-stock alerts

 Printable invoices

 Export sales to CSV

 Dark mode

 REST API

🤝 Contributing

Contributions and improvements are welcome.

Fork the repository.

Create a feature branch.

git checkout -b feature/my-feature


Make your changes.

Commit your changes.

git commit -m "Add my feature"


Push the branch.

git push origin feature/my-feature


Open a Pull Request.

📄 License

This project is intended for educational and personal use.

You are free to modify and extend it for your own projects.

⭐ Support

If this project helped you learn PHP, MySQL, Bootstrap, or session-based application development, consider giving the repository a ⭐.

<p align="center"> Built with ❤️ using PHP, MySQL & Bootstrap </p>
