# Simple Banking Plugin for WordPress

![Docker](https://img.shields.io/badge/Docker-Containerized-blue?logo=docker)
![WordPress](https://img.shields.io/badge/WordPress-Plugin%20Ready-blue?logo=wordpress)
![GPLv2](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)

A robust WordPress plugin to manage customers, accounts, and transactions in a banking system. Built for developers and financial enthusiasts to explore core banking operations within WordPress.

---

## 🚀 Features

### Core Functionality

- **Customer Management**
  - Create, Read, Update, Delete (CRUD) customer profiles.
  - Track customer details: name, ID number, CIF number, address, email, date of birth, and more.
- **Account Management**
  - CRUD operations for bank accounts.
  - Supports account types (savings, deposits), balances, and customer associations.
- **Transaction System**
  - Deposit, withdraw, and transfer funds between accounts.
  - Real-time balance updates with overdraft prevention (no negative balances).
- **Database**
  - Built on a relational database (MySQL) for scalable data management.
- **Security**
  - User authentication and role-based access.
  - Input sanitization to prevent SQL injection.

### Optional Enhancements

- Transaction logging and audit trails.
- Account statements with date/type filters.
- Dockerized for seamless deployment.

---

## 📋 Prerequisites

- **Docker** ([Install Guide](https://www.docker.com/))
- **Docker Compose** ([Install Guide](https://docs.docker.com/compose/))
- **Git** ([Install Guide](https://git-scm.com/))

---

## 🛠️ Installation & Setup

### 1. Clone the Repository

```bash
mkdir wordpress-docker && cd wordpress-docker
git clone https://github.com/your-repo/simple-banking.git
```

### 2. Set Up Docker

Copy the docker-compose.yaml to your project root

```sh
cp simple-banking/docker-compose.yaml ./
```

Start the containers

```sh
docker-compose up -d
```

### 3. Install the Plugin

Move the plugin to WordPress's plugins directory (replace with your actual path)

```sh
mv simple-banking/simple-bank-system ./wp-content/plugins/
```

## 🖥️ Usage

### 1. Access WordPress Admin

Visit `http://localhost:8005/wp-admin`.

### 2. Activate the Plugin

- Navigate to `Plugins → Installed Plugins`.
- Then Activate `Simple Bank System`.

### 3. Start Banking!

- A `Banking` menu will appear in the WordPress dashboard.
- Manage customers, accounts, and transactions directly from WordPress.

## 🌐 Contributions

This project is open to the community! Feel free to:

- ⭐ Star the repository if you find it useful.
- 🐛 Report issues or suggest features via GitHub Issues.
- 🛠️ Submit pull requests for enhancements or bug fixes.

---

## 📜 License

This project is licensed under **GPL-2.0-or-later** - see the [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html) for full details.

---

Happy Banking! 🏦
