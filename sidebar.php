<?php
// Assume session_start() is called in the including file (e.g., index.php)
// $current_page should be set in the including file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HACKERSTORM :: Admin Panel</title>
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
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    },
                    animation: {
                        'shimmer': 'shimmer 2s ease infinite',
                        'pulse-dot': 'pulse-dot 2s infinite',
                        'translate-x': 'translateX 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
                    },
                    keyframes: {
                        shimmer: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(100%)' }
                        },
                        pulse-dot: {
                            '0%': { transform: 'scale(1)', opacity: '1' },
                            '100%': { transform: 'scale(1.4)', opacity: '0' }
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
            min-h-screen;
            overflow-x: hidden;
        }

        .sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(71, 85, 105, 0.3);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.25),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 16rem;
            z-index: 30;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(59, 130, 246, 0.03) 50%, transparent 70%);
            pointer-events: none;
        }

        .brand-section {
            position: relative;
            padding: 2rem 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(71, 85, 105, 0.2);
            margin-bottom: 1rem;
        }

        .brand-logo {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: 2px solid rgba(59, 130, 246, 0.3);
            box-shadow: 
                0 8px 25px rgba(59, 130, 246, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .brand-logo::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .brand-logo:hover {
            transform: scale(1.05) rotate(5deg);
            box-shadow: 
                0 12px 35px rgba(59, 130, 246, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.2);
        }

        .brand-logo:hover::before {
            left: 100%;
        }

        .brand-text {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            color: rgba(148, 163, 184, 0.9);
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.25rem;
        }

        .admin-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 0.75rem;
            padding: 0.75rem;
            margin-top: 1rem;
        }

        .admin-name {
            color: #60a5fa;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .admin-email {
            color: rgba(148, 163, 184, 0.8);
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 0.25rem;
        }

        .nav-section {
            padding: 0 1.5rem;
            flex: 1;
        }

        .nav-item {
            margin-bottom: 0.375rem;
            position: relative;
        }

        .nav-link {
            background: rgba(51, 65, 85, 0.3);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            color: rgba(203, 213, 225, 0.9);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateX(4px);
            box-shadow: 
                0 4px 12px rgba(59, 130, 246, 0.2),
                0 0 0 1px rgba(59, 130, 246, 0.1);
            color: #e2e8f0;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(29, 78, 216, 0.1) 100%);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #60a5fa;
            font-weight: 600;
            box-shadow: 
                0 8px 25px rgba(59, 130, 246, 0.3),
                0 0 0 1px rgba(59, 130, 246, 0.2);
            transform: translateX(6px);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: linear-gradient(to bottom, #3b82f6, #1d4ed8);
            border-radius: 0 2px 2px 0;
        }

        .nav-icon {
            font-size: 1rem;
            width: 1.25rem;
            text-align: center;
            transition: all 0.3s ease;
            color: inherit;
        }

        .nav-link:hover .nav-icon {
            transform: scale(1.1);
        }

        .nav-link.active .nav-icon {
            color: #3b82f6;
            transform: scale(1.1);
        }

        .nav-text {
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .nav-link.active .nav-text {
            font-weight: 600;
        }

        .nav-divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(71, 85, 105, 0.4), transparent);
            margin: 1.5rem 0;
        }

        .logout-section {
            padding: 0 1.5rem 1.5rem;
            border-top: 1px solid rgba(71, 85, 105, 0.2);
            margin-top: 1.5rem;
            padding-top: 1.5rem;
        }

        .logout-link {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
        }

        .logout-link:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            box-shadow: 
                0 4px 12px rgba(239, 68, 68, 0.3),
                0 0 0 1px rgba(239, 68, 68, 0.2);
        }

        .logout-link:hover .nav-icon {
            color: #ef4444;
            transform: scale(1.1) rotate(-5deg);
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(71, 85, 105, 0.2);
            text-align: center;
            background: rgba(15, 23, 42, 0.5);
        }

        .footer-text {
            color: rgba(148, 163, 184, 0.7);
            font-size: 0.75rem;
            line-height: 1.5;
        }

        .version-badge {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            padding: 0.125rem 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.625rem;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            border: 1px solid rgba(34, 197, 94, 0.2);
            margin-top: 0.5rem;
            display: inline-block;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(71, 85, 105, 0.1);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.3);
            border-radius: 3px;
            transition: background 0.3s ease;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(71, 85, 105, 0.5);
        }

        .main-content {
            margin-left: 16rem;
            transition: margin-left 0.3s ease;
            padding: 1.5rem;
            min-height: 100vh;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.75rem;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .content-card:hover {
            transform: translateX(4px);
            box-shadow: 
                0 12px 35px rgba(59, 130, 246, 0.2),
                0 0 0 1px rgba(59, 130, 246, 0.1);
        }

        .content-card:hover::before {
            left: 100%;
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
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .toggle-button {
                display: block;
            }
        }

        .status-dot {
            position: relative;
        }

        .status-dot::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            background: inherit;
            animation: pulse-dot 2s infinite;
        }
    </style>
</head>
<body>
    <div class="sidebar flex flex-col">
        <!-- Brand Section -->
        <div class="brand-section">
            <div class="flex items-center gap-3">
                <div class="brand-logo w-12 h-12 rounded-xl flex items-center justify-center cursor-pointer">
                    <i class="fas fa-shield-alt text-white text-lg"></i>
                </div>
                <div class="flex-1">
                    <h2 class="brand-text text-xl">HACKERSTORM</h2>
                    <div class="brand-subtitle">Admin Panel</div>
                </div>
            </div>
            <div class="admin-info">
                <div class="flex items-center gap-2">
                    <div class="status-dot w-2 h-2 bg-green-400 rounded-full"></div>
                    <div class="admin-name"><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Administrator'; ?></div>
                </div>
                <div class="admin-email"><?php echo isset($_SESSION['admin_email']) ? htmlspecialchars($_SESSION['admin_email']) : 'admin@system.local'; ?></div>
            </div>
        </div>

        <!-- Navigation Section -->
        <nav class="nav-section flex-1">
            <div class="space-y-1">
                <div class="nav-item">
                    <a href="index.php" class="nav-link <?php echo isset($current_page) && $current_page === 'index.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="manage_users.php" class="nav-link <?php echo isset($current_page) && $current_page === 'manage_users.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-users"></i>
                        <span class="nav-text">User Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="view_score.php" class="nav-link <?php echo isset($current_page) && $current_page === 'view_score.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <span class="nav-text">Score Analytics</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="leaderboard.php" class="nav-link <?php echo isset($current_page) && $current_page === 'leaderboard.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-trophy"></i>
                        <span class="nav-text">Leaderboard</span>
                    </a>
                </div>
                <div class="nav-divider"></div>
                <div class="nav-item">
                    <a href="settings.php" class="nav-link <?php echo isset($current_page) && $current_page === 'settings.php' ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-cog"></i>
                        <span class="nav-text">Settings</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Logout Section -->
        <div class="logout-section">
            <div class="nav-item">
                <a href="logout.php" class="nav-link logout-link" onclick="return confirm('Are you sure you want to log out?');">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <span class="nav-text">Logout</span>
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="sidebar-footer">
            <div class="footer-text">
                © 2025 CTF Platform
                <div class="version-badge">v2.1.0</div>
            </div>
        </div>
    </div>

    <button class="toggle-button" id="toggleSidebar">
        <i class="fas fa-bars text-lg"></i>
    </button>

    <!-- <div class="main-content">
        <div class="content-card max-w-7xl mx-auto">
            <h1 class="text-2xl font-bold text-white mb-4">Welcome to HACKERSTORM Admin Panel</h1>
            <p class="text-gray-300 text-sm">Select an option from the sidebar to manage the CTF platform.</p>
        </div>
    </div> -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle
            const sidebar = document.querySelector('.sidebar');
            const toggleButton = document.getElementById('toggleSidebar');
            const mainContent = document.querySelector('.main-content');

            toggleButton.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
                if (sidebar.classList.contains('mobile-open')) {
                    mainContent.style.marginLeft = '16rem';
                } else {
                    mainContent.style.marginLeft = '0';
                }
            });

            // Navigation hover effects
            const navLinks = document.querySelectorAll('.nav-link:not(.logout-link)');
            navLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(6px) scale(1.02)';
                });
                link.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateX(0) scale(1)';
                    }
                });
            });

            // Brand logo click animation
            const brandLogo = document.querySelector('.brand-logo');
            brandLogo.addEventListener('click', function() {
                this.style.transform = 'scale(0.95) rotate(360deg)';
                setTimeout(() => {
                    this.style.transform = 'scale(1) rotate(0deg)';
                }, 300);
            });

            // Auto-hide sidebar on mobile navigation
            const isMobile = window.innerWidth <= 768;
            if (isMobile) {
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        sidebar.classList.remove('mobile-open');
                        mainContent.style.marginLeft = '0';
                    });
                });
            }

            // Content card hover effect
            const contentCard = document.querySelector('.content-card');
            contentCard.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(4px)';
            });
            contentCard.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    </script>
</body>
</html>