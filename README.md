# Inventory Management System Project

A full-stack web-based Inventory Management System built with HTML, CSS, JavaScript, PHP, and MySQL. It helps businesses and individuals track, manage, and analyze stock with a secure backend and persistent database storage.

---

## 🚀 Features

- 🔐 **User Authentication** — Secure PHP session-based login system with hashing
- ➕ **Add / Edit / Delete Items** — Full CRUD operations connected to MySQL database
- 📊 **Reports & Charts** — Visual insights into stock levels and inventory trends
- 🔍 **Search & Filter** — Quickly find items by name, category, or status
- ⚠️ **Stock Tracking & Alerts** — Real-time stock monitoring with low-stock warnings

---

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| HTML5 | Structure & markup |
| CSS3 | Styling & responsive design |
| JavaScript (ES6+) | Frontend interactivity |
| PHP | Backend logic & server-side processing |
| MySQL | Database for storing inventory data |

---

## 🏁 Getting Started (Run on Localhost)

### ✅ Prerequisites

Make sure you have one of the following installed:

- [XAMPP](https://www.apachefriends.org/) ✅ _(Recommended — easiest for beginners)_
- [WAMP](https://www.wampserver.com/) _(Windows only)_
- [MAMP](https://www.mamp.info/) _(Mac/Windows)_

> XAMPP gives you Apache (web server) + MySQL + PHP all in one package.

---

### 🔧 Step-by-Step Setup

#### 1. Install & Start XAMPP

- Download and install XAMPP from [apachefriends.org](https://www.apachefriends.org/)
- Open the **XAMPP Control Panel**
- Click **Start** next to both **Apache** and **MySQL**

> Both should show green. If they don't start, check if port 80 or 3306 is already in use.

---

#### 2. Clone or Download the Project

```bash
https://github.com/Tushar777-cloud/Inventory-Management-System-Project.git
```

Or download the ZIP and extract it.

---

#### 3. Move Project to the Right Folder

Copy your project folder into XAMPP's `htdocs` directory:

| OS | Path |
|----|------|
| Windows | `C:\xampp\htdocs\` |
| Mac | `/Applications/XAMPP/htdocs/` |
| Linux | `/opt/lampp/htdocs/` |

So your project should be at:
C:\xampp\htdocs\inventory-management-system\

---

#### 4. Set Up the Database

1. Open your browser and go to:
http://localhost/phpmyadmin

2. Click **"New"** on the left sidebar to create a new database

3. Name it:
inventory_db
   and click **Create**

4. Click on the **`inventory_db`** database you just created

5. Click the **SQL** tab at the top

6. Paste the SQL queries from the file below and click **Go**:
database/inventory_db.sql

> This will create all the required tables automatically.

---

#### 5. Configure Database Connection

Open the file `config/db.php` (or `connection.php` — wherever your DB config is) and update:

```php
<?php
$host = "localhost";
$user = "root";        // default XAMPP username
$password = "";        // default XAMPP password is empty
$database = "inventory_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

> If you set a custom MySQL password in XAMPP, update the `$password` field.

---

#### 6. Run the Project

Open your browser and go to:
http://localhost/inventory-management-system/index.html

Or if your entry point is a PHP file:
http://localhost/inventory-management-system/index.php

🎉 **You're all set!**

---

## 📁 Project Structure
inventory-management-system/

├── index.php               # Main entry / dashboard

├── login.php               # Login page

├── logout.php              # Session destroy

├── config/

│   └── db.php              # Database connection file

├── database/

│   └── inventory_db.sql    # SQL file to set up tables

├── css/

│   └── style.css           # Stylesheet

├── js/

│   └── app.js              # Frontend JavaScript

├── pages/

│   ├── add_item.php

│   ├── edit_item.php

│   ├── delete_item.php

│   ├── reports.php

│   └── search.php

└── README.md

> _(Update this to match your actual file structure)_

---

## 🗄️ Database Structure (Overview)

The SQL file sets up the following tables:

- **`users`** — stores login credentials
- **`items`** — stores product/inventory data
- **`categories`** — product categories
- **`stock_log`** — tracks stock changes over time

> Import `database/inventory_db.sql` into phpMyAdmin to create all tables automatically.

---

## 📖 Usage

1. Go to `http://localhost/inventory-management-system/`
2. **Log in** with your credentials
3. Use the **Dashboard** to view current stock overview
4. Click **Add Item** to add new products to the database
5. Use **Search & Filter** to find specific items
6. Monitor **low-stock alerts** and restock accordingly
7. Go to **Reports** to view charts and analytics

---

## 🎓 About This Project

This project was developed as a **college project** to practice full-stack web development using core technologies — HTML, CSS, JavaScript, PHP, and MySQL — without any frameworks. It demonstrates skills in backend development, database design, SQL queries, session management, and frontend UI design.

---

## 🙋‍♂️ Author

**Tushar**
- GitHub: [@your-username](https://github.com/your-username)

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

⭐ If you found this project useful, give it a star — it means a lot!
