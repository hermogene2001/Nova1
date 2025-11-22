# Novatech Financial Management System

A multi-role financial management system with admin, agent, and client panels.

## Features

- User management (admin, agent, client roles)
- Product investment system
- Balance recharge and withdrawal
- Transaction history tracking
- Team/Referral tracking
- Daily profit and compound investment updates

## Prerequisites

- XAMPP or WAMP installed
- PHP 7.0 or higher
- MySQL database

## Installation

1. Clone or copy the project files to your web server directory (e.g., `c:\xampp\htdocs\Nova1`)
2. Start Apache and MySQL services in XAMPP/WAMP
3. Create the database by running:
   ```
   php create_database.php
   ```
4. Verify the database setup by running:
   ```
   php verify_database.php
   ```

## Database Setup

The system uses a MySQL database named `novatech_db`. The database setup script creates:

- Users table (admins, agents, clients)
- Products table (investment products)
- Investments table (user investments)
- Wallets table (user balances)
- Transactions table (financial transactions)
- Recharges table (account recharge requests)
- Withdrawals table (withdrawal requests)
- Daily earnings table (daily investment profits)

A default admin user is created:
- Phone: `0780000000`
- Password: `password` (change immediately after first login)

## Accessing the Application

1. Open your web browser
2. Navigate to `http://localhost/Nova1/`
3. Login with the admin credentials or register as a new user

## Default Admin Credentials

- Phone: `0780000000`
- Password: `password`

**Important:** Change the admin password immediately after first login!

## System Roles

1. **Admin**: Manages users, products, investments, transactions, and withdrawals
2. **Agent**: Handles recharge requests
3. **Client**: Invests in products, manages wallet, and tracks earnings

## Security Notes

- All passwords are hashed using PHP's password_hash() function
- Session management for authentication
- Form validation and sanitization
- Role-based access control

## Troubleshooting

If you encounter any issues:

1. Ensure Apache and MySQL services are running
2. Verify database credentials in `includes/db_connection.php`
3. Check file permissions
4. Review error logs in XAMPP/WAMP