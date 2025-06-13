<?php
session_start();
// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Attendance System - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 90%;
            padding: 2rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background: none;
            border-bottom: none;
            padding-bottom: 0;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }
        .btn-primary {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-weight: 500;
        }
        .alert {
            border-radius: 8px;
        }
        .system-name {
            color: #1a1a1a;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .login-type {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header text-center pt-4">
                <h1 class="system-name">School Attendance System</h1>
                <p class="login-type mb-0">Teacher & Admin Login</p>
            </div>
            <div class="card-body p-4">
                <form id="loginForm" method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               placeholder="Enter your email">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               placeholder="Enter your password">
                    </div>

                    <div class="mb-3">
                        <select class="form-select" name="login_type" id="loginType" required>
                            <option value="">Select Login Type</option>
                            <option value="teacher">Teacher</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const loginType = document.getElementById('loginType').value;
            const form = this;
            
            if (loginType === 'teacher') {
                form.action = '../controllers/TeacherController.php?action=login';
            } else if (loginType === 'admin') {
                form.action = '../controllers/AdminController.php?action=login';
            }
            
            form.submit();
        });
    </script>
</body>
</html>
