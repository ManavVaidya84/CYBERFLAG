<?php
session_start();
require_once '../config/configdb.php';

// Set current page for sidebar
$current_page = 'manage_users.php';

// Restrict access to logged-in admins
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
    $_SESSION['feedback'] = "Please log in to access this page.";
    header('Location: login.php');
    exit();
}

// Initialize feedback
$feedback = isset($_SESSION['feedback']) ? $_SESSION['feedback'] : '';
unset($_SESSION['feedback']);

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $user_name = trim(filter_input(INPUT_POST, 'user_name', FILTER_SANITIZE_STRING));

    if (!$user_id || !$user_name) {
        $feedback = "Error: User ID and name are required.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE tbl_user_detail SET user_name = :user_name WHERE user_id = :user_id");
            $stmt->execute(['user_name' => $user_name, 'user_id' => $user_id]);
            $feedback = "User updated successfully.";
        } catch (PDOException $e) {
            error_log("Edit user failed: " . $e->getMessage());
            $feedback = "Error: Could not update user.";
        }
    }
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if (!$user_id) {
        $feedback = "Error: Invalid user ID.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM tbl_user_detail WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user_id]);
            $feedback = "User deleted successfully.";
        } catch (PDOException $e) {
            error_log("Delete user failed: " . $e->getMessage());
            $feedback = "Error: Could not delete user. It may be referenced elsewhere.";
        }
    }
}

// Pagination settings
$per_page = isset($_GET['per_page']) ? filter_input(INPUT_POST, 'per_page', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 5]]) : 10;
$per_page = in_array($per_page, [5, 10, 20, 50]) ? $per_page : 10;
$page = isset($_GET['page']) ? filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]) : 1;
$offset = ($page - 1) * $per_page;

// Fetch total user count
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM tbl_user_detail");
    $total_users = $stmt->fetchColumn();
    $total_pages = ceil($total_users / $per_page);
} catch (PDOException $e) {
    error_log("Fetch total users failed: " . $e->getMessage());
    $total_users = 0;
    $total_pages = 1;
}

// Fetch users for the current page
try {
    $stmt = $pdo->prepare("SELECT user_id, user_name, created_at FROM tbl_user_detail ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch users failed: " . $e->getMessage());
    $feedback = "Error: Could not fetch users.";
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HACKERSTORM :: User Management</title>
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
            min-h-screen;
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

        .action-button.delete {
            background: linear-gradient(90deg, #ef4444 0%, #b91c1c 100%);
            border-color: rgba(239, 68, 68, 0.3);
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

        .action-button.delete:hover {
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
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
            padding: 0.75rem 1rem;
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
                <h1 class="text-2xl font-bold text-white">User Management</h1>
                <div class="metric-badge">
                    <i class="fas fa-users"></i>
                    <?php echo number_format($total_users); ?> Users
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
            <div class="mb-6">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchInput" class="search-input pl-10" placeholder="Search by username...">
                </div>
            </div>

            <!-- Users Table -->
            <div class="card p-8">
                <table class="w-full text-left">
                    <thead class="table-header">
                        <tr>
                            <th class="p-5 text-sm font-semibold">Sr.No</th>
                            <th class="p-5 text-sm font-semibold">User ID</th>
                            <th class="p-5 text-sm font-semibold">Name</th>
                            <th class="p-5 text-sm font-semibold">Created At</th>
                            <th class="p-5 text-sm font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php if (empty($users)): ?>
                        <tr class="table-row">
                            <td colspan="5" class="p-5 text-center text-gray-300">No users found.</td>
                        </tr>
                        <?php else: ?>
                        <?php $sr_no = $offset + 1; ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="table-row border-b border-gray-600/50" data-username="<?php echo htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <td class="p-5 serial-number"><?php echo $sr_no++; ?></td>
                            <td class="p-5 font-mono"><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td class="p-5"><?php echo htmlspecialchars($user['user_name']); ?></td>
                            <td class="p-5 font-mono"><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td class="p-5 flex space-x-3">
                                <button onclick="openEditModal(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?>')" class="action-button">
                                    <i class="fas fa-edit mr-2"></i> Edit
                                </button>
                                <form action="manage_users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="delete_user" value="1">
                                    <button type="submit" class="action-button delete">
                                        <i class="fas fa-trash mr-2"></i> Delete
                                    </button>
                                </form>
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
                        <select class="pagination-select font-mono" onchange="window.location.href='manage_users.php?page=1&per_page='+this.value">
                            <option value="5" <?php echo $per_page == 5 ? 'selected' : ''; ?>>5</option>
                            <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                        </select>
                        <span class="text-gray-300 text-sm font-medium">entries</span>
                    </div>
                    <div class="flex space-x-2">
                        <a href="manage_users.php?page=<?php echo max(1, $page - 1); ?>&per_page=<?php echo $per_page; ?>" class="pagination-button <?php echo $page <= 1 ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                            <i class="fas fa-chevron-left mr-2"></i> Previous
                        </a>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="manage_users.php?page=<?php echo $i; ?>&per_page=<?php echo $per_page; ?>" class="pagination-button <?php echo $page == $i ? 'bg-primary-600' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        <a href="manage_users.php?page=<?php echo min($total_pages, $page + 1); ?>&per_page=<?php echo $per_page; ?>" class="pagination-button <?php echo $page >= $total_pages ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo $page >= $total_pages ? 'disabled' : ''; ?>>
                            Next <i class="fas fa-chevron-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="modal hidden fixed inset-0 flex items-center justify-center z-50">
            <div class="modal-content card p-8 w-full max-w-lg">
                <h2 class="text-xl font-bold text-white mb-6">Edit User</h2>
                <form action="manage_users.php" method="POST">
                    <input type="hidden" name="user_id" id="editUserId">
                    <input type="hidden" name="edit_user" value="1">
                    <div class="mb-5 flex items-center gap-3">
                        <i class="fas fa-id-badge text-primary-400"></i>
                        <div class="flex-1">
                            <label class="block text-gray-300 text-sm font-semibold mb-2">User ID</label>
                            <input type="text" id="editUserIdDisplay" class="modal-input w-full font-mono" readonly>
                        </div>
                    </div>
                    <div class="mb-5 flex items-center gap-3">
                        <i class="fas fa-user text-primary-400"></i>
                        <div class="flex-1">
                            <label class="block text-gray-300 text-sm font-semibold mb-2">Name</label>
                            <input type="text" name="user_name" id="editUserName" class="modal-input w-full" required>
                        </div>
                    </div>
                    <div class="mb-5 flex items-center gap-3">
                        <i class="fas fa-clock text-primary-400"></i>
                        <div class="flex-1">
                            <label class="block text-gray-300 text-sm font-semibold mb-2">Created At</label>
                            <input type="text" id="editCreatedAt" class="modal-input w-full font-mono" readonly>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeEditModal()" class="action-button bg-gray-600">Cancel</button>
                        <button type="submit" class="action-button">
                            <i class="fas fa-save mr-2"></i> Save
                        </button>
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

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            const userTableBody = document.getElementById('userTableBody');
            const rows = userTableBody.querySelectorAll('.table-row:not(.no-users)');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();
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

                // Update serial numbers
                const visibleRows = userTableBody.querySelectorAll('.table-row:not([style*="display: none"])');
                visibleRows.forEach((row, index) => {
                    const serialCell = row.querySelector('.serial-number');
                    if (serialCell) {
                        serialCell.textContent = index + 1;
                    }
                });

                // Show "No users found" if no matches
                const noUsersRow = userTableBody.querySelector('.no-users');
                if (visibleRowCount === 0 && !noUsersRow) {
                    const noUsersRow = document.createElement('tr');
                    noUsersRow.className = 'table-row no-users';
                    noUsersRow.innerHTML = '<td colspan="5" class="p-5 text-center text-gray-300">No users found.</td>';
                    userTableBody.appendChild(noUsersRow);
                } else if (visibleRowCount > 0 && noUsersRow) {
                    noUsersRow.remove();
                }
            });

            // Modal open/close
            function openEditModal(userId, userName, createdAt) {
                document.getElementById('editUserId').value = userId;
                document.getElementById('editUserIdDisplay').value = userId;
                document.getElementById('editUserName').value = userName;
                document.getElementById('editCreatedAt').value = createdAt;
                const modal = document.getElementById('editModal');
                const modalContent = modal.querySelector('.modal-content');
                modal.classList.remove('hidden');
                setTimeout(() => modalContent.classList.add('show'), 10);
            }

            function closeEditModal() {
                const modal = document.getElementById('editModal');
                const modalContent = modal.querySelector('.modal-content');
                modalContent.classList.remove('show');
                setTimeout(() => modal.classList.add('hidden'), 400);
            }

            // Expose functions to global scope for inline onclick
            window.openEditModal = openEditModal;
            window.closeEditModal = closeEditModal;

            // Close modal after successful edit
            <?php if ($feedback && strpos($feedback, 'successfully') !== false): ?>
            closeEditModal();
            <?php endif; ?>

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
</body>
</html>