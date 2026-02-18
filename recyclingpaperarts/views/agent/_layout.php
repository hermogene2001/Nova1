<?php
// Agent Layout Template - Provides consistent design across all agent pages
$agent_page_title = $agent_page_title ?? 'Agent Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($agent_page_title); ?> | Recycling Paper Arts</title>
    <link rel="stylesheet" href="../../assets/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Primary Brand Colors */
            --brand-primary: #2563eb;
            --brand-secondary: #7c3aed;
            --brand-accent: #10b981;
            
            /* Grayscale */
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            
            /* Status Colors */
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            
            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            
            /* Border Radius */
            --radius-sm: 0.25rem;
            --radius-md: 0.375rem;
            --radius-lg: 0.5rem;
            --radius-xl: 0.75rem;
            --radius-full: 9999px;
        }

        /* Base Styles */
        body {
            background-color: var(--gray-50);
            color: var(--gray-800);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header/Navigation */
        .agent-navbar {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            box-shadow: var(--shadow-md);
            border-bottom: 4px solid var(--brand-accent);
            padding: var(--spacing-md) 0;
        }

        .agent-navbar .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.025em;
        }

        .agent-navbar .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-lg);
            transition: all 0.2s ease;
            margin: 0 var(--spacing-xs);
        }

        .agent-navbar .nav-link:hover,
        .agent-navbar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white !important;
            transform: translateY(-1px);
        }

        /* Main Content */
        .agent-main {
            padding: var(--spacing-xl) 0;
            min-height: calc(100vh - 120px);
        }

        .agent-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--spacing-md);
        }

        /* Cards */
        .agent-card {
            background: white;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: var(--spacing-lg);
        }

        .agent-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .agent-card-header {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            color: white;
            padding: var(--spacing-lg) var(--spacing-xl);
            font-weight: 600;
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .agent-card-body {
            padding: var(--spacing-xl);
        }

        /* Tables */
        .agent-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .agent-table th {
            background-color: var(--gray-50);
            color: var(--gray-700);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: var(--spacing-md) var(--spacing-lg);
            text-align: left;
            border-bottom: 2px solid var(--gray-200);
        }

        .agent-table td {
            padding: var(--spacing-lg) var(--spacing-lg);
            border-bottom: 1px solid var(--gray-200);
        }

        .agent-table tr:last-child td {
            border-bottom: none;
        }

        .agent-table tr:hover {
            background-color: var(--gray-50);
        }

        /* Buttons */
        .btn-agent {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-xs);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-lg);
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .btn-agent-primary {
            background: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
            color: white;
        }

        .btn-agent-success {
            background-color: var(--success);
            color: white;
        }

        .btn-agent-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-agent-outline {
            background-color: transparent;
            border: 1px solid var(--gray-300);
            color: var(--gray-700);
        }

        .btn-agent:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-agent:active {
            transform: translateY(0);
        }

        /* Forms */
        .agent-form-group {
            margin-bottom: var(--spacing-lg);
        }

        .agent-form-label {
            display: block;
            margin-bottom: var(--spacing-sm);
            font-weight: 500;
            color: var(--gray-700);
        }

        .agent-form-control {
            width: 100%;
            padding: var(--spacing-md);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .agent-form-control:focus {
            outline: none;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Alerts */
        .agent-alert {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .agent-alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .agent-alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .agent-alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        /* Empty States */
        .agent-empty-state {
            text-align: center;
            padding: var(--spacing-2xl);
            color: var(--gray-500);
        }

        .agent-empty-state i {
            font-size: 3rem;
            margin-bottom: var(--spacing-md);
            color: var(--gray-300);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .agent-container {
                padding: 0 var(--spacing-sm);
            }
            
            .agent-card-header {
                padding: var(--spacing-md);
                font-size: 1rem;
            }
            
            .agent-card-body {
                padding: var(--spacing-lg);
            }
            
            .agent-table {
                font-size: 0.875rem;
            }
            
            .agent-table th,
            .agent-table td {
                padding: var(--spacing-sm);
            }
            
            .agent-navbar .navbar-brand {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 576px) {
            .agent-main {
                padding: var(--spacing-md) 0;
            }
            
            .agent-card-body {
                padding: var(--spacing-md);
            }
            
            .btn-agent {
                width: 100%;
                justify-content: center;
                margin-bottom: var(--spacing-xs);
            }
        }

        /* Loading States */
        .agent-loading {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-0 { margin-bottom: 0; }
        .mt-0 { margin-top: 0; }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
        .gap-sm { gap: var(--spacing-sm); }
        .gap-md { gap: var(--spacing-md); }
        .w-100 { width: 100%; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="agent-navbar navbar navbar-expand-lg">
        <div class="container-fluid agent-container">
            <a class="navbar-brand" href="../agent_dashboard.php">
                <i class="fas fa-user-shield me-2"></i>
                Agent Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#agentNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="agentNavbar">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="referrals.php">
                            <i class="fas fa-users me-1"></i> Referrals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-1"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">
                            <i class="fas fa-key me-1"></i> Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../actions/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="agent-main">
        <div class="agent-container">
            <?php if (isset($page_content)) echo $page_content; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>