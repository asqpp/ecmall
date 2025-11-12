<?php
/**
 * =====================================================
 * NA-FIX ERP System - System Checker & Diagnostic Tool
 * =====================================================
 *
 * This diagnostic tool checks:
 * - PHP version and extensions
 * - File and directory permissions
 * - Database connectivity
 * - Configuration files
 * - Required files existence
 * - Common code issues
 * - Security settings
 *
 * Run this anytime to diagnose system issues
 * Access: http://yourdomain.com/system_check.php
 *
 * =====================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize results array
$results = [
    'php_environment' => [],
    'extensions' => [],
    'file_permissions' => [],
    'database' => [],
    'configuration' => [],
    'files' => [],
    'security' => [],
    'recommendations' => []
];

$overall_status = 'pass'; // pass, warning, fail

/**
 * Add result to array
 */
function add_result(&$category, $name, $status, $message, $fix = null) {
    global $overall_status;

    $category[] = [
        'name' => $name,
        'status' => $status, // pass, warning, fail
        'message' => $message,
        'fix' => $fix
    ];

    // Update overall status
    if ($status === 'fail') {
        $overall_status = 'fail';
    } elseif ($status === 'warning' && $overall_status !== 'fail') {
        $overall_status = 'warning';
    }
}

// =====================================================
// 1. PHP ENVIRONMENT CHECKS
// =====================================================

// PHP Version
$php_version = phpversion();
if (version_compare($php_version, '7.2.0', '>=')) {
    add_result($results['php_environment'], 'PHP Version', 'pass',
        "PHP $php_version (Required: 7.2+)");
} else {
    add_result($results['php_environment'], 'PHP Version', 'fail',
        "PHP $php_version is too old. Required: 7.2+",
        "Upgrade PHP to version 7.2 or higher");
}

// Memory Limit
$memory_limit = ini_get('memory_limit');
$memory_bytes = return_bytes($memory_limit);
if ($memory_bytes >= 128 * 1024 * 1024) { // 128MB
    add_result($results['php_environment'], 'Memory Limit', 'pass',
        "Memory limit: $memory_limit (Recommended: 128M+)");
} else {
    add_result($results['php_environment'], 'Memory Limit', 'warning',
        "Memory limit: $memory_limit (Low, recommended: 128M+)",
        "Increase memory_limit in php.ini to 128M or higher");
}

// Max Execution Time
$max_execution_time = ini_get('max_execution_time');
if ($max_execution_time >= 60 || $max_execution_time == 0) {
    add_result($results['php_environment'], 'Max Execution Time', 'pass',
        "Max execution time: {$max_execution_time}s");
} else {
    add_result($results['php_environment'], 'Max Execution Time', 'warning',
        "Max execution time: {$max_execution_time}s (Low, recommended: 60+)",
        "Increase max_execution_time in php.ini to 60 or higher");
}

// Upload Max Filesize
$upload_max = ini_get('upload_max_filesize');
add_result($results['php_environment'], 'Upload Max Filesize', 'pass',
    "Upload max filesize: $upload_max");

// Post Max Size
$post_max = ini_get('post_max_size');
add_result($results['php_environment'], 'Post Max Size', 'pass',
    "Post max size: $post_max");

// =====================================================
// 2. PHP EXTENSIONS
// =====================================================

$required_extensions = [
    'mysqli' => 'Required for MySQL database connection',
    'mbstring' => 'Required for multi-byte string handling',
    'json' => 'Required for JSON processing',
    'curl' => 'Recommended for external API calls',
    'gd' => 'Recommended for image processing',
    'zip' => 'Recommended for backup functionality',
    'xml' => 'Recommended for XML processing'
];

foreach ($required_extensions as $ext => $description) {
    if (extension_loaded($ext)) {
        add_result($results['extensions'], $ext, 'pass',
            "Extension '$ext' is enabled ($description)");
    } else {
        $status = in_array($ext, ['mysqli', 'mbstring', 'json']) ? 'fail' : 'warning';
        add_result($results['extensions'], $ext, $status,
            "Extension '$ext' is NOT enabled ($description)",
            "Enable $ext extension in php.ini");
    }
}

// =====================================================
// 3. FILE PERMISSIONS
// =====================================================

$directories_to_check = [
    'application/cache' => true,  // must be writable
    'application/logs' => true,   // must be writable
    'application/config' => false, // should be readable
    'assets/uploads' => true,     // must be writable
    'logs' => true                // must be writable (if exists)
];

foreach ($directories_to_check as $dir => $must_be_writable) {
    $path = __DIR__ . '/' . $dir;

    if (!file_exists($path)) {
        if ($must_be_writable && $dir !== 'logs') {
            add_result($results['file_permissions'], $dir, 'fail',
                "Directory '$dir' does not exist",
                "Create directory: mkdir -p $dir && chmod 755 $dir");
        } else {
            add_result($results['file_permissions'], $dir, 'warning',
                "Directory '$dir' does not exist (optional)");
        }
        continue;
    }

    if ($must_be_writable) {
        if (is_writable($path)) {
            add_result($results['file_permissions'], $dir, 'pass',
                "Directory '$dir' is writable ✓");
        } else {
            add_result($results['file_permissions'], $dir, 'fail',
                "Directory '$dir' is NOT writable",
                "Fix permissions: chmod -R 755 $dir");
        }
    } else {
        if (is_readable($path)) {
            add_result($results['file_permissions'], $dir, 'pass',
                "Directory '$dir' is readable ✓");
        } else {
            add_result($results['file_permissions'], $dir, 'fail',
                "Directory '$dir' is NOT readable",
                "Fix permissions: chmod -R 755 $dir");
        }
    }
}

// Check critical files
$critical_files = [
    'index.php',
    'application/config/config.php',
    'application/config/database.php',
    'application/config/routes.php',
    'fullscheme.sql',
    'additional_data.sql'
];

foreach ($critical_files as $file) {
    $path = __DIR__ . '/' . $file;

    if (file_exists($path)) {
        if (is_readable($path)) {
            add_result($results['files'], $file, 'pass',
                "File '$file' exists and is readable ✓");
        } else {
            add_result($results['files'], $file, 'fail',
                "File '$file' exists but is NOT readable",
                "Fix permissions: chmod 644 $file");
        }
    } else {
        $status = in_array($file, ['index.php', 'application/config/config.php', 'application/config/database.php']) ? 'fail' : 'warning';
        add_result($results['files'], $file, $status,
            "File '$file' does NOT exist",
            $status === 'fail' ? "This is a critical file - please restore it" : "Optional file - not critical");
    }
}

// =====================================================
// 4. DATABASE CONNECTIVITY
// =====================================================

$db_config_file = __DIR__ . '/application/config/database.php';
if (file_exists($db_config_file)) {
    include($db_config_file);

    if (isset($db['default'])) {
        $db_config = $db['default'];

        // Test connection
        try {
            @$conn = new mysqli(
                $db_config['hostname'],
                $db_config['username'],
                $db_config['password'],
                $db_config['database']
            );

            if ($conn->connect_error) {
                add_result($results['database'], 'Database Connection', 'fail',
                    "Cannot connect to database: " . $conn->connect_error,
                    "Check database credentials in application/config/database.php");
            } else {
                add_result($results['database'], 'Database Connection', 'pass',
                    "Successfully connected to database '{$db_config['database']}'");

                // Check table count
                $result = $conn->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '{$db_config['database']}'");
                if ($result) {
                    $row = $result->fetch_assoc();
                    $table_count = $row['table_count'];

                    if ($table_count >= 100) {
                        add_result($results['database'], 'Database Tables', 'pass',
                            "Database has $table_count tables (Complete installation)");
                    } elseif ($table_count > 0) {
                        add_result($results['database'], 'Database Tables', 'warning',
                            "Database has only $table_count tables (Expected: 100+)",
                            "Database may not be fully installed. Run setup.php to complete installation");
                    } else {
                        add_result($results['database'], 'Database Tables', 'fail',
                            "Database is empty (no tables found)",
                            "Run setup.php to install the database");
                    }

                    $result->free();
                }

                // Check critical tables
                $critical_tables = ['users', 'roles', 'accounts', 'invoice', 'policies'];
                foreach ($critical_tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        // Table exists - check if it has data
                        $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                        if ($count_result) {
                            $count_row = $count_result->fetch_assoc();
                            $record_count = $count_row['count'];
                            add_result($results['database'], "Table: $table", 'pass',
                                "Table '$table' exists with $record_count records");
                            $count_result->free();
                        }
                    } else {
                        add_result($results['database'], "Table: $table", 'fail',
                            "Critical table '$table' does not exist",
                            "Run setup.php to install the database");
                    }
                    if ($result) $result->free();
                }

                $conn->close();
            }
        } catch (Exception $e) {
            add_result($results['database'], 'Database Connection', 'fail',
                "Database connection error: " . $e->getMessage(),
                "Check database credentials and ensure MySQL server is running");
        }
    } else {
        add_result($results['database'], 'Database Configuration', 'fail',
            "Database configuration is missing or invalid",
            "Check application/config/database.php file");
    }
} else {
    add_result($results['database'], 'Database Configuration', 'fail',
        "Database configuration file not found",
        "Ensure application/config/database.php exists");
}

// =====================================================
// 5. CONFIGURATION CHECKS
// =====================================================

// Check config.php
$config_file = __DIR__ . '/application/config/config.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);

    // Check base_url
    if (preg_match("/\\\$config\['base_url'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $base_url = $matches[1];
        if (empty($base_url) || $base_url === 'http://localhost/') {
            add_result($results['configuration'], 'Base URL', 'warning',
                "Base URL is not configured or set to default: $base_url",
                "Update base_url in application/config/config.php to your actual domain");
        } else {
            add_result($results['configuration'], 'Base URL', 'pass',
                "Base URL is configured: $base_url");
        }
    }

    // Check encryption key
    if (preg_match("/\\\$config\['encryption_key'\]\s*=\s*'([^']+)'/", $config_content, $matches)) {
        $encryption_key = $matches[1];
        if (empty($encryption_key)) {
            add_result($results['configuration'], 'Encryption Key', 'warning',
                "Encryption key is not set",
                "Set a random encryption key in application/config/config.php for security");
        } else {
            add_result($results['configuration'], 'Encryption Key', 'pass',
                "Encryption key is configured");
        }
    }
} else {
    add_result($results['configuration'], 'Configuration File', 'fail',
        "application/config/config.php not found",
        "Restore the configuration file from backup");
}

// Check routes.php
$routes_file = __DIR__ . '/application/config/routes.php';
if (file_exists($routes_file)) {
    $routes_content = file_get_contents($routes_file);

    if (preg_match("/\\\$route\['default_controller'\]\s*=\s*'([^']+)'/", $routes_content, $matches)) {
        $default_controller = $matches[1];
        add_result($results['configuration'], 'Default Controller', 'pass',
            "Default controller is set to: $default_controller");
    } else {
        add_result($results['configuration'], 'Default Controller', 'warning',
            "Default controller not found in routes configuration");
    }
}

// =====================================================
// 6. SECURITY CHECKS
// =====================================================

// Check if setup.php exists (security risk)
if (file_exists(__DIR__ . '/setup.php')) {
    if (file_exists(__DIR__ . '/SETUP_COMPLETE.lock')) {
        add_result($results['security'], 'Setup Script', 'warning',
            "setup.php still exists after installation",
            "DELETE or RENAME setup.php for security reasons");
    } else {
        add_result($results['security'], 'Setup Script', 'pass',
            "setup.php exists but installation not complete - this is OK");
    }
}

// Check error reporting in production
$display_errors = ini_get('display_errors');
if ($display_errors) {
    add_result($results['security'], 'Error Display', 'warning',
        "Error display is enabled (display_errors = On)",
        "Disable display_errors in php.ini for production environment");
} else {
    add_result($results['security'], 'Error Display', 'pass',
        "Error display is disabled (Good for production)");
}

// Check if index.php has proper security headers
if (file_exists(__DIR__ . '/index.php')) {
    $index_content = file_get_contents(__DIR__ . '/index.php');

    if (strpos($index_content, 'BASEPATH') !== false) {
        add_result($results['security'], 'Direct Access Protection', 'pass',
            "BASEPATH constant is used to prevent direct access");
    } else {
        add_result($results['security'], 'Direct Access Protection', 'warning',
            "BASEPATH constant not found in index.php");
    }
}

// =====================================================
// 7. RECOMMENDATIONS
// =====================================================

if ($overall_status === 'pass') {
    $results['recommendations'][] = "✓ All critical checks passed! Your system is properly configured.";
    $results['recommendations'][] = "• Keep your PHP version updated for security";
    $results['recommendations'][] = "• Regularly backup your database and files";
    $results['recommendations'][] = "• Monitor application logs for errors";
} elseif ($overall_status === 'warning') {
    $results['recommendations'][] = "⚠️ Your system is functional but has some warnings";
    $results['recommendations'][] = "• Review the warnings above and fix them when possible";
    $results['recommendations'][] = "• Some warnings may not affect functionality";
} else {
    $results['recommendations'][] = "❌ Critical issues found! Your system may not work properly";
    $results['recommendations'][] = "• Fix all FAILED checks immediately";
    $results['recommendations'][] = "• Review the suggested fixes for each failed check";
}

// Helper function
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int) $val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Checker - NA-FIX ERP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
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
        .status-header {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }
        .status-pass {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }
        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }
        .status-fail {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        .section {
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            font-size: 18px;
            font-weight: 600;
        }
        .section-content {
            padding: 20px;
        }
        .check-item {
            display: flex;
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            align-items: flex-start;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .check-status {
            width: 30px;
            font-size: 20px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .check-details {
            flex: 1;
        }
        .check-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .check-message {
            color: #666;
            font-size: 14px;
        }
        .check-fix {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            color: #495057;
        }
        .check-fix::before {
            content: "💡 Fix: ";
            font-weight: bold;
            color: #667eea;
        }
        .recommendations {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .recommendations h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .recommendations ul {
            list-style: none;
            padding: 0;
        }
        .recommendations li {
            padding: 8px 0;
            color: #495057;
        }
        .btn-refresh {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-refresh:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 System Checker & Diagnostic Tool</h1>
        <p class="subtitle">NA-FIX ERP System Health Check</p>

        <!-- Overall Status -->
        <div class="status-header status-<?php echo $overall_status; ?>">
            <?php
            if ($overall_status === 'pass') {
                echo "✅ ALL CHECKS PASSED";
            } elseif ($overall_status === 'warning') {
                echo "⚠️ SOME WARNINGS FOUND";
            } else {
                echo "❌ CRITICAL ISSUES FOUND";
            }
            ?>
        </div>

        <!-- PHP Environment -->
        <div class="section">
            <div class="section-header">🐘 PHP Environment</div>
            <div class="section-content">
                <?php foreach ($results['php_environment'] as $check): ?>
                    <div class="check-item">
                        <div class="check-status">
                            <?php
                            if ($check['status'] === 'pass') echo '✅';
                            elseif ($check['status'] === 'warning') echo '⚠️';
                            else echo '❌';
                            ?>
                        </div>
                        <div class="check-details">
                            <div class="check-name"><?php echo $check['name']; ?></div>
                            <div class="check-message"><?php echo $check['message']; ?></div>
                            <?php if ($check['fix']): ?>
                                <div class="check-fix"><?php echo $check['fix']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- PHP Extensions -->
        <div class="section">
            <div class="section-header">🔌 PHP Extensions</div>
            <div class="section-content">
                <?php foreach ($results['extensions'] as $check): ?>
                    <div class="check-item">
                        <div class="check-status">
                            <?php
                            if ($check['status'] === 'pass') echo '✅';
                            elseif ($check['status'] === 'warning') echo '⚠️';
                            else echo '❌';
                            ?>
                        </div>
                        <div class="check-details">
                            <div class="check-name"><?php echo $check['name']; ?></div>
                            <div class="check-message"><?php echo $check['message']; ?></div>
                            <?php if ($check['fix']): ?>
                                <div class="check-fix"><?php echo $check['fix']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- File Permissions -->
        <div class="section">
            <div class="section-header">📁 File Permissions</div>
            <div class="section-content">
                <?php foreach ($results['file_permissions'] as $check): ?>
                    <div class="check-item">
                        <div class="check-status">
                            <?php
                            if ($check['status'] === 'pass') echo '✅';
                            elseif ($check['status'] === 'warning') echo '⚠️';
                            else echo '❌';
                            ?>
                        </div>
                        <div class="check-details">
                            <div class="check-name"><?php echo $check['name']; ?></div>
                            <div class="check-message"><?php echo $check['message']; ?></div>
                            <?php if ($check['fix']): ?>
                                <div class="check-fix"><?php echo $check['fix']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Files -->
        <div class="section">
            <div class="section-header">📄 Required Files</div>
            <div class="section-content">
                <?php foreach ($results['files'] as $check): ?>
                    <div class="check-item">
                        <div class="check-status">
                            <?php
                            if ($check['status'] === 'pass') echo '✅';
                            elseif ($check['status'] === 'warning') echo '⚠️';
                            else echo '❌';
                            ?>
                        </div>
                        <div class="check-details">
                            <div class="check-name"><?php echo $check['name']; ?></div>
                            <div class="check-message"><?php echo $check['message']; ?></div>
                            <?php if ($check['fix']): ?>
                                <div class="check-fix"><?php echo $check['fix']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Database -->
        <div class="section">
            <div class="section-header">🗄️ Database</div>
            <div class="section-content">
                <?php foreach ($results['database'] as $check): ?>
                    <div class="check-item">
                        <div class="check-status">
                            <?php
                            if ($check['status'] === 'pass') echo '✅';
                            elseif ($check['status'] === 'warning') echo '⚠️';
                            else echo '❌';
                            ?>
                        </div>
                        <div class="check-details">
                            <div class="check-name"><?php echo $check['name']; ?></div>
                            <div class="check-message"><?php echo $check['message']; ?></div>
                            <?php if ($check['fix']): ?>
                                <div class="check-fix"><?php echo $check['fix']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Configuration -->
        <div class="section">
            <div class="section-header">⚙️ Configuration</div>
            <div class="section-content">
                <?php foreach ($results['configuration'] as $check): ?>
                    <div class="check-item">
                        <div class="check-status">
                            <?php
                            if ($check['status'] === 'pass') echo '✅';
                            elseif ($check['status'] === 'warning') echo '⚠️';
                            else echo '❌';
                            ?>
                        </div>
                        <div class="check-details">
                            <div class="check-name"><?php echo $check['name']; ?></div>
                            <div class="check-message"><?php echo $check['message']; ?></div>
                            <?php if ($check['fix']): ?>
                                <div class="check-fix"><?php echo $check['fix']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Security -->
        <div class="section">
            <div class="section-header">🔒 Security</div>
            <div class="section-content">
                <?php foreach ($results['security'] as $check): ?>
                    <div class="check-item">
                        <div class="check-status">
                            <?php
                            if ($check['status'] === 'pass') echo '✅';
                            elseif ($check['status'] === 'warning') echo '⚠️';
                            else echo '❌';
                            ?>
                        </div>
                        <div class="check-details">
                            <div class="check-name"><?php echo $check['name']; ?></div>
                            <div class="check-message"><?php echo $check['message']; ?></div>
                            <?php if ($check['fix']): ?>
                                <div class="check-fix"><?php echo $check['fix']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="recommendations">
            <h3>💡 Recommendations</h3>
            <ul>
                <?php foreach ($results['recommendations'] as $recommendation): ?>
                    <li><?php echo $recommendation; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="text-align: center;">
            <a href="?" class="btn-refresh">🔄 Refresh Check</a>
            <a href="dashboard" class="btn-refresh" style="background: #28a745;">Go to Dashboard →</a>
        </div>

        <div style="margin-top: 20px; text-align: center; color: #666; font-size: 14px;">
            Generated on: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
</body>
</html>
