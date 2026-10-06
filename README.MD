# 🛒 Sales System

A lightweight PHP + MySQL sales dashboard with product management and a shopping cart. Built with a clean, responsive Bootstrap 5 UI featuring a collapsible sidebar layout.

✨ Features
User Authentication — Session-based login/logout with protected pages
Product Catalog — Browse, add, and edit products with image uploads
Shopping Cart — Add products to cart with quantity validation against stock
Stock Management — Out-of-stock indicators and quantity limits
Flash Messages — Success/error notifications via session flash messages
Responsive Design — Collapsible sidebar (slide-in on mobile, toggle on desktop)
Secure Queries — Prepared statements for all user-scoped queries
🧰 Tech Stack
Layer	Technology
Backend	PHP 7.4+ (sessions, MySQLi)
Database	MySQL / MariaDB
Frontend	Bootstrap 5.3.3, Bootstrap Icons
Font	Poppins (Google Fonts)
📁 Project Structure
├── index.php # Product catalog dashboard (main page) ├── login.php # User login ├── logout.php # Session destroy & redirect ├── cart.php # Shopping cart view ├── add_product.php # Create product (POST handler) ├── edit_product.php # Edit product form/handler ├── add_to_cart.php # Add item to cart (POST handler) ├── connection.php # Database connection └── uploads/ # Product images └── placeholder.svg # Default image fallback


## 🗄️ Database Schema

```sql
CREATE TABLE users (
    user_id   INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL
);

CREATE TABLE products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    price       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock       INT NOT NULL DEFAULT 0,
    image       VARCHAR(255) DEFAULT NULL
);

CREATE TABLE cart_master (
    cart_id  INT AUTO_INCREMENT PRIMARY KEY,
    user_id  INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE cart_details (
    cart_id    INT NOT NULL,
    product_id INT NOT NULL,
    cqty       INT NOT NULL DEFAULT 1,
    FOREIGN KEY (cart_id) REFERENCES cart_master(cart_id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

🚀 Getting Started
Prerequisites
PHP 7.4 or later (with mysqli and mysqlnd extensions enabled)
MySQL 5.7+ or MariaDB
A web server (Apache via XAMPP/WAMP/Laragon, or PHP's built-in server)
Installation
Clone or copy the project into your web root (e.g. htdocs/ for XAMPP):

git clone <repo-url> sales-system

Create the database — import the schema above:

mysql -u root -p < schema.sql

Configure the connection — edit connection.php with your credentials:

$conn = new mysqli('localhost', 'root', 'your_password', 'sales_db');

Ensure the uploads folder is writable so product images can be saved:

chmod 775 uploads/

Run it:

# With PHP's built-in server
php -S localhost:8000

# Or just visit http://localhost/sales-system/ via XAMPP

Log in via login.php and you'll be redirected to the dashboard.

🔐 Security Notes
All pages require an active session (user_id) — unauthenticated visitors are redirected to the login page
Cart queries use prepared statements with bound parameters to prevent SQL injection
User input is escaped with htmlspecialchars() before rendering to prevent XSS
Image uploads are restricted to JPG, PNG, and WebP (max 2MB)
Quantity inputs are clamped to available stock on both client and server side
📝 Notes
The cart badge shows a live count of total items across the user's cart (capped at 99+)
The sidebar collapses via a body.toggled class — desktop hides it, mobile slides it in with an overlay
Product images fall back to uploads/placeholder.svg if missing or broken
📄 License
This project is for educational/personal use.


A couple of things worth double-checking against your actual codebase:

- **Schema** — I inferred `cart_details` has a `product_id` column; if your column names differ (e.g., `pid`), update the SQL block.
- **File list** — If you have extra pages (e.g., `register.php`, `remove_from_cart.php`), add them to the structure section.
- **Image upload validation** — The README claims server-side file-type validation; make sure `add_product.php` actually checks MIME type and extension, not just the client-side `accept` attribute.

Want me to also generate the `schema.sql` as a separate ready-to-import file, or add a screenshots section with placeholder