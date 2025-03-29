# Simple Banking Plugin for WordPress

![Docker](https://img.shields.io/badge/Docker-Containerized-blue?logo=docker)
![WordPress](https://img.shields.io/badge/WordPress-Plugin%20Ready-blue?logo=wordpress)
![GPLv2](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)

A **foundational implementation** of banking operations within WordPress, providing core structures for managing customers, accounts, and transactions.

Designed as:

- 🧩 A starter project for extending financial systems in WordPress.
- 🔍 A learning tool for banking transaction workflows.
- � Modular foundation for custom banking solutions.

## 🚀 Core Functionality

### A. Essential Banking Operations

- **Customer Management**

  - Full CRUD operations for customer profiles.
  - Track comprehensive details:
    - Name
    - ID/CIF numbers
    - Address
    - Email
    - Date of birth
    - Account creation date

- **Account Management**

  - Complete account lifecycle management.
  - Supports:
    - Savings/Deposit account types
    - Balance tracking
    - Customer associations
    - Basic account statements

- **Transaction System**
  - Core operations:
    - Deposits
    - Withdrawals
    - transfer funds between accounts
  - Safety features:
    - Real-time balance updates
    - Overdraft prevention
    - Transaction logging (basic audit trail)

### B. Infrastructure & Security

- **Database**
  MySQL relational database with:

  - Normalized table structure
  - Scalable data relationships

- **Security Framework**

  - WordPress authentication integration
  - Role-based access control
  - SQL injection prevention
  - Input/output sanitization

- **Deployment**
  - Docker containerization
  - Including docker-compose.yaml file

## 📋 Prerequisites

- **Docker** (<a href="https://www.docker.com/" target="_blank">Install Guide</a>)
- **Docker Compose** (<a href="https://docs.docker.com/compose/" target="_blank">Install Guide</a>)
- **Git** (<a href="https://git-scm.com/" target="_blank">Install Guide</a>)

## 🛠️ Installation & Setup

### 1. Clone the Repository

```bash
mkdir wordpress-docker && cd wordpress-docker
git clone https://github.com/leonhards/simple-banking.git
```

### 2. Set Up Docker

Copy the docker-compose.yaml to your project root.

```sh
cp simple-banking/docker-compose.yaml ./
```

Start the containers.

```sh
docker-compose up -d
```

### 3. Install the Plugin

Move the plugin to WordPress's plugins directory (replace with your actual path).

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

## 📜 License

This project is licensed under **GPL-2.0-or-later** - see the [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html) for full details.

---

Happy Banking! 🏦
