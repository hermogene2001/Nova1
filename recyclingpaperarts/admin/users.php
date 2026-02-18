<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../admin_login.php');
    exit();
}

require_once('../includes/db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <!-- Add Bootstrap and jQuery CDNs -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .main-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            margin: -15px -15px 0 -15px;
        }
        .search-container {
            position: relative;
            max-width: 400px;
        }
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 5;
        }
        #searchInput {
            padding-left: 50px;
            padding-right: 45px;
            border-radius: 25px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            height: 45px;
        }
        #searchInput:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        .clear-search {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            z-index: 5;
            transition: color 0.3s ease;
        }
        .clear-search:hover {
            color: #dc3545;
        }
        .table-container {
            margin: 0 -15px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: none;
            color: #495057;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-group .btn {
            margin: 0 1px;
        }
        .badge {
            font-size: 0.75em;
            padding: 0.375rem 0.75rem;
        }
        .pagination-container {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            padding: 1rem 1.5rem;
            margin: 0 -15px -15px -15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .pagination-info {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
            margin: 0;
        }
        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .page-size-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            color: #6c757d;
        }
        .page-size-selector select {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 0.25rem 0.5rem;
            font-size: 14px;
        }
        .pagination {
            margin: 0;
        }
        .page-link {
            color: #667eea;
            border: 1px solid #dee2e6;
            padding: 0.5rem 0.75rem;
            transition: all 0.3s ease;
        }
        .page-link:hover {
            color: #495057;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        .page-item.active .page-link {
            background-color: #667eea;
            border-color: #667eea;
            color: white;
        }
        .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
        }
        .quick-jump {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 14px;
            color: #6c757d;
        }
        .quick-jump input {
            width: 60px;
            padding: 0.25rem 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 8px;
        }
        .stats-cards {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            flex: 1;
            border-left: 4px solid;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card.clients {
            border-left-color: #28a745;
        }
        .stat-card.agents {
            border-left-color: #007bff;
        }
        .stat-card.total {
            border-left-color: #6f42c1;
        }
        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            margin: 0;
        }
        @media (max-width: 768px) {
            .pagination-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .pagination-controls {
                flex-direction: column;
                width: 100%;
                gap: 1rem;
            }
            .stats-cards {
                flex-direction: column;
            }
            .header-section {
                padding: 1rem;
            }
            .header-section h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-newspaper me-2"></i>Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php"><i class="fas fa-users me-1"></i>Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="phone_management.php"><i class="fas fa-phone me-1"></i>Phone Management</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support_phone.php"><i class="fas fa-headset me-1"></i>Support Phone</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php"><i class="fas fa-box me-1"></i>Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php"><i class="fas fa-exchange-alt me-1"></i>Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pending_withdrawals.php"><i class="fas fa-money-bill-wave me-1"></i>Withdrawals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pending_recharges.php"><i class="fas fa-credit-card me-1"></i>Recharges</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="update_social_links.php"><i class="fas fa-hashtag me-1"></i>SocialMedia</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-light" href="../actions/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="main-container">
            <div class="header-section">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="mb-0">
                            <i class="fas fa-users me-2"></i>
                            User Management
                        </h2>
                        <p class="mb-0 mt-1 opacity-75">Manage all system users</p>
                    </div>
                    <div class="col-md-6 mt-3 mt-md-0">
                        <div class="search-container ms-auto">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search by name, phone number, or referral code...">
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="searchByPhone">
                                    <i class="fas fa-phone"></i> Phone Search
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" id="exportPhones">
                                    <i class="fas fa-download"></i> Export Phones
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" id="bulkVerifyPhones">
                                    <i class="fas fa-check-circle"></i> Verify Selected
                                </button>
                            </div>
                            <button type="button" class="clear-search" id="clearSearch" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <!-- Statistics Cards -->
                <div class="stats-cards" id="statsCards">
                    <div class="stat-card clients">
                        <div class="stat-number" id="clientCount">0</div>
                        <div class="stat-label">Clients</div>
                    </div>
                    <div class="stat-card agents">
                        <div class="stat-number" id="agentCount">0</div>
                        <div class="stat-label">Agents</div>
                    </div>
                    <div class="stat-card total">
                        <div class="stat-number" id="totalCount">0</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>

                <div class="table-container position-relative">
                    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Loading users...</div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" title="Select all users">
                                    </th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Referral Code</th>
                                    <th>Balance</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTable">
                                <!-- Dynamic content will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination Container -->
            <div class="pagination-container">
                <div class="pagination-info" id="paginationInfo">
                    Loading...
                </div>
                <div class="pagination-controls">
                    <div class="page-size-selector">
                        <label for="pageSize">Show:</label>
                        <select id="pageSize" class="form-select">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span>per page</span>
                    </div>
                    <nav aria-label="User pagination">
                        <ul class="pagination pagination-sm" id="pagination">
                            <!-- Dynamic pagination will be inserted here -->
                        </ul>
                    </nav>
                    <div class="quick-jump">
                        <span>Go to page:</span>
                        <input type="number" id="jumpToPage" min="1" max="1" class="form-control">
                        <button type="button" id="jumpButton" class="btn btn-sm btn-outline-secondary">Go</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">
                            <i class="fas fa-user-edit"></i> Edit User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../actions/edit_user.php" method="POST">
                            <input type="hidden" name="id" value="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="editPhoneNumber" class="form-label">
                                        <i class="fas fa-phone"></i> Phone Number
                                    </label>
                                    <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="editRole" class="form-label">
                                        <i class="fas fa-user-tag"></i> Role
                                    </label>
                                    <select class="form-select" id="editRole" name="role" required>
                                        <option value="client">Client</option>
                                        <option value="agent">Agent</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="editBalance" class="form-label">
                                        <i class="fas fa-wallet"></i> Balance
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" id="editBalance" name="balance" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="editStatus" class="form-label">
                                        <i class="fas fa-toggle-on"></i> Status
                                    </label>
                                    <select class="form-select" id="editStatus" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                    <div class="form-text">
                                        <small>
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Active:</strong> User can use all features |
                                            <strong>Inactive:</strong> User cannot login |
                                            <strong>Suspended:</strong> User has limited access
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        let currentPage = 1;
        let currentSearch = '';
        let pageSize = 10;
        let totalPages = 1;

        // Load users and stats initially
        loadUsers('', 1, pageSize);
        loadStats();

        // Search functionality
        $('#searchInput').on('keyup', debounce(function() {
            const query = $(this).val();
            currentSearch = query;
            currentPage = 1;
            loadUsers(query, 1, pageSize);
            
            // Show/hide clear button
            if (query.length > 0) {
                $('#clearSearch').show();
            } else {
                $('#clearSearch').hide();
            }
        }, 300));

        // Clear search
        $('#clearSearch').on('click', function() {
            $('#searchInput').val('');
            $('#clearSearch').hide();
            currentSearch = '';
            currentPage = 1;
            loadUsers('', 1, pageSize);
            $('#searchInput').focus();
        });

        // Page size change
        $('#pageSize').on('change', function() {
            pageSize = parseInt($(this).val());
            currentPage = 1;
            loadUsers(currentSearch, 1, pageSize);
        });

        // Pagination click handler
        $(document).on('click', '.pagination .page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page && page !== currentPage && page >= 1 && page <= totalPages) {
                currentPage = page;
                loadUsers(currentSearch, page, pageSize);
            }
        });

        // Quick jump to page
        $('#jumpButton').on('click', function() {
            const page = parseInt($('#jumpToPage').val());
            if (page && page >= 1 && page <= totalPages && page !== currentPage) {
                currentPage = page;
                loadUsers(currentSearch, page, pageSize);
            }
        });

        $('#jumpToPage').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                $('#jumpButton').click();
            }
        });

        function debounce(func, delay) {
            let timeoutId;
            return function (...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        }

        function showLoading() {
            $('#loadingOverlay').show();
        }

        function hideLoading() {
            $('#loadingOverlay').hide();
        }

        function loadUsers(query, page, size) {
            showLoading();
            
            $.ajax({
                url: '../actions/search_users.php',
                method: 'GET',
                data: { 
                    search: query,
                    page: page,
                    limit: size
                },
                success: function(data) {
                    $('#userTable').html(data);
                    
                    // Extract pagination info
                    const paginationInfoDiv = $('#pagination-info');
                    if (paginationInfoDiv.length > 0) {
                        const paginationData = JSON.parse(paginationInfoDiv.data('info'));
                        updatePaginationUI(paginationData);
                        paginationInfoDiv.remove();
                    }
                    
                    hideLoading();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading users:', error);
                    $('#userTable').html(`
                        <tr>
                            <td colspan="8" class="text-center text-danger p-4">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <br>Error loading users. Please try again.
                            </td>
                        </tr>
                    `);
                    hideLoading();
                }
            });
        }

        function loadStats() {
            $.ajax({
                url: '../actions/get_user_stats.php',
                method: 'GET',
                success: function(data) {
                    const stats = JSON.parse(data);
                    $('#clientCount').text(stats.clients || 0);
                    $('#agentCount').text(stats.agents || 0);
                    $('#totalCount').text(stats.total || 0);
                },
                error: function() {
                    console.error('Error loading stats');
                }
            });
        }

        function updatePaginationUI(paginationData) {
            const { 
                current_page, 
                total_pages, 
                total_users, 
                showing_from, 
                showing_to,
                has_previous,
                has_next,
                previous_page,
                next_page
            } = paginationData;
            
            totalPages = total_pages;
            
            // Update pagination info
            let infoText = total_users > 0 
                ? `Showing ${showing_from} to ${showing_to} of ${total_users} users`
                : 'No users found';
            $('#paginationInfo').text(infoText);
            
            // Update jump input max value
            $('#jumpToPage').attr('max', total_pages).val('');
            
            // Update pagination controls
            let paginationHTML = '';
            
            if (total_pages > 1) {
                // Previous button
                paginationHTML += `<li class="page-item ${!has_previous ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${previous_page}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>`;
                
                // Page numbers with smart truncation
                const startPage = Math.max(1, current_page - 2);
                const endPage = Math.min(total_pages, current_page + 2);
                
                // First page if not in range
                if (startPage > 1) {
                    paginationHTML += `<li class="page-item">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>`;
                    if (startPage > 2) {
                        paginationHTML += `<li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>`;
                    }
                }
                
                // Page range
                for (let i = startPage; i <= endPage; i++) {
                    paginationHTML += `<li class="page-item ${i === current_page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
                }
                
                // Last page if not in range
                if (endPage < total_pages) {
                    if (endPage < total_pages - 1) {
                        paginationHTML += `<li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>`;
                    }
                    paginationHTML += `<li class="page-item">
                        <a class="page-link" href="#" data-page="${total_pages}">${total_pages}</a>
                    </li>`;
                }
                
                // Next button
                paginationHTML += `<li class="page-item ${!has_next ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${next_page}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>`;
            }
            
            $('#pagination').html(paginationHTML);
        }

        // Edit user modal
        $(document).on('click', '.edit-btn', function() {
            const id = $(this).data('id');
            const phone = $(this).data('phone');
            const role = $(this).data('role');
            const balance = $(this).data('balance');
            const status = $(this).data('status') || 'active';
            
            $('#editUserModal input[name="id"]').val(id);
            $('#editUserModal input[name="phone_number"]').val(phone);
            $('#editUserModal select[name="role"]').val(role);
            $('#editUserModal input[name="balance"]').val(balance);
            $('#editUserModal select[name="status"]').val(status);
            
            $('#editUserModal').modal('show');
        });

        // Confirmation dialogs
        $(document).on('click', '.btn-danger', function(e) {
            if (!confirm('⚠️ Are you sure you want to delete this user?\n\nThis action cannot be undone and will permanently remove all user data.')) {
                e.preventDefault();
            }
        });

        $(document).on('click', '.btn-warning', function(e) {
            if (!confirm('🔑 Reset this user\'s password to "Password123!"?\n\nThe user will need to log in with the new password.')) {
                e.preventDefault();
            }
        });

        // Phone number specific search
        $('#searchByPhone').on('click', function() {
            const phoneQuery = prompt('Enter phone number to search:');
            if (phoneQuery) {
                $('#searchInput').val(phoneQuery);
                currentSearch = phoneQuery;
                currentPage = 1;
                loadUsers(phoneQuery, 1, pageSize);
            }
        });

        // Export phone numbers
        $('#exportPhones').on('click', function() {
            if (confirm('Export all phone numbers to CSV file?')) {
                window.location.href = '../actions/export_phones.php';
            }
        });

        // Bulk verify phone numbers
        $('#bulkVerifyPhones').on('click', function() {
            const selectedIds = [];
            $('input[name="selected_users[]"]:checked').each(function() {
                selectedIds.push($(this).val());
            });
            
            if (selectedIds.length === 0) {
                alert('Please select users first');
                return;
            }
            
            if (confirm(`Verify phone numbers for ${selectedIds.length} selected users?`)) {
                $.post('../actions/bulk_verify_phones.php', {
                    user_ids: selectedIds
                }, function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert(`Successfully verified ${result.verified} phone numbers`);
                        loadUsers(currentSearch, currentPage, pageSize);
                    } else {
                        alert('Error verifying phone numbers: ' + result.message);
                    }
                });
            }
        });

        // Select all users checkbox
        $(document).on('change', '#selectAll', function() {
            $('input[name="selected_users[]"]').prop('checked', this.checked);
        });

        // Auto-refresh stats every 30 seconds
        setInterval(loadStats, 30000);
    });
    </script>
</body>
</html>