McNeese Bookstore Web Application
=================================

This project is a PHP-based online bookstore web application built for McNeese State University. It includes features for both regular users and administrators.

----------------------------------------
FEATURES
----------------------------------------

USER FEATURES:
- User login and logout
- Password recovery
- Product browsing with details
- Shopping cart and checkout
- Product search

ADMIN FEATURES:
- Admin login
- Add, edit, and delete products
- View and manage orders

----------------------------------------
FILES AND STRUCTURE
----------------------------------------

login.html            - Front-end login form
login.php             - Processes login credentials
logout.php            - Handles user logout
forgot_password.php   - Password reset logic
home.php              - Displays product listings
admin.php             - Admin dashboard
edit_product.php      - Edit product interface
cart_checkout.php     - Shopping cart and payment form
db_connect.php        - Database connection script

login.css             - Styles for login page
home.css              - Styles for home/product page
admin.css             - Styles for admin dashboard
cart_checkout.css     - Styles for checkout page

images/MSU.png        - McNeese State University logo (required)

----------------------------------------
HOW TO RUN LOCALLY
----------------------------------------

1. Install XAMPP or any LAMP/WAMP stack.
2. Copy the project folder into your web root (e.g., htdocs/).
3. Start Apache and MySQL from XAMPP control panel.
4. Create a MySQL database (e.g., bookstore).
5. Import your database schema (SQL file not included here).
6. Update database credentials in 'db_connect.php'.
7. Open your browser and go to:
   http://localhost/[your-folder-name]/login.html

----------------------------------------
REQUIREMENTS
----------------------------------------

- PHP 7.x or above
- MySQL or compatible DB
- Web browser
- Local server (e.g., XAMPP)

----------------------------------------
CREDITS
----------------------------------------

Developed for educational purposes at McNeese State University.
