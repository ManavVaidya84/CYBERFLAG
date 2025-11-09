<?php
// Start or resume session
session_start();

// Include database connection
require_once '../config/configdb.php';

// Initialize feedback variable
$feedback = isset($_SESSION['feedback']) ? $_SESSION['feedback'] : '';
unset($_SESSION['feedback']);

// Handle form submission and data insertion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $admin_name = trim(filter_input(INPUT_POST, 'admin_name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = trim($_POST['password']); // Password not sanitized to preserve special characters
    $confirm_password = trim($_POST['confirm_password']);

    // Validation checks
    if (empty($admin_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $feedback = "❌ All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback = "❌ Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $feedback = "❌ Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $feedback = "❌ Password must be at least 8 characters long.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tbl_admin_detail WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $feedback = "❌ Email already registered.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insert data into tbl_admin_detail
                $stmt = $pdo->prepare("INSERT INTO tbl_admin_detail (admin_name, email, password) VALUES (:admin_name, :email, :password)");
                $stmt->execute([
                    'admin_name' => $admin_name,
                    'email' => $email,
                    'password' => $hashed_password
                ]);

                // Success: Set feedback and redirect
                $feedback = "✅ Registration successful! Redirecting to login...";
                $_SESSION['feedback'] = $feedback;
                header("Location: index.php");
                exit();
            }
        } catch (PDOException $e) {
            // Log error and show user-friendly message
            error_log("Registration failed: " . $e->getMessage());
            $feedback = "❌ Error: Could not complete registration.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HACKERSTORM :: Admin Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono:wght@400&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Share Tech Mono', monospace;
            background: #000000;
            color: #00ff00;
            overflow-x: hidden;
        }

        .hacker-bg {
            background: 
                radial-gradient(circle at 30% 40%, rgba(0, 255, 0, 0.06) 0%, transparent 40%),
                radial-gradient(circle at 70% 30%, rgba(0, 255, 255, 0.04) 0%, transparent 40%),
                linear-gradient(180deg, #000000 0%, #001a00 50%, #000000 100%);
            background-size: 100% 100%;
            animation: backgroundShift 20s ease-in-out infinite;
        }

        @keyframes backgroundShift {
            0%, 100% { background-position: 0% 0%, 0% 0%, 0% 0%; }
            50% { background-position: 30% 30%, 70% 70%, 0% 0%; }
        }

        .matrix-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 1;
            opacity: 0.1;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 3px,
                rgba(0, 255, 0, 0.02) 3px,
                rgba(0, 255, 0, 0.02) 5px
            );
            animation: matrixScroll 25s linear infinite;
        }

        @keyframes matrixScroll {
            0% { transform: translateY(0); }
            100% { transform: translateY(120px); }
        }

        .terminal-glow {
            box-shadow: 
                0 0 15px rgba(0, 255, 0, 0.4),
                0 0 30px rgba(0, 255, 0, 0.2),
                inset 0 0 15px rgba(0, 255, 0, 0.05);
            border: 2px solid #00ff00;
            background: rgba(0, 0, 0, 0.85);
        }

        .terminal-scan::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #00ff00, transparent);
            animation: terminalScan 4s infinite;
        }

        @keyframes terminalScan {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .hack-button {
            background: linear-gradient(45deg, #000000, #001a00);
            border: 2px solid #00ff00;
            box-shadow: 0 0 8px #00ff00;
            transition: all 0.3s ease;
        }

        .hack-button:hover {
            box-shadow: 0 0 25px #00ff00;
            transform: scale(1.03);
        }

        .hack-input {
            background: rgba(0, 0, 0, 0.9);
            border: 1px solid #00ff00;
            color: #00ff00;
            font-family: 'Share Tech Mono', monospace;
            transition: all 0.3s ease;
        }

        .hack-input:focus {
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.4);
            outline: none;
        }

        .glitch-text {
            position: relative;
            animation: glitch 1.5s infinite;
        }

        .glitch-text::before,
        .glitch-text::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .glitch-text::before {
            animation: glitch-red 0.4s infinite;
            color: #ff0000;
            z-index: -1;
        }

        .glitch-text::after {
            animation: glitch-blue 0.4s infinite;
            color: #00ccff;
            z-index: -2;
        }

        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-1px, -1px); }
            40% { transform: translate(1px, 1px); }
            60% { transform: translate(-1px, 1px); }
            80% { transform: translate(1px, -1px); }
        }

        @keyframes glitch-red {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-1px, -1px); }
            40% { transform: translate(1px, 1px); }
            60% { transform: translate(-1px, 1px); }
            80% { transform: translate(1px, -1px); }
        }

        @keyframes glitch-blue {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(1px, 1px); }
            40% { transform: translate(-1px, -1px); }
            60% { transform: translate(1px, -1px); }
            80% { transform: translate(-1px, 1px); }
        }

        .error-message {
            border: 2px solid #ff0000;
            background: rgba(255, 0, 0, 0.1);
        }
    </style>
</head>
<body class="hacker-bg min-h-screen">
    <!-- Matrix Overlay -->
    <div class="matrix-overlay"></div>

    <!-- Main Container -->
    <div class="min-h-screen flex items-center justify-center p-8">
        <div class="terminal-glow rounded-lg w-full max-w-md p-10 terminal-scan relative">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-green-500">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-red-500 rounded-full animate-pulse"></div>
                    <div>
                        <h1 class="glitch-text text-3xl font-bold text-green-400" data-text="ADMIN REGISTRATION">ADMIN REGISTRATION</h1>
                        <div class="text-green-300 text-sm">HACKERSTORM CTF</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-red-400 font-mono text-sm" id="systemTime">00:00:00</div>
                    <div class="text-green-300 text-xs">SYSTEM TIME</div>
                </div>
            </div>

            <!-- Registration Form -->
            <form action="registration.php" method="POST" class="space-y-6">
                <div>
                    <label class="block text-green-400 font-mono text-sm mb-2">
                        <span class="flex items-center space-x-2">
                            <span>👤</span>
                            <span>ADMIN NAME</span>
                            <span class="text-red-400 animate-pulse">[REQUIRED]</span>
                        </span>
                    </label>
                    <input 
                        type="text" 
                        name="admin_name" 
                        placeholder="Enter your name" 
                        class="hack-input w-full px-4 py-3 rounded-lg text-lg"
                        autocomplete="off"
                        value="<?php echo isset($_POST['admin_name']) ? htmlspecialchars($_POST['admin_name']) : ''; ?>"
                    >
                </div>
                <div>
                    <label class="block text-green-400 font-mono text-sm mb-2">
                        <span class="flex items-center space-x-2">
                            <span>📧</span>
                            <span>EMAIL</span>
                            <span class="text-red-400 animate-pulse">[REQUIRED]</span>
                        </span>
                    </label>
                    <input 
                        type="email" 
                        name="email" 
                        placeholder="Enter your email" 
                        class="hack-input w-full px-4 py-3 rounded-lg text-lg"
                        autocomplete="off"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>
                <div>
                    <label class="block text-green-400 font-mono text-sm mb-2">
                        <span class="flex items-center space-x-2">
                            <span>🔒</span>
                            <span>PASSWORD</span>
                            <span class="text-red-400 animate-pulse">[REQUIRED]</span>
                        </span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        placeholder="Enter your password" 
                        class="hack-input w-full px-4 py-3 rounded-lg text-lg"
                        autocomplete="off"
                    >
                </div>
                <div>
                    <label class="block text-green-400 font-mono text-sm mb-2">
                        <span class="flex items-center space-x-2">
                            <span>🔒</span>
                            <span>CONFIRM PASSWORD</span>
                            <span class="text-red-400 animate-pulse">[REQUIRED]</span>
                        </span>
                    </label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        placeholder="Confirm your password" 
                        class="hack-input w-full px-4 py-3 rounded-lg text-lg"
                        autocomplete="off"
                    >
                </div>
                <button 
                    type="submit" 
                    class="hack-button w-full py-3 rounded-lg font-bold text-lg text-green-400"
                >
                    REGISTER
                </button>
            </form>

            <!-- Feedback -->
            <?php if ($feedback): ?>
            <div class="error-message rounded-lg p-4 mt-6">
                <p class="text-red-400 font-mono"><?php echo htmlspecialchars($feedback); ?></p>
            </div>
            <?php endif; ?>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <a href="index.php" class="text-green-400 hover:text-green-300 font-mono">Already registered? Login here</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-black bg-opacity-80 border-t border-green-500 p-4 z-20">
        <div class="flex justify-between items-center text-green-400 font-mono text-sm">
            <div>© 2025 HACKERSTORM CTF</div>
            <div class="flex space-x-4">
                <span>STATUS: <span class="text-red-400">REGISTRATION</span></span>
            </div>
        </div>
    </div>

    <script>
        // System time
        function updateSystemTime() {
            const now = new Date();
            document.getElementById('systemTime').textContent = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        // Initialize
        setInterval(updateSystemTime, 1000);
    </script>
</body>
</html>