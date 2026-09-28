Sales App (Sales_app)

A simple PHP-based sales management application. Follow the instructions below to clone the repository, download the database file, and set up the project locally using XAMPP/WAMP.

🛠️ Prerequisites

Ensure you have the following installed on your machine:

Git (for cloning the repository)

XAMPP / WAMP (Apache web server + MySQL database)

Web Browser (Chrome, Firefox, Edge, etc.)

🚀 Setup Instructions

1. Clone the Repository

Open your terminal or command prompt and clone the repository into your local web server's root folder (htdocs for XAMPP or www for WAMP):

# For XAMPP users (navigate to htdocs first)
cd C:/xampp/htdocs

# Clone the project repository
git clone https://github.com/seanDotDev67/Sales_app.git

# Move into the project directory
cd Sales_app


2. Download the Database File

Download the SQL schema file using the link below:

Database File: Download sales_project.sql

Save the sales_project.sql file to your computer (e.g., in your Downloads folder or inside the Sales_app root directory).

3. Import the Database (phpMyAdmin)

Start Apache and MySQL:

Open the XAMPP Control Panel.

Click Start next to both Apache and MySQL.

Open phpMyAdmin:

Open your web browser and navigate to http://localhost/phpmyadmin.

Create a New Database:

In the left panel, click New.

Enter sales_project as the database name.

Click Create.

Import the .sql File:

Select your newly created database (sales_project) from the left sidebar.

Click on the Import tab in the top navigation bar.

Under File to import, click Choose File and select your downloaded sales_project.sql file.

Scroll down and click Import (or Go).

Once complete, you will see a success message listing all imported tables.

4. Database Connection Configuration

Verify that the database configuration in connection.php matches your local MySQL credentials:




5. Run the Application

Open your browser and access the local server path:

http://localhost/Sales_app/index.php


📁 Project Structure

Sales_app/
├── uploads/               # Product image uploads
├── add_product.php        # Form to add new products
├── add_to_cart.php       # Add items to shopping cart
├── cart.php               # Shopping cart view
├── checkout.php           # Checkout functionality
├── connection.php         # MySQL connection setup
├── edit_product.php       # Edit product details
├── index.php              # Main landing / dashboard page
├── login.php              # User authentication (Login)
├── logout.php             # Session termination
├── signup.php             # User registration
└── update_cart.php        # Modify cart item quantities
