# 🛒 Sales System

<p align="center">
  <strong>A lightweight PHP + MySQL sales dashboard with product management, inventory tracking, and a shopping cart.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/Bootstrap-5.3.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white">
  <img src="https://img.shields.io/badge/License-Personal-success?style=for-the-badge">
</p>

---

## 📸 Preview

> Add your project screenshots here.

<p align="center">
  <img src="screenshots/dashboard.png" alt="Dashboard" width="850">
</p>

<p align="center">
  <img src="screenshots/cart.png" alt="Shopping Cart" width="850">
</p>

---

## ✨ Features

- 🔐 **User Authentication**
  - Session-based login and logout
  - Protected application pages
  - Automatic authentication checks

- 📦 **Product Management**
  - Browse products
  - Add products
  - Edit products
  - Product descriptions
  - Product pricing
  - Stock management
  - Product image uploads

- 🛒 **Shopping Cart**
  - Add products to cart
  - Quantity management
  - Stock-based quantity limits
  - Live cart item count
  - User-specific carts

- 📊 **Stock Management**
  - Stock quantity tracking
  - Out-of-stock indicators
  - Quantity validation
  - Server-side stock checks

- 🎨 **Responsive UI**
  - Bootstrap 5.3.3
  - Responsive sidebar
  - Mobile navigation
  - Desktop sidebar toggle
  - Bootstrap Icons
  - Poppins font

- 🛡️ **Security**
  - Prepared SQL statements
  - Session-based authorization
  - HTML output escaping
  - Server-side validation
  - Image upload restrictions

---

## 🧰 Tech Stack

| Technology | Usage |
|---|---|
| PHP 7.4+ | Backend |
| MySQL / MariaDB | Database |
| MySQLi | Database connection |
| Bootstrap 5.3.3 | UI framework |
| Bootstrap Icons | Icons |
| Poppins | Font |
| PHP Sessions | Authentication |
| Apache / PHP Server | Web server |

---

## 📁 Project Structure

```text
sales-system/
│
├── index.php
├── login.php
├── logout.php
│
├── cart.php
├── add_to_cart.php
│
├── add_product.php
├── edit_product.php
│
├── connection.php
│
├── uploads/
│   └── placeholder.svg
│
├── screenshots/
│   ├── dashboard.png
│   ├── cart.png
│   ├── login.png
│   └── product-form.png
│
└── README.md



users
  │
  └── cart_master
          │
          └── cart_details
                  │
                  └── products



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

