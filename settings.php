<?php
session_start();
require_once '../config/configdb.php';

// Restrict access to logged-in admins
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
    $_SESSION['feedback'] = "Please log in to access this page.";
    header('Location: login.php');
    exit();
}

// Initialize variables
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];
$admin_email = '';
$feedback = isset($_SESSION['feedback']) ? $_SESSION['feedback'] : '';
unset($_SESSION['feedback']);

// Test database connection
try {
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $feedback = "Error: Database connection failed. Please check configdb.php.";
    $admin_name = $_SESSION['admin_name'];
    $admin_email = 'N/A';
    goto render_page;
}

// Check table existence
try {
    $pdo->query("SELECT 1 FROM tbl_admin_detail LIMIT 1");
} catch (PDOException $e) {
    error_log("Table check failed: " . $e->getMessage());
    $feedback = "Error: Table tbl_admin_detail is inaccessible.";
    $admin_name = $_SESSION['admin_name'];
    $admin_email = 'N/A';
    goto render_page;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_admin_name = filter_input(INPUT_POST, 'admin_name', FILTER_SANITIZE_STRING);
    $new_admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);
    $current_password = filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($new_admin_name) || empty($new_admin_email)) {
        $feedback = "Error: Name and email are required.";
    } elseif (!filter_var($new_admin_email, FILTER_VALIDATE_EMAIL)) {
        $feedback = "Error: Invalid email format.";
    } else {
        try {
            // Fetch current admin details for password verification
            $stmt = $pdo->prepare("SELECT admin_name, email, password FROM tbl_admin_detail WHERE admin_id = :admin_id");
            $stmt->execute(['admin_id' => $admin_id]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {
                $feedback = "Error: Admin account not found.";
                goto render_page;
            }

            $update_fields = ['admin_name' => $new_admin_name, 'email' => $new_admin_email];
            $params = [':admin_name' => $new_admin_name, ':email' => $new_admin_email, ':admin_id' => $admin_id];

            // Handle password update if provided
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    $feedback = "Error: All password fields are required to change password.";
                    goto render_page;
                }
                if (!password_verify($current_password, $admin['password'])) {
                    $feedback = "Error: Incorrect current password.";
                    goto render_page;
                }
                if ($new_password !== $confirm_password) {
                    $feedback = "Error: New password and confirm password do not match.";
                    goto render_page;
                }
                if (strlen($new_password) < 6) {
                    $feedback = "Error: New password must be at least 6 characters.";
                    goto render_page;
                }
                $update_fields['password'] = password_hash($new_password, PASSWORD_BCRYPT);
                $params[':password'] = $update_fields['password'];
            }

            // Update admin details
            $set_clause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($update_fields)));
            $stmt = $pdo->prepare("UPDATE tbl_admin_detail SET $set_clause WHERE admin_id = :admin_id");
            $stmt->execute($params);
            $_SESSION['admin_name'] = $new_admin_name;
            $feedback = "Profile updated successfully.";
            $admin_name = $new_admin_name;
            $admin_email = $new_admin_email;
        } catch (PDOException $e) {
            error_log("Error updating admin: " . $e->getMessage());
            $feedback = "Error: Could not update profile.";
        }
    }
}

// Fetch current admin details
try {
    $stmt = $pdo->prepare("SELECT admin_name, email FROM tbl_admin_detail WHERE admin_id = :admin_id");
    $stmt->execute(['admin_id' => $admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_name = $admin['admin_name'] ?? $_SESSION['admin_name'];
    $admin_email = $admin['email'] ?? 'N/A';
} catch (PDOException $e) {
    error_log("Error fetching admin details: " . $e->getMessage());
    $feedback = "Error: Could not fetch admin details.";
    $admin_name = $_SESSION['admin_name'];
    $admin_email = 'N/A';
}

render_page:
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HACKERSTORM :: Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            400: '#60a5fa',
                            500: '#3b82f6',
                            700: '#1d4ed8'
                        },
                        dark: {
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a'
                        },
                        success: '#22c55e',
                        error: '#ef4444'
                    },
                    animation: {
                        'shimmer': 'shimmer 2s ease infinite',
                        'fade-in': 'fadeIn 0.8s ease-out',
                        'translate-x': 'translateX 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                    },
                    keyframes: {
                        shimmer: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(100%)' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        translateX: {
                            '0%': { transform: 'translateX(0)' },
                            '100%': { transform: 'translateX(4px)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap');

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        body {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            background-attachment: fixed;
            color: #f1f5f9;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .main-content {
            margin-left: 16rem;
            transition: margin-left 0.3s ease;
            padding: 1.5rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.75rem;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .card:hover {
            transform: translateX(4px);
            box-shadow: 
                0 12px 35px rgba(59, 130, 246, 0.2),
                0 0 0 1px rgba(59, 130, 246, 0.1);
        }

        .card:hover::before {
            left: 100%;
        }

        .action-button {
            background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            color: #f1f5f9;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .action-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .action-button:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .action-button:hover::before {
            left: 100%;
        }

        .form-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.5rem;
            padding: 0.75rem 2.5rem 0.75rem 2.5rem;
            color: #f1f5f9;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
            outline: none;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .input-container {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            transition: opacity 0.3s ease;
        }

        .password-toggle:hover {
            opacity: 0.7;
        }

        .feedback-message {
            border-left: 3px solid;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feedback-message.success {
            border-color: #22c55e;
        }

        .feedback-message.error {
            border-color: #ef4444;
        }

        .toggle-button {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.75rem;
            padding: 0.5rem;
            color: #f1f5f9;
            transition: all 0.3s ease;
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 40;
        }

        .toggle-button:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.3);
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            .toggle-button {
                display: block;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <button class="toggle-button" id="toggleSidebar">
        <i class="fas fa-bars text-lg"></i>
    </button>

    <div class="main-content">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-white">Settings</h1>
                <div class="metric-badge bg-primary-500/10 text-primary-400 px-3 py-1 rounded-full text-sm font-mono flex items-center gap-2">
                    <i class="fas fa-user-cog"></i>
                    Admin ID: <?php echo htmlspecialchars($admin_id); ?>
                </div>
            </div>

            <!-- Feedback Message -->
            <?php if ($feedback): ?>
            <div class="card feedback-message p-6 mb-8 flex items-center gap-3 fade-in <?php echo strpos($feedback, 'Error') === false ? 'success border-l-3' : 'error border-l-3'; ?>">
                <i class="fas <?php echo strpos($feedback, 'Error') === false ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-error'; ?>"></i>
                <span class="text-sm"><?php echo htmlspecialchars($feedback); ?></span>
            </div>
            <?php endif; ?>

            <!-- Admin Settings Form -->
            <div class="card p-8 max-w-lg mx-auto fade-in">
                <h2 class="text-xl font-bold text-white mb-6">Update Admin Profile</h2>
                <form method="POST" action="settings.php">
                    <div class="mb-6 input-container">
                        <i class="fas fa-id-badge input-icon"></i>
                        <label for="admin_id" class="block text-gray-300 text-sm font-semibold mb-2">Admin ID</label>
                        <input type="text" id="admin_id" value="<?php echo htmlspecialchars($admin_id); ?>" class="form-input font-mono" readonly>
                    </div>
                    <div class="mb-6 input-container">
                        <i class="fas fa-user input-icon"></i>
                        <label for="admin_name" class="block text-gray-300 text-sm font-semibold mb-2">Name</label>
                        <input type="text" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin_name); ?>" class="form-input" placeholder="Enter your name" required>
                    </div>
                    <div class="mb-6 input-container">
                        <i class="fas fa-envelope input-icon"></i>
                        <label for="admin_email" class="block text-gray-300 text-sm font-semibold mb-2">Email</label>
                        <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin_email); ?>" class="form-input" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-6 input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <label for="current_password" class="block text-gray-300 text-sm font-semibold mb-2">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-input font-mono" placeholder="Enter current password">
                        <i class="fas fa-eye password-toggle" id="toggleCurrentPassword"></i>
                    </div>
                    <div class="mb-6 input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <label for="new_password" class="block text-gray-300 text-sm font-semibold mb-2">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-input font-mono" placeholder="Enter new password">
                        <i class="fas fa-eye password-toggle" id="toggleNewPassword"></i>
                    </div>
                    <div class="mb-6 input-container">
                        <i class="fas fa-lock input-icon"></i>
                        <label for="confirm_password" class="block text-gray-300 text-sm font-semibold mb-2">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input font-mono" placeholder="Confirm new password">
                        <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                    </div>
                    <div class="flex justify-end gap-4">
                        <button type="submit" class="action-button">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                        <a href="index.php" class="action-button bg-gray-600">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.getElementById('toggleSidebar');
            const mainContent = document.querySelector('.main-content');

            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                mainContent.classList.toggle('ml-64');
            });

            // Password toggle functionality
            const toggleCurrentPassword = document.getElementById('toggleCurrentPassword');
            const currentPasswordInput = document.getElementById('current_password');
            const toggleNewPassword = document.getElementById('toggleNewPassword');
            const newPasswordInput = document.getElementById('new_password');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordInput = document.getElementById('confirm_password');

            toggleCurrentPassword.addEventListener('click', function() {
                const type = currentPasswordInput.type === 'password' ? 'text' : 'password';
                currentPasswordInput.type = type;
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            toggleNewPassword.addEventListener('click', function() {
                const type = newPasswordInput.type === 'password' ? 'text' : 'password';
                newPasswordInput.type = type;
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
                confirmPasswordInput.type = type;
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            // Auto-hide sidebar on mobile navigation
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.addEventListener('click', function() {
                        sidebar.classList.remove('mobile-open');
                        mainContent.classList.remove('ml-64');
                    });
                });
            }

            // Fade-in animations
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    el.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Card and button hover effects
            document.querySelectorAll('.card, .action-button').forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(4px)';
                });
                el.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
        });
    </script>
</body>
</html>