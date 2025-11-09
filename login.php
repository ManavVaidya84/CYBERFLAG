<?php
// Start or resume session
session_start();

// Include database connection
require_once '../config/configdb.php';

// Initialize feedback
$feedback = isset($_SESSION['feedback']) ? $_SESSION['feedback'] : '';
unset($_SESSION['feedback']);

// Check if already logged in
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_name'])) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = trim($_POST['password']); // Password not sanitized to preserve special characters

    // Validation
    if (empty($email) || empty($password)) {
        $feedback = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback = "Invalid email format.";
    } else {
        try {
            // Check if email exists and fetch admin details
            $stmt = $pdo->prepare("SELECT admin_id, admin_name, password FROM tbl_admin_detail WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                // Successful login
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['admin_name'];
                $_SESSION['admin_email'] = $email;
                $_SESSION['feedback'] = "Login successful! Welcome, " . htmlspecialchars($admin['admin_name']) . ".";
                header("Location: index.php");
                exit();
            } else {
                $feedback = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log("Login failed: " . $e->getMessage());
            $feedback = "Error: Could not process login. Please try again.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CTF Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(12px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-radius: 1rem;
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            transform: translateY(20px);
            animation: slideIn 0.5s ease forwards;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .input-field {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            width: 100%;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
            outline: none;
        }
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .toggle-password:hover {
            color: #3b82f6;
        }
        .submit-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            width: 100%;
        }
        .submit-button:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .feedback-message {
            background: rgba(255, 255, 255, 0.97);
            border: 1px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(12px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        .register-link {
            color: #3b82f6;
            transition: color 0.3s ease;
        }
        .register-link:hover {
            color: #1d4ed8;
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1 class="text-2xl font-bold tracking-tight">Admin Login</h1>
            <p class="text-sm opacity-80">CTF Platform</p>
        </div>

        <!-- Login Form -->
        <form action="login.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email</label>
                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    class="input-field"
                    autocomplete="off"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                >
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <div class="password-container">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Enter your password"
                        class="input-field"
                        autocomplete="off"
                    >
                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>
            <button type="submit" class="submit-button">
                <i class="fas fa-sign-in-alt mr-2"></i> Login
            </button>
        </form>

        <!-- Feedback -->
        <?php if ($feedback): ?>
        <div class="feedback-message flex items-center space-x-2 <?php echo strpos($feedback, 'Error') === false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
            <i class="fas <?php echo strpos($feedback, 'Error') === false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($feedback); ?></span>
        </div>
        <?php endif; ?>

        <!-- Register Link
        <div class="mt-4 text-center">
            <a href="register.php" class="register-link font-medium">Need an account? Register here</a>
        </div> -->
    </div>

    <script>
        // Password toggle
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>