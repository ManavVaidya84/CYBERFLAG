<?php
session_start();
$current_page = 'index.php';

// Redirect to login if not logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
    error_log("Session missing: admin_id or admin_name not set");
    $_SESSION['feedback'] = "Please log in to access the admin dashboard.";
    header('Location: login.php');
    exit();
}

// Include database connection
require_once '../config/configdb.php';

// Initialize feedback
$feedback = isset($_SESSION['feedback']) ? $_SESSION['feedback'] : '';
unset($_SESSION['feedback']);

// Test database connection
try {
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $feedback = "❌ Error: Database connection failed. Please check configdb.php.";
    $total_students = 0;
    $active_users = 0;
    $challenge_stats = array_fill_keys(array_map(fn($i) => "c$i_completed", range(1, 20)), 0);
    $recent_activities = [];
    goto render_page;
}

// Check table existence
try {
    $pdo->query("SELECT 1 FROM tbl_user_detail LIMIT 1");
    $pdo->query("SELECT 1 FROM tbl_user_score LIMIT 1");
} catch (PDOException $e) {
    error_log("Table check failed: " . $e->getMessage());
    $feedback = "❌ Error: Tables tbl_user_detail or tbl_user_score are inaccessible.";
    $total_students = 0;
    $active_users = 0;
    $challenge_stats = array_fill_keys(array_map(fn($i) => "c$i_completed", range(1, 20)), 0);
    $recent_activities = [];
    goto render_page;
}

// Fetch total students
try {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM tbl_user_detail");
    $total_students = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    error_log("Error fetching total students: " . $e->getMessage());
    $total_students = 0;
    $feedback = "❌ Error: Could not fetch student count.";
}

// Fetch active users (activity in last 24 hours or non-null challenges)
try {
    $query = "SELECT COUNT(DISTINCT u.user_id) AS active_users 
              FROM tbl_user_detail u
              LEFT JOIN tbl_user_score s ON u.user_id = s.user_id
              WHERE u.created_at >= NOW() - INTERVAL 1 DAY";
    for ($i = 1; $i <= 20; $i++) {
        $query .= " OR s.c$i IS NOT NULL";
    }
    $stmt = $pdo->query($query);
    $active_users = $stmt->fetch(PDO::FETCH_ASSOC)['active_users'];
} catch (PDOException $e) {
    error_log("Error fetching active users: " . $e->getMessage());
    $active_users = 0;
    $feedback = "❌ Error: Could not fetch active users.";
}

// Fetch challenge completion stats
try {
    $query = "SELECT ";
    for ($i = 1; $i <= 20; $i++) {
        $query .= "SUM(CASE WHEN c$i IS NOT NULL THEN 1 ELSE 0 END) AS c{$i}_completed" . ($i < 20 ? "," : "");
    }
    $query .= " FROM tbl_user_score";
    $stmt = $pdo->query($query);
    $challenge_stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching challenge stats: " . $e->getMessage());
    $challenge_stats = array_fill_keys(array_map(fn($i) => "c$i_completed", range(1, 20)), 0);
    $feedback = "❌ Error: Could not fetch challenge stats.";
}

// Fetch recent activities
try {
    $stmt = $pdo->prepare("
        SELECT al.activity_type, al.activity_description, al.activity_time, ud.user_name
        FROM tbl_activity_log al
        LEFT JOIN tbl_user_detail ud ON al.user_id = ud.user_id
        ORDER BY al.activity_time DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching recent activities: " . $e->getMessage());
    $recent_activities = [];
    $feedback = "❌ Error: Could not fetch recent activities. Ensure tbl_activity_log exists.";
}

render_page:
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HACKERSTORM :: Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        },
                        dark: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'scale-in': 'scaleIn 0.3s ease-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(100px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.8)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' }
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
            font-family: 'JetBrains Mono', 'Courier New', monospace;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #475569 100%);
            background-attachment: fixed;
            overflow-x: hidden;
            color: #f1f5f9;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dashboard-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
            position: relative;
            overflow: hidden;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(59, 130, 246, 0.1) 50%, transparent 70%);
            animation: shimmer-header 3s ease-in-out infinite alternate;
        }

        @keyframes shimmer-header {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(241, 245, 249, 0.1) 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.5rem;
            box-shadow: 
                0 10px 25px -3px rgba(0, 0, 0, 0.2),
                0 4px 6px -2px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            color: #f1f5f9;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #06b6d4, #10b981);
            background-size: 400% 100%;
            animation: gradient-shift 3s ease infinite;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.3),
                0 10px 20px -5px rgba(0, 0, 0, 0.2);
        }

        .challenge-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(241, 245, 249, 0.1) 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.25rem;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            color: #f1f5f9;
        }

        .challenge-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            background-size: 200% 100%;
            animation: gradient-shift 2s ease infinite;
        }

        .challenge-card:hover {
            transform: translateY(-5px) rotateX(5deg);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: #3b82f6;
        }

        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .progress-bar {
            background: linear-gradient(90deg, #1e293b, #334155);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            background-size: 200% 100%;
            height: 100%;
            border-radius: 12px;
            transition: width 1s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            animation: gradient-shift 2s ease infinite;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(30px) scale(0.95); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1); 
            }
        }

        .icon-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 1rem;
            width: 3.5rem;
            height: 3.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }

        .icon-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
        }

        .time-display {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            border: 2px solid #4b5563;
            color: #10b981;
            font-family: 'JetBrains Mono', monospace;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .status-indicator {
            position: relative;
        }

        .status-indicator::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            animation: pulse-ring 2s cubic-bezier(0.25, 0.8, 0.25, 1) infinite;
        }

        .status-online::before {
            background: rgba(16, 185, 129, 0.3);
        }

        .status-active::before {
            background: rgba(59, 130, 246, 0.3);
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(2); opacity: 0; }
        }

        .quick-action-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(241, 245, 249, 0.1) 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.25rem;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            color: #f1f5f9;
        }

        .quick-action-card:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border-color: #3b82f6;
        }

        .quick-action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .quick-action-card:hover::before {
            transform: scaleX(1);
        }

        .floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .floating-shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        .metric-badge {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #f1f5f9;
            padding: 0.375rem 0.75rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .system-status-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(241, 245, 249, 0.1) 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            color: #f1f5f9;
        }

        .notification-dot {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 12px;
            height: 12px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: pulse-slow 2s infinite;
        }

        .activity-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-left: 4px solid;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .activity-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        @media (max-width: 768px) {
            .dashboard-header {
                padding: 2rem 1rem;
            }
            .stat-card, .challenge-card, .quick-action-card, .system-status-card {
                margin-bottom: 1rem;
            }
            .ml-64 {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-dark-900 via-dark-800 to-dark-900 ">
    <?php include 'sidebar.php'; ?>

    <div class="floating-shapes">
        <div class="floating-shape w-32 h-32 bg-blue-500 rounded-full"></div>
        <div class="floating-shape w-24 h-24 bg-purple-500 rounded-full"></div>
        <div class="floating-shape w-20 h-20 bg-green-500 rounded-full"></div>
    </div>

    <div class="ml-64 min-h-screen">
        <div class="dashboard-header p-12 relative">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-start">
                    <div class="fade-in">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mr-4 shadow-lg">
                                <i class="fas fa-shield-alt text-2xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-5xl font-bold mb-2 bg-gradient-to-r from-white to-blue-200 bg-clip-text text-transparent">
                                    Admin Dashboard
                                </h1>
                                <div class="flex items-center gap-4">
                                    <p class="text-gray-100 text-lg">Welcome back, <span class="font-semibold text-white"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>!</p>
                                    <div class="status-indicator status-active w-3 h-3 bg-green-400 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-200 text-base">Manage your HACKERSTORM CTF platform with advanced controls</p>
                    </div>
                    <div class="time-display px-6 py-4 rounded-xl border-2">
                        <div class="text-xs text-gray-400 mb-1 tracking-wide">SYSTEM TIME</div>
                        <div class="font-mono text-xl font-semibold" id="systemTime">00:00:00</div>
                        <div class="text-xs text-green-400 mt-1">UTC +5:30</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8 -mt-8">
            <div class="max-w-7xl mx-auto">
                <!-- Platform Statistics -->
                <div class="mb-12">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-3xl font-bold text-white">Platform Overview</h2>
                        <div class="metric-badge">
                            <i class="fas fa-chart-line"></i>
                            Live Metrics
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="stat-card p-8 fade-in">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <p class="text-gray-300 text-sm font-semibold tracking-wide uppercase">Total Students</p>
                                    </div>
                                    <p class="text-4xl font-bold text-white mb-1"><?php echo number_format($total_students); ?></p>
                                    <div class="flex items-center mt-3">
                                        <span class="text-green-400 text-sm font-medium flex items-center">
                                            <i class="fas fa-arrow-up mr-2"></i>
                                            Registered users
                                        </span>
                                    </div>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-users text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="stat-card p-8 fade-in" style="animation-delay: 0.1s;">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <p class="text-gray-300 text-sm font-semibold tracking-wide uppercase">Active Participants</p>
                                        <div class="notification-dot"></div>
                                    </div>
                                    <p class="text-4xl font-bold text-white mb-1"><?php echo number_format($active_users); ?></p>
                                    <div class="flex items-center mt-3">
                                        <span class="text-blue-400 text-sm font-medium flex items-center">
                                            <i class="fas fa-bolt mr-2"></i>
                                            Recent activity
                                        </span>
                                    </div>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-chart-line text-white text-xl"></i>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>

                <!-- Challenge Completion Stats -->
                <div class="mb-12">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-3xl font-bold text-white">Challenge Analytics</h2>
                        <div class="metric-badge">
                            <i class="fas fa-trophy"></i>
                            20 Challenges Active
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php 
                        $challenge_labels = [
                            'c1' => 'Web Exploitation 1',
                            'c2' => 'Cryptography 1',
                            'c3' => 'Forensics 1',
                            'c4' => 'Reverse Engineering 1',
                            'c5' => 'Binary Exploitation 1',
                            'c6' => 'Network Security 1',
                            'c7' => 'OSINT 1',
                            'c8' => 'Web Exploitation 2',
                            'c9' => 'Cryptography 2',
                            'c10' => 'Forensics 2',
                            'c11' => 'Reverse Engineering 2',
                            'c12' => 'Binary Exploitation 2',
                            'c13' => 'Network Security 2',
                            'c14' => 'OSINT 2',
                            'c15' => 'Web Exploitation 3',
                            'c16' => 'Cryptography 3',
                            'c17' => 'Forensics 3',
                            'c18' => 'Reverse Engineering 3',
                            'c19' => 'Binary Exploitation 3',
                            'c20' => 'Network Security 3'
                        ];
                        $colors = [
                            'from-red-500 to-pink-600',
                            'from-blue-500 to-cyan-600',
                            'from-green-500 to-emerald-600',
                            'from-purple-500 to-violet-600',
                            'from-orange-500 to-amber-600',
                            'from-indigo-500 to-blue-600',
                            'from-teal-500 to-green-600',
                            'from-red-600 to-pink-700',
                            'from-blue-600 to-cyan-700',
                            'from-green-600 to-emerald-700',
                            'from-purple-600 to-violet-700',
                            'from-orange-600 to-amber-700',
                            'from-indigo-600 to-blue-700',
                            'from-teal-600 to-green-700',
                            'from-red-400 to-pink-500',
                            'from-blue-400 to-cyan-500',
                            'from-green-400 to-emerald-500',
                            'from-purple-400 to-violet-500',
                            'from-orange-400 to-amber-500',
                            'from-indigo-400 to-blue-500'
                        ];
                        $icons = [
                            'fas fa-globe',
                            'fas fa-key',
                            'fas fa-search',
                            'fas fa-cogs',
                            'fas fa-bug',
                            'fas fa-network-wired',
                            'fas fa-eye',
                            'fas fa-globe',
                            'fas fa-key',
                            'fas fa-search',
                            'fas fa-cogs',
                            'fas fa-bug',
                            'fas fa-network-wired',
                            'fas fa-eye',
                            'fas fa-globe',
                            'fas fa-key',
                            'fas fa-search',
                            'fas fa-cogs',
                            'fas fa-bug',
                            'fas fa-network-wired'
                        ];
                        $index = 0;
                        foreach ($challenge_labels as $key => $label): 
                            $completed = $challenge_stats[$key . '_completed'];
                            $completion_rate = $total_students > 0 ? round(($completed / $total_students) * 100, 1) : 0;
                        ?>
                        <div class="challenge-card p-6 fade-in" style="animation-delay: <?php echo 0.2 + ($index * 0.1); ?>s;">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="font-bold text-white mb-1 text-lg"><?php echo $label; ?></h3>
                                    <p class="text-gray-300 text-sm font-medium"><?php echo number_format($completed); ?> completions</p>
                                </div>
                                <div class="bg-gradient-to-r <?php echo $colors[$index]; ?> p-3 rounded-xl shadow-lg">
                                    <i class="<?php echo $icons[$index]; ?> text-white text-lg"></i>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-300 font-medium">Completion Rate</span>
                                    <span class="font-bold text-white text-lg"><?php echo $completion_rate; ?>%</span>
                                </div>
                                <div class="progress-bar h-3">
                                    <div class="progress-fill" style="width: <?php echo $completion_rate; ?>%;"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-400">
                                    <span>0%</span>
                                    <span>50%</span>
                                    <span>100%</span>
                                </div>
                            </div>
                        </div>
                        <?php 
                        $index++;
                        endforeach; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mb-12">
                    <h2 class="text-3xl font-bold text-white mb-8">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <a href="manage_users.php" class="quick-action-card p-8 text-center hover:no-underline fade-in group block" style="animation-delay: 0.9s;">
                            <div class="icon-wrapper mx-auto mb-4">
                                <i class="fas fa-user-plus text-white text-xl"></i>
                            </div>
                            <h3 class="font-bold text-white mb-2 text-lg group-hover:text-blue-400 transition-colors">Manage Users</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">Add, edit, or remove users from the platform</p>
                        </a>
                       
                        <a href="leaderboard.php" class="quick-action-card p-8 text-center hover:no-underline fade-in group block" style="animation-delay: 1.1s;">
                            <div class="icon-wrapper mx-auto mb-4">
                                <i class="fas fa-medal text-white text-xl"></i>
                            </div>
                            <h3 class="font-bold text-white mb-2 text-lg group-hover:text-blue-400 transition-colors">View Rankings</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">Check live leaderboard and standings</p>
                        </a>
                        <a href="view_score.php" class="quick-action-card p-8 text-center hover:no-underline fade-in group block" style="animation-delay: 1.2s;">
                            <div class="icon-wrapper mx-auto mb-4">
                                <i class="fas fa-chart-bar text-white text-xl"></i>
                            </div>
                            <h3 class="font-bold text-white mb-2 text-lg group-hover:text-blue-400 transition-colors">Score Analytics</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">Detailed score analysis and insights</p>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity
                <div class="mb-12">
                    <h2 class="text-3xl font-bold text-white mb-8">Recent Activity</h2>
                    <div class="system-status-card p-8 fade-in" style="animation-delay: 1.3s;">
                        <div class="space-y-4">
                            <?php if (empty($recent_activities)): ?>
                            <div class="flex items-center p-4 bg-blue-50/10 rounded-xl border-l-4 border-blue-500">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-info-circle text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-white">No recent activity</p>
                                    <p class="text-gray-300 text-sm">No activities recorded in the last 24 hours</p>
                                </div>
                            </div>
                            <?php else: ?>
                            <?php 
                            $activity_colors = [
                                'registration' => 'border-blue-500 bg-blue-50/10',
                                'challenge_completion' => 'border-green-500 bg-green-50/10'
                            ];
                            $activity_icons = [
                                'registration' => 'fas fa-user-plus',
                                'challenge_completion' => 'fas fa-flag'
                            ];
                            foreach ($recent_activities as $activity): 
                                $time_ago = (new DateTime())->diff(new DateTime($activity['activity_time']));
                                $time_display = $time_ago->h > 0 ? $time_ago->h . ' hour' . ($time_ago->h > 1 ? 's' : '') . ' ago' : 
                                               ($time_ago->i > 0 ? $time_ago->i . ' min' . ($time_ago->i > 1 ? 's' : '') . ' ago' : 'just now');
                            ?>
                            <div class="flex items-center p-4 activity-card <?php echo $activity_colors[$activity['activity_type']]; ?>">
                                <div class="w-10 h-10 bg-<?php echo $activity['activity_type'] === 'registration' ? 'blue' : 'green'; ?>-500 rounded-full flex items-center justify-center mr-4">
                                    <i class="<?php echo $activity_icons[$activity['activity_type']]; ?> text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-white"><?php echo htmlspecialchars($activity['activity_description']); ?></p>
                                    <p class="text-gray-300 text-sm">By <?php echo htmlspecialchars($activity['user_name'] ?? 'Unknown'); ?></p>
                                </div>
                                <span class="text-gray-400 text-sm"><?php echo $time_display; ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div> -->

                <!-- Feedback Messages -->
                <?php if ($feedback): ?>
                <!-- <div class="fade-in mb-8" style="animation-delay: 1.5s;">
                    <div class="bg-red-50/10 border-l-4 border-red-500 p-6 rounded-xl shadow-lg">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-exclamation-triangle text-white"></i>
                            </div>
                            <div>
                                <p class="text-red-400 font-semibold">System Alert</p>
                                <p class="text-red-300"><?php echo htmlspecialchars($feedback); ?></p>
                            </div>
                        </div>
                    </div>
                </div> -->
                <?php endif; ?>

                <!-- System Status -->
                <div class="mb-12">
                    <h2 class="text-3xl font-bold text-white mb-8">System Health</h2>
                    <div class="system-status-card p-8 fade-in" style="animation-delay: 1.3s;">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="status-indicator status-online w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                                    <span class="font-bold text-white text-lg">Database</span>
                                </div>
                                <div class="bg-green-50/10 border-2 border-green-200/20 rounded-xl p-4">
                                    <p class="text-green-400 font-semibold mb-1">Connected</p>
                                    <p class="text-green-300 text-sm">Latency: 2ms</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="status-indicator status-online w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                                    <span class="font-bold text-white text-lg">Server</span>
                                </div>
                                <div class="bg-green-50/10 border-2 border-green-200/20 rounded-xl p-4">
                                    <p class="text-green-400 font-semibold mb-1">Online</p>
                                    <p class="text-green-300 text-sm">Uptime: 99.9%</p>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="status-indicator status-active w-4 h-4 bg-blue-500 rounded-full mr-3"></div>
                                    <span class="font-bold text-white text-lg">Session</span>
                                </div>
                                <div class="bg-blue-50/10 border-2 border-blue-200/20 rounded-xl p-4">
                                    <p class="text-blue-400 font-semibold mb-1">Active</p>
                                    <p class="text-blue-300 text-sm">Secure Connection</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-8 pt-6 border-t border-gray-600">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
                                <div>
                                    <div class="text-2xl font-bold text-white mb-1">256GB</div>
                                    <div class="text-gray-300 text-sm">Storage Used</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-white mb-1">12GB</div>
                                    <div class="text-gray-300 text-sm">Memory Usage</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-white mb-1">2.1GHz</div>
                                    <div class="text-gray-300 text-sm">CPU Frequency</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-white mb-1">45°C</div>
                                    <div class="text-gray-300 text-sm">Temperature</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       
    </div>

    <script>
        function updateSystemTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('systemTime').textContent = timeString;
        }
        updateSystemTime();
        setInterval(updateSystemTime, 1000);

        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px) scale(0.95)';
                setTimeout(() => {
                    el.style.transition = 'all 0.8s cubic-bezier(0.25, 0.8, 0.25, 1)';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0) scale(1)';
                }, index * 100);
            });
        });

        document.querySelectorAll('.stat-card, .challenge-card, .quick-action-card, .activity-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        function animateProgressBars() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        }
        setTimeout(animateProgressBars, 1000);

        function createParticle() {
            const particle = document.createElement('div');
            particle.className = 'absolute w-1 h-1 bg-blue-400 rounded-full opacity-30';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = '100%';
            particle.style.animation = 'float 8s linear infinite';
            document.body.appendChild(particle);
            setTimeout(() => particle.remove(), 8000);
        }
        setInterval(createParticle, 3000);
    </script>
</body>
</html>