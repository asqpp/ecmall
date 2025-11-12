# Database Import Instructions

## Step 1: Save Your SQL File
Save your phpMyAdmin export as: `fullscheme_v2.sql` in the project root

## Step 2: Import to MySQL
```bash
mysql -u root -p < fullscheme_v2.sql
```

Or use phpMyAdmin:
1. Open phpMyAdmin
2. Create database `cybor432_erpnew` (if not exists)
3. Select the database
4. Click Import tab
5. Choose your SQL file
6. Click Go

## Step 3: Update Database Config
File: `application/config/database.php`
```php
$db['default'] = array(
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',  // Your MySQL password
    'database' => 'cybor432_erpnew',
);
```

## Step 4: Login Credentials
- **Username:** admin
- **Password:** admin123

## Step 5: Access System
Navigate to: `http://localhost/ecmall/`

The system will redirect to login if not authenticated.
