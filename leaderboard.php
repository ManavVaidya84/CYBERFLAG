<?php
session_start();
require_once '../config/configdb.php';

// Restrict access to logged-in admins
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
    $_SESSION['feedback'] = "Please log in to access this page.";
    header('Location: login.php');
    exit();
}

// Initialize feedback
$feedback = isset($_SESSION['feedback']) ? $_SESSION['feedback'] : '';
unset($_SESSION['feedback']);

// Pagination settings
$per_page = isset($_GET['per_page']) ? filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 5]]) : 10;
$per_page = in_array($per_page, [5, 10, 20, 50]) ? $per_page : 10;
$page = isset($_GET['page']) ? filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]) : 1;
$offset = ($page - 1) * $per_page;

// Function to parse MM:SS to seconds
function parseTimeToSeconds($time) {
    if (preg_match('/^\d{2}:\d{2}$/', $time)) {
        list($minutes, $seconds) = explode(':', $time);
        return (int)$minutes * 60 + (int)$seconds;
    }
    return null;
}

// Function to format seconds to MM:SS
function formatSecondsToTime($seconds) {
    if ($seconds === null || $seconds === 0) {
        return 'N/A';
    }
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $seconds);
}

// Test database connection
try {
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $feedback = "Error: Database connection failed. Please check configdb.php.";
    $scores = [];
    $total_pages = 1;
    goto render_page;
}

// Check table existence
try {
    $pdo->query("SELECT 1 FROM tbl_user_score LIMIT 1");
    $pdo->query("SELECT 1 FROM tbl_user_detail LIMIT 1");
} catch (PDOException $e) {
    error_log("Table check failed: " . $e->getMessage());
    $feedback = "Error: Tables tbl_user_score or tbl_user_detail are inaccessible.";
    $scores = [];
    $total_pages = 1;
    goto render_page;
}

// Fetch total score count
try {
    $count_query = "SELECT COUNT(*) FROM tbl_user_score us LEFT JOIN tbl_user_detail ud ON us.user_id = ud.user_id";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute();
    $total_scores = $stmt->fetchColumn();
    $total_pages = max(1, ceil($total_scores / $per_page));
} catch (PDOException $e) {
    error_log("Error fetching total scores: " . $e->getMessage());
    $feedback = "Error: Could not fetch score count.";
    $total_scores = 0;
    $total_pages = 1;
    $scores = [];
    goto render_page;
}

// Fetch scores with average time and solved challenges
try {
    $query = "SELECT us.user_id, ud.user_name, us.total_point, 
                     us.c1, us.c2, us.c3, us.c4, us.c5, us.c6, us.c7, us.c8, us.c9, us.c10,
                     us.c11, us.c12, us.c13, us.c14, us.c15, us.c16, us.c17, us.c18, us.c19, us.c20
              FROM tbl_user_score us 
              LEFT JOIN tbl_user_detail ud ON us.user_id = ud.user_id
              ORDER BY us.total_point DESC, (
                  SELECT AVG(time_in_seconds)
                  FROM (
                      SELECT parseTimeToSeconds(c1) AS time_in_seconds WHERE c1 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c2) WHERE c2 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c3) WHERE c3 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c4) WHERE c4 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c5) WHERE c5 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c6) WHERE c6 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c7) WHERE c7 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c8) WHERE c8 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c9) WHERE c9 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c10) WHERE c10 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c11) WHERE c11 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c12) WHERE c12 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c13) WHERE c13 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c14) WHERE c14 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c15) WHERE c15 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c16) WHERE c16 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c17) WHERE c17 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c18) WHERE c18 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c19) WHERE c19 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c20) WHERE c20 IS NOT NULL
                  ) times
              ) ASC
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $raw_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate average time and solved challenges
    $scores = [];
    foreach ($raw_scores as $score) {
        $times = [];
        $solved_challenges = 0;
        for ($i = 1; $i <= 20; $i++) {
            $key = "c$i";
            if ($score[$key] && preg_match('/^\d{2}:\d{2}$/', $score[$key])) {
                $seconds = parseTimeToSeconds($score[$key]);
                if ($seconds !== null) {
                    $times[] = $seconds;
                    $solved_challenges++;
                }
            }
        }
        $average_time = !empty($times) ? formatSecondsToTime(array_sum($times) / count($times)) : 'N/A';
        $score['average_time'] = $average_time;
        $score['solved_challenges'] = $solved_challenges;
        $scores[] = $score;
    }
} catch (PDOException $e) {
    error_log("Error fetching scores: " . $e->getMessage());
    $feedback = "Error: Could not fetch scores. Ensure tables tbl_user_score and tbl_user_detail exist.";
    $scores = [];
}

render_page:
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HACKERSTORM :: Leaderboard</title>
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
                        'pulse-dot': 'pulse-dot 2s infinite',
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

        .table-header {
            background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
            color: #f1f5f9;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table-row {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .table-row:hover {
            background: rgba(59, 130, 246, 0.15);
            transform: translateX(4px);
        }

        .table-row.updated {
            animation: fadeIn 0.5s ease-out;
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

        .modal {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            transition: opacity 0.3s ease;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.75rem;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.2),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .modal-content.show {
            transform: translateY(0);
        }

        .modal-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            color: #f1f5f9;
            transition: all 0.3s ease;
        }

        .modal-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
            outline: none;
        }

        .modal-input::placeholder {
            color: #94a3b8;
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

        .pagination-button {
            background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            color: #f1f5f9;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .pagination-button:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .pagination-button:disabled {
            background: rgba(71, 85, 105, 0.3);
            border-color: rgba(71, 85, 105, 0.2);
            cursor: not-allowed;
            transform: none;
        }

        .pagination-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.5rem;
            padding: 0.5rem;
            color: #f1f5f9;
            transition: all 0.3s ease;
        }

        .pagination-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
            outline: none;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(71, 85, 105, 0.2);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            color: #f1f5f9;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 20rem;
        }

        .search-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
            outline: none;
        }

        .search-input::placeholder {
            color: #94a3b8;
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
                <h1 class="text-2xl font-bold text-white">Leaderboard</h1>
                <div class="metric-badge bg-primary-500/10 text-primary-400 px-3 py-1 rounded-full text-sm font-mono flex items-center gap-2">
                    <i class="fas fa-trophy"></i>
                    <?php echo number_format($total_scores); ?> Scores
                </div>
            </div>

            <!-- Feedback Message -->
            <?php if ($feedback): ?>
            <div class="card feedback-message p-6 mb-8 flex items-center gap-3 fade-in <?php echo strpos($feedback, 'Error') === false ? 'success border-l-3' : 'error border-l-3'; ?>">
                <i class="fas <?php echo strpos($feedback, 'Error') === false ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-error'; ?>"></i>
                <span class="text-sm"><?php echo htmlspecialchars($feedback); ?></span>
            </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="mb-6 relative">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" class="search-input" placeholder="Search by username...">
            </div>

            <!-- Leaderboard Table -->
            <div class="card p-8">
                <table class="w-full text-left">
                    <thead class="table-header">
                        <tr>
                            <th class="p-5 text-sm font-semibold">Rank</th>
                            <th class="p-5 text-sm font-semibold">User ID</th>
                            <th class="p-5 text-sm font-semibold">Name</th>
                            <th class="p-5 text-sm font-semibold">Total Points</th>
                            <th class="p-5 text-sm font-semibold">Average Time</th>
                            <th class="p-5 text-sm font-semibold">Solved Challenges</th>
                            <th class="p-5 text-sm font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="leaderboardBody">
                        <?php if (empty($scores)): ?>
                        <tr class="table-row no-scores">
                            <td colspan="7" class="p-5 text-center text-gray-300">No scores found.</td>
                        </tr>
                        <?php else: ?>
                        <?php $rank = $offset + 1; ?>
                        <?php foreach ($scores as $score): ?>
                        <tr class="table-row border-b border-gray-600/50" data-user-id="<?php echo htmlspecialchars($score['user_id']); ?>" data-username="<?php echo htmlspecialchars($score['user_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>">
                            <td class="p-5 rank"><?php echo $rank++; ?></td>
                            <td class="p-5 font-mono"><?php echo htmlspecialchars($score['user_id']); ?></td>
                            <td class="p-5"><?php echo htmlspecialchars($score['user_name'] ?? 'N/A'); ?></td>
                            <td class="p-5 font-mono"><?php echo htmlspecialchars($score['total_point']); ?></td>
                            <td class="p-5 font-mono"><?php echo htmlspecialchars($score['average_time']); ?></td>
                            <td class="p-5 font-mono"><?php echo htmlspecialchars($score['solved_challenges']); ?></td>
                            <td class="p-5">
                                <button onclick="openDetailsModal(<?php echo $score['user_id']; ?>)" class="action-button">
                                    <i class="fas fa-eye mr-2"></i> View Details
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-6 flex justify-between items-center">
                    <div class="flex items-center space-x-3">
                        <span class="text-gray-300 text-sm font-medium">Show</span>
                        <select id="perPageSelect" class="pagination-select font-mono">
                            <option value="5" <?php echo $per_page == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                        </select>
                        <span class="text-gray-300 text-sm font-medium">entries</span>
                    </div>
                    <div class="flex space-x-2">
                        <a href="?page=<?php echo max(1, $page - 1); ?>&per_page=<?php echo $per_page; ?>" class="pagination-button <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-left mr-2"></i> Previous
                        </a>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>" class="pagination-button <?php echo $page == $i ? 'bg-primary-600' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        <a href="?page=<?php echo min($total_pages, $page + 1); ?>&per_page=<?php echo $per_page; ?>" class="pagination-button <?php echo $page >= $total_pages ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $page >= $total_pages ? 'disabled' : ''; ?>>
                            Next <i class="fas fa-chevron-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div id="detailsModal" class="modal hidden fixed inset-0 flex items-center justify-center z-50">
            <div class="modal-content card p-8 w-full max-w-2xl">
                <h2 class="text-xl font-bold text-white mb-6">Score Details</h2>
                <div id="modalContent" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Populated by JavaScript -->
                </div>
                <div class="flex justify-end mt-6">
                    <button type="button" onclick="closeDetailsModal()" class="action-button bg-gray-600">
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                </div>
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

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const leaderboardBody = document.getElementById('leaderboardBody');
            let rows = leaderboardBody.querySelectorAll('.table-row:not(.no-scores)');

            function updateSearch() {
                const searchTerm = searchInput.value.trim().toLowerCase();
                let visibleRowCount = 0;

                rows.forEach(row => {
                    const username = row.getAttribute('data-username').toLowerCase();
                    if (username.includes(searchTerm)) {
                        row.style.display = '';
                        visibleRowCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update ranks
                const visibleRows = leaderboardBody.querySelectorAll('.table-row:not([style*="display: none"])');
                visibleRows.forEach((row, index) => {
                    const rankCell = row.querySelector('.rank');
                    if (rankCell) {
                        rankCell.textContent = index + 1;
                    }
                });

                // Show "No scores found" if no matches
                const noScoresRow = leaderboardBody.querySelector('.no-scores');
                if (visibleRowCount === 0 && !noScoresRow) {
                    const noScoresRow = document.createElement('tr');
                    noScoresRow.className = 'table-row no-scores';
                    noScoresRow.innerHTML = '<td colspan="7" class="p-5 text-center text-gray-300">No scores found.</td>';
                    leaderboardBody.appendChild(noScoresRow);
                } else if (visibleRowCount > 0 && noScoresRow) {
                    noScoresRow.remove();
                }
            }

            searchInput.addEventListener('input', updateSearch);

            // Pagination change
            const perPageSelect = document.getElementById('perPageSelect');
            perPageSelect.addEventListener('change', function() {
                window.location.href = `?page=1&per_page=${this.value}`;
            });

            // Modal open/close
            function openDetailsModal(userId) {
                fetch('leaderboard.php?get_details=' + userId)
                    .then(response => response.json())
                    .then(data => {
                        if (!data || Object.keys(data).length === 0) {
                            alert('Error: Could not fetch score details.');
                            return;
                        }
                        const modalContent = document.getElementById('modalContent');
                        modalContent.innerHTML = `
                            <div class="mb-4 flex items-center gap-3">
                                <i class="fas fa-id-badge text-primary-400"></i>
                                <div class="flex-1">
                                    <label class="block text-gray-300 text-sm font-semibold mb-2">User ID</label>
                                    <input type="text" value="${data.user_id}" class="modal-input w-full font-mono" readonly>
                                </div>
                            </div>
                            <div class="mb-4 flex items-center gap-3">
                                <i class="fas fa-user text-primary-400"></i>
                                <div class="flex-1">
                                    <label class="block text-gray-300 text-sm font-semibold mb-2">Name</label>
                                    <input type="text" value="${data.user_name || 'N/A'}" class="modal-input w-full" readonly>
                                </div>
                            </div>
                            <div class="mb-4 flex items-center gap-3">
                                <i class="fas fa-trophy text-primary-400"></i>
                                <div class="flex-1">
                                    <label class="block text-gray-300 text-sm font-semibold mb-2">Total Points</label>
                                    <input type="text" value="${data.total_point || 0}" class="modal-input w-full font-mono" readonly>
                                </div>
                            </div>
                            <div class="mb-4 flex items-center gap-3">
                                <i class="fas fa-check-circle text-primary-400"></i>
                                <div class="flex-1">
                                    <label class="block text-gray-300 text-sm font-semibold mb-2">Solved Challenges</label>
                                    <input type="text" value="${data.solved_challenges || 0}" class="modal-input w-full font-mono" readonly>
                                </div>
                            </div>
                            <div class="mb-4 flex items-center gap-3">
                                <i class="fas fa-clock text-primary-400"></i>
                                <div class="flex-1">
                                    <label class="block text-gray-300 text-sm font-semibold mb-2">Average Time</label>
                                    <input type="text" value="${data.average_time || 'N/A'}" class="modal-input w-full font-mono" readonly>
                                </div>
                            </div>
                            <div class="mb-4 flex items-center gap-3">
                                <i class="fas fa-clock text-primary-400"></i>
                                <div class="flex-1">
                                    <label class="block text-gray-300 text-sm font-semibold mb-2">Score Created At</label>
                                    <input type="text" value="${data.score_created_at || 'N/A'}" class="modal-input w-full font-mono" readonly>
                                </div>
                            </div>
                            ${Object.keys(data).filter(key => key.startsWith('c') && !isNaN(key.slice(1))).map(key => `
                                <div class="mb-4 flex items-center gap-3">
                                    <i class="fas fa-check-circle text-primary-400"></i>
                                    <div class="flex-1">
                                        <label class="block text-gray-300 text-sm font-semibold mb-2">Challenge ${key.slice(1)}</label>
                                        <input type="text" value="${data[key] || 'N/A'}" class="modal-input w-full font-mono" readonly>
                                    </div>
                                </div>
                            `).join('')}
                        `;
                        const modal = document.getElementById('detailsModal');
                        const modalContent = modal.querySelector('.modal-content');
                        modal.classList.remove('hidden');
                        setTimeout(() => modalContent.classList.add('show'), 10);
                    })
                    .catch(error => {
                        console.error('Error fetching details:', error);
                        alert('Error: Could not fetch score details.');
                    });
            }

            function closeDetailsModal() {
                const modal = document.getElementById('detailsModal');
                const modalContent = modal.querySelector('.modal-content');
                modalContent.classList.remove('show');
                setTimeout(() => modal.classList.add('hidden'), 400);
            }

            // Live update functionality
            function fetchLeaderboard() {
                const perPage = perPageSelect.value;
                const currentPage = <?php echo $page; ?>;
                fetch(`leaderboard.php?get_scores=1&page=${currentPage}&per_page=${perPage}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.scores && data.scores.length > 0) {
                            const noScoresRow = leaderboardBody.querySelector('.no-scores');
                            if (noScoresRow) noScoresRow.remove();

                            const existingRows = Array.from(leaderboardBody.querySelectorAll('.table-row:not(.no-scores)'));
                            const newRows = data.scores.map((score, index) => {
                                const row = document.createElement('tr');
                                row.className = 'table-row border-b border-gray-600/50';
                                row.dataset.userId = score.user_id;
                                row.dataset.username = score.user_name || 'N/A';
                                row.innerHTML = `
                                    <td class="p-5 rank">${index + 1 + ((currentPage - 1) * perPage)}</td>
                                    <td class="p-5 font-mono">${score.user_id}</td>
                                    <td class="p-5">${score.user_name || 'N/A'}</td>
                                    <td class="p-5 font-mono">${score.total_point}</td>
                                    <td class="p-5 font-mono">${score.average_time}</td>
                                    <td class="p-5 font-mono">${score.solved_challenges}</td>
                                    <td class="p-5">
                                        <button onclick="openDetailsModal(${score.user_id})" class="action-button">
                                            <i class="fas fa-eye mr-2"></i> View Details
                                        </button>
                                    </td>
                                `;
                                return row;
                            });

                            // Update or add rows
                            newRows.forEach((newRow, index) => {
                                const existingRow = existingRows.find(row => row.dataset.userId === newRow.dataset.userId);
                                if (existingRow) {
                                    const existingData = {
                                        total_point: existingRow.children[3].textContent,
                                        average_time: existingRow.children[4].textContent,
                                        solved_challenges: existingRow.children[5].textContent
                                    };
                                    const newData = {
                                        total_point: newRow.children[3].textContent,
                                        average_time: newRow.children[4].textContent,
                                        solved_challenges: newRow.children[5].textContent
                                    };
                                    if (JSON.stringify(existingData) !== JSON.stringify(newData)) {
                                        existingRow.innerHTML = newRow.innerHTML;
                                        existingRow.classList.add('updated');
                                        setTimeout(() => existingRow.classList.remove('updated'), 500);
                                    }
                                } else {
                                    leaderboardBody.appendChild(newRow);
                                    newRow.classList.add('updated');
                                    setTimeout(() => newRow.classList.remove('updated'), 500);
                                }
                            });

                            // Remove rows that no longer exist
                            existingRows.forEach(existingRow => {
                                if (!newRows.some(newRow => newRow.dataset.userId === existingRow.dataset.userId)) {
                                    existingRow.remove();
                                }
                            });

                            // Update rows for search
                            rows = leaderboardBody.querySelectorAll('.table-row:not(.no-scores)');
                            updateSearch();
                        } else {
                            leaderboardBody.innerHTML = '<tr class="table-row no-scores"><td colspan="7" class="p-5 text-center text-gray-300">No scores found.</td></tr>';
                            rows = [];
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching leaderboard:', error);
                    });
            }

            // Poll every 10 seconds
            setInterval(fetchLeaderboard, 10000);
            fetchLeaderboard(); // Initial fetch

            // Expose functions to global scope
            window.openDetailsModal = openDetailsModal;
            window.closeDetailsModal = closeDetailsModal;

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
            document.querySelectorAll('.card, .action-button, .pagination-button').forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(4px)';
                });
                el.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
        });
    </script>

    <?php
    // Handle AJAX request for scores
    if (isset($_GET['get_scores'])) {
        try {
            $ajax_page = isset($_GET['page']) ? filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]) : 1;
            $ajax_per_page = isset($_GET['per_page']) ? filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 5]]) : 10;
            $ajax_offset = ($ajax_page - 1) * $ajax_per_page;

            $query = "SELECT us.user_id, ud.user_name, us.total_point, 
                     us.c1, us.c2, us.c3, us.c4, us.c5, us.c6, us.c7, us.c8, us.c9, us.c10,
                     us.c11, us.c12, us.c13, us.c14, us.c15, us.c16, us.c17, us.c18, us.c19, us.c20
              FROM tbl_user_score us 
              LEFT JOIN tbl_user_detail ud ON us.user_id = ud.user_id
              ORDER BY us.total_point DESC, (
                  SELECT AVG(time_in_seconds)
                  FROM (
                      SELECT parseTimeToSeconds(c1) AS time_in_seconds WHERE c1 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c2) WHERE c2 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c3) WHERE c3 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c4) WHERE c4 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c5) WHERE c5 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c6) WHERE c6 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c7) WHERE c7 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c8) WHERE c8 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c9) WHERE c9 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c10) WHERE c10 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c11) WHERE c11 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c12) WHERE c12 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c13) WHERE c13 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c14) WHERE c14 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c15) WHERE c15 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c16) WHERE c16 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c17) WHERE c17 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c18) WHERE c18 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c19) WHERE c19 IS NOT NULL
                      UNION SELECT parseTimeToSeconds(c20) WHERE c20 IS NOT NULL
                  ) times
              ) ASC
              LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':limit', $ajax_per_page, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $ajax_offset, PDO::PARAM_INT);
            $stmt->execute();
            $raw_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $scores = [];
            foreach ($raw_scores as $score) {
                $times = [];
                $solved_challenges = 0;
                for ($i = 1; $i <= 20; $i++) {
                    $key = "c$i";
                    if ($score[$key] && preg_match('/^\d{2}:\d{2}$/', $score[$key])) {
                        $seconds = parseTimeToSeconds($score[$key]);
                        if ($seconds !== null) {
                            $times[] = $seconds;
                            $solved_challenges++;
                        }
                    }
                }
                $score['average_time'] = !empty($times) ? formatSecondsToTime(array_sum($times) / count($times)) : 'N/A';
                $score['solved_challenges'] = $solved_challenges;
                $scores[] = $score;
            }

            header('Content-Type: application/json');
            echo json_encode(['scores' => $scores]);
            exit;
        } catch (PDOException $e) {
            error_log("Error fetching scores for AJAX: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['scores' => []]);
            exit;
        }
    }

    // Handle AJAX request for score details
    if (isset($_GET['get_details'])) {
        $user_id = filter_input(INPUT_GET, 'get_details', FILTER_VALIDATE_INT);
        if ($user_id) {
            try {
                $stmt = $pdo->prepare("SELECT us.user_id, ud.user_name, ud.created_at AS user_created_at, us.created_at AS score_created_at, us.total_point, 
                                       us.c1, us.c2, us.c3, us.c4, us.c5, us.c6, us.c7, us.c8, us.c9, us.c10,
                                       us.c11, us.c12, us.c13, us.c14, us.c15, us.c16, us.c17, us.c18, us.c19, us.c20
                                       FROM tbl_user_score us 
                                       LEFT JOIN tbl_user_detail ud ON us.user_id = ud.user_id 
                                       WHERE us.user_id = :user_id");
                $stmt->execute(['user_id' => $user_id]);
                $score = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($score) {
                    $times = [];
                    $solved_challenges = 0;
                    for ($i = 1; $i <= 20; $i++) {
                        $key = "c$i";
                        if ($score[$key] && preg_match('/^\d{2}:\d{2}$/', $score[$key])) {
                            $seconds = parseTimeToSeconds($score[$key]);
                            if ($seconds !== null) {
                                $times[] = $seconds;
                                $solved_challenges++;
                            }
                        }
                    }
                    $score['average_time'] = !empty($times) ? formatSecondsToTime(array_sum($times) / count($times)) : 'N/A';
                    $score['solved_challenges'] = $solved_challenges;
                }
                header('Content-Type: application/json');
                echo json_encode($score ?: []);
                exit;
            } catch (PDOException $e) {
                error_log("Error fetching score details: " . $e->getMessage());
                header('Content-Type: application/json');
                echo json_encode([]);
                exit;
            }
        }
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
    }
    ?>
</body>
</html>