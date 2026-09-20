# 🍽️ SRMS — Smart Restaurant Management System

SRMS (Smart Restaurant Management System) is a web-based restaurant management system built with **Laravel**.

The system helps restaurants manage their tables, QR menus, categories, products, and customer orders through a centralized management system.

---

## 📌 Project Overview

SRMS provides a digital ordering experience using **QR Codes assigned to restaurant tables**.

Each table has a unique QR Code. When a customer scans the QR Code, the system automatically identifies the table and displays the restaurant's digital menu.

The customer can browse products, add items to their order, and place an order directly from their table.

At the same time, restaurant staff can manage the restaurant's data and monitor orders through the management system.

---

## ✨ Features

### 🪑 Table Management

* Create and manage restaurant tables
* Set table capacity
* Manage table status
* Generate a unique QR token for each table
* Access the menu through the table's QR Code

### 📱 QR Menu

* Unique QR Code for every table
* Automatically identify the table
* Display the restaurant's digital menu
* Browse products by category

### 📂 Category Management

* Create categories
* Edit categories
* Delete categories
* Activate or deactivate categories

### 🍔 Product Management

* Create products
* Edit products
* Delete products
* Assign products to categories
* Manage product availability

### 🛒 Order Management

* Create orders from the digital menu
* Associate orders with restaurant tables
* Add multiple products to an order
* Calculate order totals
* Manage order status

### 💰 Cash & Payment Management

* Record physical cash collected by employees
* Compare physical cash with cash sales
* Calculate cash shortage or surplus
* Track total Visa/Card payments

---

## 🛠️ Technologies

* **Laravel 13**
* **PHP 8.3+**
* **MySQL**
* **Blade**
* **Tailwind CSS**
* **JavaScript**
* **Composer**
* **Simple QR Code**

---

## 🏗️ Project Architecture

The project follows a structured Laravel architecture with separated responsibilities.

```text
Controller
    ↓
Service
    ↓
Model
    ↓
Database
```

Services are used to keep business logic separated from controllers.

Example:

```text
RestaurantTableController
        ↓
    TableService
        ↓
 RestaurantTable
        ↓
 restaurant_tables
```

This approach makes the project easier to maintain, extend, and debug.

---

## 📂 Main Modules

```text
Authentication
│
├── Tables
│   └── QR Codes
│
├── Categories
│
├── Products
│
├── Menu
│
├── Orders
│
└── Cash Reconciliation
```

---

## ⚙️ Installation

### 1. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/srms.git
```

### 2. Enter the project directory

```bash
cd srms
```

### 3. Install PHP dependencies

```bash
composer install
```

### 4. Install frontend dependencies

```bash
npm install
```

### 5. Create the environment file

```bash
cp .env.example .env
```

### 6. Generate the application key

```bash
php artisan key:generate
```

### 7. Configure the database

Open `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=srms
DB_USERNAME=root
DB_PASSWORD=
```

Change the values according to your local environment.

### 8. Run migrations

```bash
php artisan migrate
```

### 9. Create storage link

```bash
php artisan storage:link
```

### 10. Start the Laravel server

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

---

## 🧑‍💻 Development

For frontend development, run:

```bash
npm run dev
```

Then in another terminal:

```bash
php artisan serve
```

---

## 🔐 Security

The system is designed with security and data separation in mind.

Important areas include:

* Authentication
* CSRF protection
* Request validation
* Unique QR tokens
* Server-side order validation
* Table/order relationship validation
* Authorization for management features

Security considerations will continue to be improved as the project develops.

---

## 🚧 Project Status

**In Development**

The project is currently under active development.

Planned improvements include:

* Improved order session management
* More advanced order tracking
* Restaurant dashboard improvements
* Payment reporting
* Better role and permission management
* UI/UX improvements
* Additional security hardening

---

## 🎯 Goal

The main goal of SRMS is to provide restaurants with a simple digital system that connects:

**Tables → QR Codes → Digital Menu → Orders → Restaurant Management**

while reducing manual ordering and providing restaurant staff with better control over daily operations.

---

## 📄 License

This project is currently for educational and development purposes.
