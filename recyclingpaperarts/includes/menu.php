<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Paper Arts Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Recycling Paper Arts Color Scheme */
        :root {
            --magazine-red: #c8102e;
            --magazine-blue: #003d7a;
            --magazine-light-blue: #e8f4fd;
            --magazine-gray: #f5f5f5;
            --magazine-dark-gray: #424242;
            --magazine-gold: #FFD700;
        }

        /* Magazine Navigation Styling */
        .magazine-navbar {
            background: linear-gradient(135deg, var(--magazine-blue), var(--magazine-red)) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 0.8rem 0;
            position: relative;
            overflow: visible;
            z-index: 1000;
        }

        .magazine-navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="waves" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M0,10 Q5,0 10,10 T20,10" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" fill="none"/></pattern></defs><rect width="100" height="100" fill="url(%23waves)"/></svg>') repeat;
            opacity: 0.3;
            z-index: 1;
        }

        .magazine-navbar .navbar-content {
            position: relative;
            z-index: 1001;
        }

        .magazine-navbar .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }

        .magazine-navbar .navbar-brand:hover {
            transform: scale(1.05);
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
        }

        .magazine-navbar .navbar-brand i {
            color: var(--magazine-gold);
            margin-right: 0.5rem;
            animation: gentle-float 3s ease-in-out infinite;
        }

        @keyframes gentle-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        .magazine-navbar .nav-link {
            color: rgba(255,255,255,0.9) !important;
            transition: all 0.3s ease;
            margin: 0 0.3rem;
            border-radius: 8px;
            padding: 0.7rem 1.2rem !important;
            position: relative;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .magazine-navbar .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            transform: scaleX(0);
            transition: transform 0.3s ease;
            z-index: -1;
        }

        .magazine-navbar .nav-link:hover::before {
            transform: scaleX(1);
        }

        .magazine-navbar .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .magazine-navbar .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        /* Dropdown specific styles */
        .magazine-navbar .dropdown {
            position: static;
        }

        .magazine-navbar .dropdown-toggle::after {
            margin-left: 0.5rem;
            transition: transform 0.3s ease;
        }

        .magazine-navbar .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        .magazine-navbar .dropdown-menu {
            background: linear-gradient(135deg, var(--magazine-blue), var(--magazine-red));
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            margin-top: 0.5rem;
            min-width: 200px;
            animation: dropdown-fade-in 0.3s ease;
            z-index: 9999;
            position: absolute;
        }

        @keyframes dropdown-fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .magazine-navbar .dropdown-item {
            color: rgba(255,255,255,0.9) !important;
            transition: all 0.3s ease;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-radius: 8px;
            margin: 0.2rem 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .magazine-navbar .dropdown-item::before {
            content: '';
            position: absolute;
            top: 0;
            /* left: -100%; */
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .magazine-navbar .dropdown-item:hover::before {
            left: 100%;
        }

        .magazine-navbar .dropdown-item:hover {
            background: rgba(255,255,255,0.15) !important;
            color: white !important;
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .magazine-navbar .dropdown-item i {
            margin-right: 0.7rem;
            font-size: 1rem;
            color: var(--magazine-gold);
        }

        .magazine-navbar .btn-danger {
            background: linear-gradient(135deg, var(--magazine-red), #c41e24) !important;
            border: 2px solid rgba(255,255,255,0.3) !important;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .magazine-navbar .btn-danger::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .magazine-navbar .btn-danger:hover::before {
            left: 100%;
        }

        .magazine-navbar .btn-danger:hover {
            background: linear-gradient(135deg, #c41e24, var(--magazine-red)) !important;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(196, 30, 36, 0.4);
            border-color: rgba(255,255,255,0.6) !important;
        }

        .magazine-navbar .navbar-toggler {
            border: 2px solid rgba(255,255,255,0.3);
            padding: 0.5rem 0.8rem;
            transition: all 0.3s ease;
        }

        .magazine-navbar .navbar-toggler:hover {
            border-color: rgba(255,255,255,0.6);
            transform: scale(1.1);
        }

        .magazine-navbar .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Active nav item */
        .magazine-navbar .nav-link.active {
            background: rgba(255,255,255,0.15) !important;
            color: white !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .magazine-navbar .navbar-collapse {
                background: rgba(0,0,0,0.3);
                margin-top: 1rem;
                padding: 1rem;
                border-radius: 10px;
                backdrop-filter: blur(10px);
            }
            
            .magazine-navbar .nav-link {
                margin: 0.2rem 0;
            }

            .magazine-navbar .dropdown-menu {
                background: rgba(0,0,0,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                margin-top: 0.5rem;
                margin-left: 1rem;
            }
        }

        /* Demo body styling */
        body {
            background: linear-gradient(135deg, var(--magazine-gray), #e9ecef);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }

        .demo-content {
            padding: 3rem 0;
            text-align: center;
        }

        .demo-content h1 {
            color: var(--magazine-blue);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .demo-content p {
            color: var(--magazine-dark-gray);
            font-size: 1.1rem;
        }

        .feature-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--magazine-red), var(--magazine-blue));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            margin: 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Magazine Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark magazine-navbar">
        <div class="container-fluid navbar-content">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-newspaper"></i>Recycling Paper Arts 
            </a>
            <!-- Magazine Admin -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">
                            <i class="fas fa-money-bill-wave"></i>Transactions
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-box"></i>Products
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                            <li><a class="dropdown-item" href="products.php">
                                <i class="fas fa-list"></i>VIP Products
                            </a></li>
                            <li><a class="dropdown-item" href="manage-products.php">
                                <i class="fas fa-plus"></i>LongTerm Product
                            </a></li>
                            <li><a class="dropdown-item" href="add-product.php">
                                <i class="fas fa-plus-circle"></i>Add New Product
                            </a></li>
                            <li><a class="dropdown-item" href="add-product-compound.php">
                                <i class="fas fa-layer-group"></i>Compound Products
                            </a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users-cog"></i>Users
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="usersDropdown">
                            <li><a class="dropdown-item" href="users.php">
                                <i class="fas fa-users"></i>All Users
                            </a></li>
                            <li><a class="dropdown-item" href="pending_recharges.php">
                                <i class="fas fa-wallet"></i>Pending Recharges
                            </a></li>
                            <li><a class="dropdown-item" href="pending_withdrawals.php">
                                <i class="fas fa-money-bill-wave"></i>Pending Withdrawals
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="update_social_links.php">
                            <i class="fas fa-hashtag"></i>SocialMedia
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="support_phone.php">
                            <i class="fas fa-headset"></i>Support Phone
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="phone_management.php">
                            <i class="fas fa-phone-alt"></i>Phone Management
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="rates.php">
                            <i class="fas fa-percentage"></i>Rates
                        </a>
                    </li>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="shipments.php">
                            <i class="fas fa-truck"></i>Shipments
                        </a>
                    </li> -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="settingsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i>Settings
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                            <li><a class="dropdown-item" href="about.php">
                                <i class="fas fa-info-circle"></i>About Page
                            </a></li>
                            <li><a class="dropdown-item" href="search_transactions.php">
                                <i class="fas fa-search-dollar"></i>Search Transactions
                            </a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-light px-3" href="../actions/logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Demo Content -->
    <!-- <div class="demo-content">
        <div class="container">
            <h1>Recycling Paper Arts Magazine Navigation Menu</h1>
            <p>Enhanced magazine-themed navigation with Products dropdown menu</p>
            
            <div class="mt-4">
                <span class="feature-badge">Products Dropdown</span>
                <span class="feature-badge">Gradient Background</span>
                <span class="feature-badge">Hover Effects</span>
                <span class="feature-badge">Magazine Icons</span>
                <span class="feature-badge">Responsive Design</span>
                <span class="feature-badge">Magazine Colors</span>
            </div>
        </div>
    </div> -->

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>