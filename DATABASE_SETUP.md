# Novatech Database Setup

This document explains how to set up the database for the Novatech Financial Management System.

## Prerequisites

- XAMPP or WAMP installed
- MySQL service running
- PHP CLI available

## Database Setup Instructions

### Option 1: Using the PHP Setup Script (Recommended)

1. Make sure your XAMPP/WAMP MySQL service is running
2. Open a terminal/command prompt
3. Navigate to your project directory:
   ```
   cd c:\xampp\htdocs\Nova1
   ```
4. Run the setup script:
   ```
   php setup_database.php
   ```

### Option 2: Manual SQL Import

1. Start your MySQL service through XAMPP/WAMP control panel
2. Open phpMyAdmin (usually at http://localhost/phpmyadmin)
3. Create a new database named `novatech_db`
4. Select the database
5. Go to the "Import" tab
6. Choose the `database_setup.sql` file
7. Click "Go" to execute the SQL commands

## Database Structure

The setup will create the following tables:

1. **users** - Stores all user information (admins, agents, clients)
2. **products** - Investment products available for purchase
3. **investments** - Records of user investments
4. **wallets** - User wallet balances
5. **transactions** - All financial transactions
6. **recharges** - User account recharge requests
7. **withdrawals** - User withdrawal requests
8. **daily_earnings** - Daily earnings from investments

## Default Admin User

The setup creates a default admin user with:
- Phone: `0780000000`
- Password: `password`
- Role: `admin`

**Important:** Change the admin password immediately after first login!

## Sample Products

The setup includes sample investment products:
1. Starter Package (Regular)
2. Premium Package (Regular)
3. Compound Basic (Compound)
4. Compound Premium (Compound)

## Troubleshooting

If you encounter any issues:

1. **Connection failed errors:**
   - Check that MySQL service is running
   - Verify database credentials in `includes/db_connection.php`
   - Ensure the database user has proper permissions

2. **Permission denied errors:**
   - Make sure your database user has CREATE privileges
   - Check file permissions on the SQL files

3. **Table already exists errors:**
   - Either drop existing tables or modify the SQL to use CREATE TABLE IF NOT EXISTS

## Security Notes

- The default admin password should be changed immediately
- In production, use a strong password for the database user
- Consider using environment variables for database credentials