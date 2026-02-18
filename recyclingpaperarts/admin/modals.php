<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Modals</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }
        
        .btn-close:hover {
            opacity: 1;
        }
        
        .modal-body {
            padding: 30px;
            background: #f8f9fa;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        
        .form-control.has-icon {
            padding-left: 45px;
        }
        
        .modal-footer {
            padding: 20px 30px;
            border-top: 1px solid #e9ecef;
            border-radius: 0 0 15px 15px;
        }
        
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .calculated-field {
            background-color: #e7f3ff;
            border-color: #b8daff;
            color: #004085;
            font-weight: 600;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
            border-radius: 10px;
            font-size: 0.9rem;
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4">
                    <button type="button" class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        Change Password
                    </button>
                    <button type="button" class="btn btn-success me-3" data-bs-toggle="modal" data-bs-target="#createAgentModal">
                        Create Agent
                    </button>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        Add Product
                    </button>
                </div>
            </div>
        </div>
    </div> -->

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changePasswordModalLabel">
                        <i class="fas fa-lock me-2"></i>Change Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../actions/change_password.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">
                                <i class="fas fa-key me-2"></i>Current Password
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control has-icon" id="currentPassword" name="current_password" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">
                                <i class="fas fa-key me-2"></i>New Password
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control has-icon" id="newPassword" name="new_password" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">
                                <i class="fas fa-check-circle me-2"></i>Confirm New Password
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control has-icon" id="confirmPassword" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Create Agent Modal -->
    <div class="modal fade" id="createAgentModal" tabindex="-1" aria-labelledby="createAgentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createAgentModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Create New Agent
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../actions/create_agent.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="agentPhone" class="form-label">
                                <i class="fas fa-phone me-2"></i>Phone Number
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="text" class="form-control has-icon" id="agentPhone" name="phone_number" placeholder="Enter phone number" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="agentPassword" class="form-label">
                                <i class="fas fa-lock me-2"></i>Password
                            </label>
                            <div class="input-group">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control has-icon" id="agentPassword" name="password" placeholder="Enter password" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Agent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Add New Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="../actions/add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="alert alert-info fade-in" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Auto-calculation:</strong> Daily earning will be calculated automatically based on price and profit rate.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="productName" class="form-label">
                                    <i class="fas fa-tag me-2"></i>Product Name
                                </label>
                                <div class="input-group">
                                    <span class="input-icon">
                                        <i class="fas fa-tag"></i>
                                    </span>
                                    <input type="text" class="form-control has-icon" id="productName" name="name" placeholder="Enter product name" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="productImage" class="form-label">
                                    <i class="fas fa-image me-2"></i>Product Image
                                </label>
                                <input type="file" class="form-control" id="productImage" name="image" accept="image/*" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">
                                    <i class="fas fa-dollar-sign me-2"></i>Price ($)
                                </label>
                                <div class="input-group">
                                    <span class="input-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </span>
                                    <input type="number" class="form-control has-icon" id="price" name="price" placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="profitRate" class="form-label">
                                    <i class="fas fa-percentage me-2"></i>Profit Rate (%)
                                </label>
                                <div class="input-group">
                                    <span class="input-icon">
                                        <i class="fas fa-percentage"></i>
                                    </span>
                                    <input type="number" class="form-control has-icon" id="profitRate" name="profit_rate" placeholder="0.00" step="0.01" min="0" max="100" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cycle" class="form-label">
                                    <i class="fas fa-calendar-day me-2"></i>Cycle (Days)
                                </label>
                                <div class="input-group">
                                    <span class="input-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </span>
                                    <input type="number" class="form-control has-icon" id="cycle" name="cycle" placeholder="30" min="1" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="dailyEarning" class="form-label">
                                    <i class="fas fa-coins me-2"></i>Daily Earning ($)
                                </label>
                                <div class="input-group">
                                    <span class="input-icon">
                                        <i class="fas fa-coins"></i>
                                    </span>
                                    <input type="text" class="form-control has-icon calculated-field" id="dailyEarning" name="daily_earning" placeholder="Auto-calculated" readonly>
                                </div>
                                <small class="text-muted">Calculated as: (Price × Profit Rate) ÷ (Cycle × 100)</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-calculate daily earning
        function calculateDailyEarning() {
            const price = parseFloat(document.getElementById('price').value) || 0;
            const profitRate = parseFloat(document.getElementById('profitRate').value) || 0;
            const cycle = parseInt(document.getElementById('cycle').value) || 1;
            
            if (price > 0 && profitRate > 0 && cycle > 0) {
                const dailyEarning = (price * profitRate) /  100;
                document.getElementById('dailyEarning').value = dailyEarning.toFixed(2);
            } else {
                document.getElementById('dailyEarning').value = '';
            }
        }

        // Add event listeners for auto-calculation
        document.getElementById('price').addEventListener('input', calculateDailyEarning);
        document.getElementById('profitRate').addEventListener('input', calculateDailyEarning);
        document.getElementById('cycle').addEventListener('input', calculateDailyEarning);

        // Form validation for password confirmation
        document.getElementById('confirmPassword').addEventListener('input', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            
            if (newPassword && confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        // Reset form when modal is closed
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                }
            });
        });
    </script>
</body>
</html>