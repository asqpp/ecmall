<?php
/**
 * =====================================================
 * NA-FIX ERP System - Complete Database Setup
 * =====================================================
 *
 * This script will:
 * 1. Check system requirements
 * 2. Create database: cybor432_erpnew
 * 3. Import complete database (100+ tables) from fullscheme.sql
 * 4. Import additional data from additional_data.sql (optional)
 * 5. Configure database connection
 * 6. Create installation logs
 * 7. Redirect to dashboard
 *
 * IMPORTANT: Run this file only once during installation!
 * After successful setup, DELETE or RENAME this file for security.
 *
 * =====================================================
 */

// Enable error reporting for installation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create logs directory if not exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Initialize log file
$log_file = __DIR__ . '/logs/setup_' . date('Y-m-d_H-i-s') . '.log';

/**
 * Log function with timestamp
 */
function log_message($message, $type = 'INFO') {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$type] $message" . PHP_EOL;
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    return $log_entry;
}

/**
 * System requirements checker
 */
function check_system_requirements() {
    $checks = [];

    // PHP Version
    $php_version = phpversion();
    $checks['php_version'] = [
        'name' => 'PHP Version (7.2 or higher)',
        'status' => version_compare($php_version, '7.2.0', '>='),
        'current' => $php_version,
        'required' => '7.2.0+'
    ];

    // MySQLi Extension
    $checks['mysqli'] = [
        'name' => 'MySQLi Extension',
        'status' => extension_loaded('mysqli'),
        'current' => extension_loaded('mysqli') ? 'Enabled' : 'Disabled',
        'required' => 'Required'
    ];

    // mbstring Extension
    $checks['mbstring'] = [
        'name' => 'Mbstring Extension',
        'status' => extension_loaded('mbstring'),
        'current' => extension_loaded('mbstring') ? 'Enabled' : 'Disabled',
        'required' => 'Required'
    ];

    // JSON Extension
    $checks['json'] = [
        'name' => 'JSON Extension',
        'status' => extension_loaded('json'),
        'current' => extension_loaded('json') ? 'Enabled' : 'Disabled',
        'required' => 'Required'
    ];

    // Writable config directory
    $config_dir = __DIR__ . '/application/config';
    $checks['config_writable'] = [
        'name' => 'Config Directory Writable',
        'status' => is_writable($config_dir),
        'current' => is_writable($config_dir) ? 'Writable' : 'Not Writable',
        'required' => 'Required'
    ];

    // Writable logs directory
    $logs_dir = __DIR__ . '/application/logs';
    $checks['logs_writable'] = [
        'name' => 'Logs Directory Writable',
        'status' => is_writable($logs_dir),
        'current' => is_writable($logs_dir) ? 'Writable' : 'Not Writable',
        'required' => 'Required'
    ];

    // Check if fullscheme.sql exists
    $checks['fullscheme'] = [
        'name' => 'Main Database File (fullscheme.sql)',
        'status' => file_exists(__DIR__ . '/fullscheme.sql'),
        'current' => file_exists(__DIR__ . '/fullscheme.sql') ? 'Found' : 'Not Found',
        'required' => 'Required'
    ];

    // Check if additional_data.sql exists
    $checks['additional_data'] = [
        'name' => 'Additional Data File (additional_data.sql)',
        'status' => file_exists(__DIR__ . '/additional_data.sql'),
        'current' => file_exists(__DIR__ . '/additional_data.sql') ? 'Found' : 'Not Found (Optional)',
        'required' => 'Optional'
    ];

    log_message('System requirements check completed');
    foreach ($checks as $key => $check) {
        $status = $check['status'] ? 'PASS' : 'FAIL';
        log_message("{$check['name']}: {$status} - {$check['current']}", $status);
    }

    return $checks;
}

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

        // Step 3: Import main database (fullscheme.sql)
        log_message('Starting import of main database (fullscheme.sql)');
        $schema_file = __DIR__ . '/fullscheme.sql';

        if (!file_exists($schema_file)) {
            throw new Exception("Main database file not found: fullscheme.sql");
        }

        $schema_sql = file_get_contents($schema_file);
        log_message('Read fullscheme.sql (' . strlen($schema_sql) . ' bytes)');

        // Remove comments and split into statements
        $schema_sql = preg_replace('/^--.*$/m', '', $schema_sql); // Remove SQL comments
        $schema_sql = preg_replace('/\/\*.*?\*\//s', '', $schema_sql); // Remove multi-line comments

        // Execute the entire SQL file using multi_query
        if ($conn->multi_query($schema_sql)) {
            $table_count = 0;
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }

                // Count CREATE TABLE statements
                if ($conn->error) {
                    log_message('Query error: ' . $conn->error, 'WARNING');
                }
            } while ($conn->more_results() && $conn->next_result());

            // Count tables created
            $result = $conn->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '$db_name'");
            if ($result) {
                $row = $result->fetch_assoc();
                $table_count = $row['table_count'];
                $result->free();
            }

            log_message("Database schema imported successfully ($table_count tables created)");
            $success_messages[] = "✓ Complete database imported ($table_count tables created)";
        } else {
            throw new Exception("Error importing database: " . $conn->error);
        }

        // Step 4: Import additional data (optional but recommended)
        if (isset($_POST['import_additional_data']) && $_POST['import_additional_data'] === 'yes') {
            $additional_file = __DIR__ . '/additional_data.sql';

            if (file_exists($additional_file)) {
                log_message('Starting import of additional_data.sql');
                $additional_sql = file_get_contents($additional_file);
                log_message('Read additional_data.sql (' . strlen($additional_sql) . ' bytes)');

                // Remove comments
                $additional_sql = preg_replace('/^--.*$/m', '', $additional_sql);
                $additional_sql = preg_replace('/\/\*.*?\*\//s', '', $additional_sql);

                if ($conn->multi_query($additional_sql)) {
                    do {
                        if ($result = $conn->store_result()) {
                            $result->free();
                        }
                        if ($conn->error) {
                            log_message('Additional data query error: ' . $conn->error, 'WARNING');
                        }
                    } while ($conn->more_results() && $conn->next_result());

                    log_message('Additional data imported successfully');
                    $success_messages[] = "✓ Additional data imported (extra users, accounts, permissions)";
                } else {
                    log_message('Error importing additional data: ' . $conn->error, 'WARNING');
                    $success_messages[] = "⚠️ Additional data import had some warnings (check logs)";
                }
            } else {
                log_message('additional_data.sql not found, skipping', 'INFO');
            }
        }

        // Step 5: Update database config file
        log_message('Updating database configuration file');
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

            if (file_put_contents($config_file, $config_content)) {
                log_message('Database configuration file updated successfully');
                $success_messages[] = "✓ Database configuration updated";
            } else {
                throw new Exception("Failed to update database configuration file");
            }
        } else {
            throw new Exception("Database configuration file not found: $config_file");
        }

        // Create lock file
        $lock_content = "Installation completed on: " . date('Y-m-d H:i:s') . "\n";
        $lock_content .= "Database: $db_name\n";
        $lock_content .= "Host: $db_host\n";
        $lock_content .= "Log file: $log_file\n";
        file_put_contents('SETUP_COMPLETE.lock', $lock_content);
        log_message('Setup completed successfully - Lock file created');

        $setup_complete = true;

        $conn->close();
        log_message('Database connection closed');

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        log_message('ERROR: ' . $e->getMessage(), 'ERROR');
    }
}

// Always check system requirements
$system_checks = check_system_requirements();
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
            <p class="subtitle" style="text-align: center;">Your NA-FIX ERP System is ready to use</p>

            <?php foreach ($success_messages as $msg): ?>
                <div class="alert alert-success"><?php echo $msg; ?></div>
            <?php endforeach; ?>

            <div class="info-box">
                <h3>✅ What was installed:</h3>
                <ul>
                    <li>✓ Database: <strong><?php echo $db_name; ?></strong></li>
                    <li>✓ 100+ Database Tables (Complete ERP System)</li>
                    <li>✓ Double-Entry Accounting System</li>
                    <li>✓ Insurance Management Modules</li>
                    <li>✓ Sales, Purchase, Inventory Modules</li>
                    <li>✓ User Authentication & Role Management</li>
                    <?php if (isset($_POST['import_additional_data']) && $_POST['import_additional_data'] === 'yes'): ?>
                    <li>✓ Additional Data (Extra users: manager, accountant, underwriter, claims)</li>
                    <li>✓ Extended Chart of Accounts</li>
                    <li>✓ Comprehensive Role Permissions</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="info-box">
                <h3>🔑 Default Login Credentials:</h3>
                <ul style="background: #fff; padding: 15px; border-radius: 5px; margin-top: 10px;">
                    <li><strong>Username:</strong> admin</li>
                    <li><strong>Password:</strong> admin123</li>
                </ul>
                <?php if (isset($_POST['import_additional_data']) && $_POST['import_additional_data'] === 'yes'): ?>
                <p style="margin-top: 10px; color: #666;">
                    <small>Additional test users: manager, accountant, underwriter, claims (all password: admin123)</small>
                </p>
                <?php endif; ?>
            </div>

            <div class="warning-box">
                <strong>⚠️ IMPORTANT SECURITY NOTICE:</strong>
                <p style="margin-top: 10px;">For security reasons, please DELETE or RENAME the <code>setup.php</code> file immediately!</p>
                <p style="margin-top: 5px;">Installation log saved to: <code><?php echo basename($log_file); ?></code></p>
            </div>

            <div style="text-align: center;">
                <a href="dashboard" class="btn-link">Go to Dashboard →</a>
                <p style="margin-top: 15px; color: #666;">
                    <small>You will be redirected to the login page if not logged in</small>
                </p>
            </div>

            <script>
                // Auto redirect after 5 seconds
                setTimeout(function() {
                    window.location.href = 'dashboard';
                }, 5000);
            </script>

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
            <p class="subtitle">NA-FIX ERP System - Complete Installation</p>

            <!-- System Requirements Check -->
            <div class="info-box" style="margin-bottom: 25px;">
                <h3>🔍 System Requirements Check</h3>
                <table style="width: 100%; margin-top: 15px; border-collapse: collapse;">
                    <?php
                    $all_passed = true;
                    foreach ($system_checks as $key => $check):
                        if (!$check['status'] && $check['required'] !== 'Optional') {
                            $all_passed = false;
                        }
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px 5px;">
                            <?php if ($check['status']): ?>
                                <span style="color: #28a745; font-size: 18px;">✓</span>
                            <?php else: ?>
                                <span style="color: #dc3545; font-size: 18px;">✗</span>
                            <?php endif; ?>
                            <?php echo $check['name']; ?>
                        </td>
                        <td style="padding: 10px 5px; text-align: right; color: #666;">
                            <?php echo $check['current']; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <?php if (!$all_passed): ?>
                <div class="alert alert-error" style="margin-top: 15px;">
                    <strong>⚠️ Warning:</strong> Some required checks failed. Please fix the issues above before proceeding.
                </div>
                <?php endif; ?>
            </div>

            <div class="alert alert-info">
                <strong>ℹ️ Setup Instructions:</strong><br>
                1. Ensure all system requirements are met (green checkmarks above)<br>
                2. Enter your MySQL database credentials below<br>
                3. Choose whether to import additional data (recommended)<br>
                4. Click "Install Database" button
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
                        <input type="checkbox" name="import_additional_data" value="yes" id="additional_data" checked>
                        <label for="additional_data" style="margin: 0;">Import Additional Data (Recommended)</label>
                    </div>
                    <small style="color: #666; margin-left: 30px; display: block; margin-top: 5px;">
                        Includes: Extra test users (manager, accountant, underwriter, claims), extended chart of accounts, comprehensive role permissions, opening balances, categories, units, departments, and more
                    </small>
                </div>

                <button type="submit" <?php echo !$all_passed ? 'disabled' : ''; ?>>🚀 Install Database</button>
            </form>

            <div class="info-box" style="margin-top: 30px;">
                <h3>📦 What will be installed:</h3>
                <ul>
                    <li>• <strong>100+ Database Tables</strong> (Complete ERP System)</li>
                    <li>• <strong>Double-Entry Accounting System</strong></li>
                    <li>• Complete Chart of Accounts (Assets, Liabilities, Equity, Income, Expenses)</li>
                    <li>• Insurance Management (Policies, Claims, Commissions, Brokers, Agents)</li>
                    <li>• Customer & Supplier Management</li>
                    <li>• Product Catalog & Inventory System</li>
                    <li>• Sales & Purchase Modules</li>
                    <li>• Receipt & Payment Processing</li>
                    <li>• Multi-Currency Support (10 currencies)</li>
                    <li>• VAT/Tax Management (UAE 5% VAT)</li>
                    <li>• Banking & Finance Modules</li>
                    <li>• User Authentication & Role-Based Access Control</li>
                    <li>• Audit Logs & Security Features</li>
                    <li>• Financial Reports & Analytics</li>
                </ul>
            </div>

            <div class="info-box" style="margin-top: 20px;">
                <h3>📋 Files to be imported:</h3>
                <ul>
                    <li>• <strong>fullscheme.sql</strong> - Complete database structure with 100+ tables and sample data</li>
                    <li>• <strong>additional_data.sql</strong> - Extra test users, accounts, and permissions (optional)</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
