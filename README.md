# E-Commerce Relational Database & Web Storefront

A full-stack e-commerce implementation developed for **CS3425: Introduction to Database Systems** at Michigan Technological University.
This project demonstrates a fully normalized relational database (MySQL) enforcing core business rules through database-level constraints, stored procedures, and triggers, paired with a PHP web interface for customer shopping and inventory administration.

Contributors: krcampbe, kcdrys

## Key Features & Highlights:

### Database Architecture & Business Logic
* **Transactional Checkout System:** Implemented MySQL cursors and explicit transaction controls ('START TRANSACTION', 'COMMIT', 'ROLLBACK') with 'FOR UPDATE' row locks to guarantee inventory consistency during concurrency.
* **Automated Audit Logging:** Utilized database triggers ('BEFORE UPDATE', 'AFTER UPDATE', 'BEFORE DELETE') to track price and stock changes inside a 'product_history' audit table while preventing illegal record modifications or hard deletions.
* **Security & Authentication:** Password hashing using SHA-256 for user authentication and role-based access separation between customers and employees.
* **Data Integrity:** Strict foreign key cascading policies ('ON DELETE SET NULL', 'ON UPDATE CASCADE') and check constraints to prevent negative stock levels or invalid pricing.

### Web Front-End (PHP)
* **Dynamic Product Catalog:** Dynamic categorization and filtering using PDO parameterized queries to prevent SQL injection vulnerabilities.
* **Multi-Portal Experience:** Distinct customer and employee user journeys for order placement, stock management, and system navigation.

## Database Schema Overview:
The relational schema models a retail system with 6 core tabels:
| Table | Primary Key | Key Fields & Description |
| :--- | :--- | :--- |
| 'employee' | 'employee_id' | Staff authentication ('password_hash'), email, and temporary password flags |
| 'category' | 'category_name' | Product taxonomy and description string |
| 'product' | 'product_id' | Price, threshold tracking ('advising_threshold'), actual stock, and category reference |
| 'customer' | 'customer_id' | Customer details, credentials, and shipping addresses |
| 'purchase' | 'order_id' | Order timestamp, date, status, order total, and customer mapping |
| 'product_history' | 'action_time' | Audit trail logging old/new prices, stock variations, and product IDs |

## Built With:
* **Database:** MySQL
* **Editor/IDE:** Visual Studio Code
* **Language:** SQL/PHP
