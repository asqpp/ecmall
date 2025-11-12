<?php
/**
 * =====================================================
 * Insurance Management System v2.0 - Database Setup
 * =====================================================
 *
 * This script will:
 * 1. Create database: cybor432_erpnew
 * 2. Import database schema (23 tables)
 * 3. Import sample data
 *
 * IMPORTANT: Run this file only once during installation!
 * After successful setup, DELETE or RENAME this file for security.
 *
 * =====================================================
 */

// Prevent running setup multiple times
if (file_exists('SETUP_COMPLETE.lock')) {
    die('
    <!DOCTYPE html>
    <html>
    <head>
        <title>Setup Already Complete</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 50px; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .alert { padding: 20px; background: #ffc107; color: #000; border-radius: 5px; margin-bottom: 20px; }
            h1 { color: #333; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="alert">⚠️ Setup has already been completed!</div>
            <h1>Database Already Installed</h1>
            <p>The database has already been set up. If you need to reinstall:</p>
            <ol>
                <li>Delete the SETUP_COMPLETE.lock file</li>
                <li>Drop the database manually</li>
                <li>Run setup.php again</li>
            </ol>
            <p><a href="index.php">Go to Application &rarr;</a></p>
        </div>
    </body>
    </html>
    ');
}

// Database configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'cybor432_erpnew';

// Initialize variables
$errors = [];
$success_messages = [];
$setup_complete = false;

// Start setup if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Override with user input if provided
    $db_host = $_POST['db_host'] ?? $db_host;
    $db_username = $_POST['db_username'] ?? $db_username;
    $db_password = $_POST['db_password'] ?? $db_password;
    $db_name = $_POST['db_name'] ?? $db_name;

    try {
        // Step 1: Connect to MySQL (without database)
        $conn = new mysqli($db_host, $db_username, $db_password);

        if ($conn->connect_error) {
            throw new Exception("MySQL Connection failed: " . $conn->connect_error);
        }

        $success_messages[] = "✓ Connected to MySQL server";

        // Step 2: Create database if not exists
        $sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if ($conn->query($sql) === TRUE) {
            $success_messages[] = "✓ Database '$db_name' created successfully";
        } else {
            throw new Exception("Error creating database: " . $conn->error);
        }

        // Select the database
        $conn->select_db($db_name);

        // Step 3: Import schema
        $schema_file = __DIR__ . '/database/schema.sql';
        if (!file_exists($schema_file)) {
            throw new Exception("Schema file not found: $schema_file");
        }

        $schema_sql = file_get_contents($schema_file);

        // Execute schema (split by ;)
        $statements = array_filter(array_map('trim', explode(';', $schema_sql)));
        $table_count = 0;

        foreach ($statements as $statement) {
            if (empty($statement) || strpos($statement, '--') === 0) continue;

            if ($conn->multi_query($statement)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->next_result());

                if (stripos($statement, 'CREATE TABLE') !== false) {
                    $table_count++;
                }
            }
        }

        $success_messages[] = "✓ Database schema imported ($table_count tables created)";

        // Step 4: Import sample data (optional)
        if (isset($_POST['import_sample_data']) && $_POST['import_sample_data'] === 'yes') {
            $sample_file = __DIR__ . '/database/sample_data.sql';

            if (file_exists($sample_file)) {
                $sample_sql = file_get_contents($sample_file);
                $statements = array_filter(array_map('trim', explode(';', $sample_sql)));
                $insert_count = 0;

                foreach ($statements as $statement) {
                    if (empty($statement) || strpos($statement, '--') === 0) continue;

                    if ($conn->multi_query($statement)) {
                        do {
                            if ($result = $conn->store_result()) {
                                $result->free();
                            }
                        } while ($conn->next_result());

                        if (stripos($statement, 'INSERT INTO') !== false) {
                            $insert_count++;
                        }
                    }
                }

                $success_messages[] = "✓ Sample data imported ($insert_count insert statements executed)";
            }
        }

        // Step 5: Update database config file
        $config_file = __DIR__ . '/application/config/database.php';
        if (file_exists($config_file)) {
            $config_content = file_get_contents($config_file);

            // Update database settings
            $config_content = preg_replace(
                "/'hostname'\s*=>\s*'[^']*'/",
                "'hostname' => '$db_host'",
                $config_content
            );
            $config_content = preg_replace(
                "/'username'\s*=>\s*'[^']*'/",
                "'username' => '$db_username'",
                $config_content
            );
            $config_content = preg_replace(
                "/'password'\s*=>\s*'[^']*'/",
                "'password' => '$db_password'",
                $config_content
            );
            $config_content = preg_replace(
                "/'database'\s*=>\s*'[^']*'/",
                "'database' => '$db_name'",
                $config_content
            );

            file_put_contents($config_file, $config_content);
            $success_messages[] = "✓ Database configuration updated";
        }

        // Create lock file
        file_put_contents('SETUP_COMPLETE.lock', date('Y-m-d H:i:s'));
        $setup_complete = true;

        $conn->close();

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insurance Management System - Database Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .success-icon {
            font-size: 64px;
            text-align: center;
            margin-bottom: 20px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        ul li:last-child {
            border-bottom: none;
        }
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .info-box h3 {
            margin-bottom: 10px;
            color: #667eea;
        }
        .info-box ul {
            margin-left: 20px;
        }
        .info-box ul li {
            border: none;
            padding: 5px 0;
        }
        .btn-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn-link:hover {
            background: #218838;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($setup_complete): ?>
            <!-- Success Screen -->
            <div class="success-icon">🎉</div>
            <h1 style="text-align: center; color: #28a745;">Setup Complete!</h1>
            <p class="subtitle" style="text-align: center;">Your Insurance Management System is ready to use</p>

            <?php foreach ($success_messages as $msg): ?>
                <div class="alert alert-success"><?php echo $msg; ?></div>
            <?php endforeach; ?>

            <div class="info-box">
                <h3>✅ What was installed:</h3>
                <ul>
                    <li>✓ Database: <strong><?php echo $db_name; ?></strong></li>
                    <li>✓ 23 Database Tables</li>
                    <li>✓ Complete Schema</li>
                    <?php if (isset($_POST['import_sample_data']) && $_POST['import_sample_data'] === 'yes'): ?>
                    <li>✓ Sample Data (Demo accounts, products, transactions)</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="warning-box">
                <strong>⚠️ IMPORTANT SECURITY NOTICE:</strong>
                <p style="margin-top: 10px;">For security reasons, please DELETE or RENAME the <code>setup.php</code> file immediately!</p>
            </div>

            <div style="text-align: center;">
                <a href="index.php" class="btn-link">Launch Application →</a>
            </div>

        <?php elseif (!empty($errors)): ?>
            <!-- Error Screen -->
            <h1>Setup Failed</h1>
            <p class="subtitle">Please fix the errors below and try again</p>

            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>

            <button onclick="window.location.reload()">Try Again</button>

        <?php else: ?>
            <!-- Setup Form -->
            <h1>Database Setup</h1>
            <p class="subtitle">Insurance Management System v2.0</p>

            <div class="alert alert-info">
                <strong>ℹ️ Setup Instructions:</strong><br>
                1. Enter your database credentials below<br>
                2. Choose whether to import sample data<br>
                3. Click "Install Database" button
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Database Host:</label>
                    <input type="text" name="db_host" value="<?php echo $db_host; ?>" required>
                </div>

                <div class="form-group">
                    <label>Database Username:</label>
                    <input type="text" name="db_username" value="<?php echo $db_username; ?>" required>
                </div>

                <div class="form-group">
                    <label>Database Password:</label>
                    <input type="password" name="db_password" value="<?php echo $db_password; ?>">
                </div>

                <div class="form-group">
                    <label>Database Name:</label>
                    <input type="text" name="db_name" value="<?php echo $db_name; ?>" required>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="import_sample_data" value="yes" id="sample_data" checked>
                        <label for="sample_data" style="margin: 0;">Import Sample Data (Recommended for testing)</label>
                    </div>
                    <small style="color: #666; margin-left: 30px; display: block; margin-top: 5px;">
                        Includes demo customers, products, invoices, and transactions
                    </small>
                </div>

                <button type="submit">🚀 Install Database</button>
            </form>

            <div class="info-box" style="margin-top: 30px;">
                <h3>📦 What will be installed:</h3>
                <ul>
                    <li>• 23 Database Tables</li>
                    <li>• Complete Chart of Accounts</li>
                    <li>• Customer & Supplier Management</li>
                    <li>• Product Catalog System</li>
                    <li>• Sales & Purchase Modules</li>
                    <li>• Broker & Agent Commission Tracking</li>
                    <li>• Financial Reports & Accounting</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
