<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../../controllers/AdminController.php';
$controller = new AdminController();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - School Attendance System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .sidebar {
            background: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        .nav-link {
            color: #1a1a1a;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            margin: 0.2rem 1rem;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #f8f9fa;
            color: #0d6efd;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .stats-card {
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .table {
            vertical-align: middle;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar py-3">
        <div class="text-center mb-4 p-3">
            <h4 class="mb-1">School Attendance</h4>
            <p class="text-muted mb-0">Administrator Panel</p>
        </div>
        <div class="nav flex-column">
            <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                Dashboard
            </a>
            <a class="nav-link" href="#teachers" data-bs-toggle="tab">
                Manage Teachers
            </a>
            <a class="nav-link" href="#students" data-bs-toggle="tab">
                Manage Students
            </a>
            <a class="nav-link" href="#classes" data-bs-toggle="tab">
                Manage Classes
            </a>
            <a class="nav-link" href="#reports" data-bs-toggle="tab">
                Reports
            </a>
            <a class="nav-link" href="#settings" data-bs-toggle="tab">
                School Settings
            </a>
            <a class="nav-link text-danger" href="../logout.php">
                Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Welcome Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Welcome, Administrator</h2>
                            <p class="text-muted mb-0">
                                <?php echo date('l, F j, Y'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Dashboard Tab -->
                <div class="tab-pane fade show active" id="dashboard">
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Total Teachers</h6>
                                    <h3 class="mb-0" id="teacherCount">Loading...</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Total Students</h6>
                                    <h3 class="mb-0" id="studentCount">Loading...</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Classes</h6>
                                    <h3 class="mb-0" id="classCount">Loading...</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-4">
                            <div class="card stats-card">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Today's Attendance</h6>
                                    <h3 class="mb-0" id="todayAttendance">Loading...</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Recent Activity</h5>
                                    <div class="table-responsive">
                                        <table class="table" id="recentActivity">
                                            <thead>
                                                <tr>
                                                    <th>Time</th>
                                                    <th>Teacher</th>
                                                    <th>Class</th>
                                                    <th>Activity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="4" class="text-center">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teachers Tab -->
                <div class="tab-pane fade" id="teachers">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Manage Teachers</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                                    Add New Teacher
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table" id="teachersTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Assigned Class</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Tab -->
                <div class="tab-pane fade" id="students">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Manage Students</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                    Add New Student
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table" id="studentsTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Class</th>
                                            <th>Attendance Rate</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Classes Tab -->
                <div class="tab-pane fade" id="classes">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Manage Classes</h5>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                                    Add New Class
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table" id="classesTable">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Students</th>
                                            <th>Teacher</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Tab -->
                <div class="tab-pane fade" id="reports">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Generate Reports</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Report Type</label>
                                        <select class="form-select" id="reportType">
                                            <option value="attendance">Attendance Report</option>
                                            <option value="evidence">Teaching Evidence Report</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Class</label>
                                        <select class="form-select" id="reportClass">
                                            <option value="">All Classes</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Date Range</label>
                                        <input type="date" class="form-control" id="reportDate">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary" id="generateReport">
                                Generate Report
                            </button>
                            <div id="reportContent" class="mt-4">
                                <!-- Report content will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div class="tab-pane fade" id="settings">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">School Settings</h5>
                            <form id="settingsForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">School Name</label>
                                            <input type="text" class="form-control" name="school_name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Allowed Radius (meters)</label>
                                            <input type="number" class="form-control" name="allowed_radius" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">School Latitude</label>
                                            <input type="text" class="form-control" name="latitude" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">School Longitude</label>
                                            <input type="text" class="form-control" name="longitude" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    Save Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Add Teacher Modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTeacherForm">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigned Class</label>
                            <select class="form-select" name="class_id" required>
                                <option value="">Select class...</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTeacher">Add Teacher</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load dashboard statistics
        function loadDashboardStats() {
            // Fetch counts from server
            fetch('../../controllers/AdminController.php?action=getDashboardStats')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('teacherCount').textContent = data.teacherCount;
                    document.getElementById('studentCount').textContent = data.studentCount;
                    document.getElementById('classCount').textContent = data.classCount;
                    document.getElementById('todayAttendance').textContent = data.todayAttendance + '%';
                })
                .catch(error => console.error('Error loading dashboard stats:', error));
        }

        // Load recent activity
        function loadRecentActivity() {
            fetch('../../controllers/AdminController.php?action=getRecentActivity')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#recentActivity tbody');
                    tbody.innerHTML = data.map(activity => `
                        <tr>
                            <td>${activity.time}</td>
                            <td>${activity.teacher}</td>
                            <td>${activity.class}</td>
                            <td>${activity.activity}</td>
                        </tr>
                    `).join('');
                })
                .catch(error => console.error('Error loading recent activity:', error));
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
            loadRecentActivity();
            
            // Refresh data periodically
            setInterval(loadDashboardStats, 60000);
            setInterval(loadRecentActivity, 30000);
        });

        // Form submissions
        document.getElementById('settingsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../../controllers/AdminController.php?action=updateSchoolSettings', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Settings updated successfully');
                } else {
                    alert(data.error || 'Failed to update settings');
                }
            })
            .catch(error => console.error('Error updating settings:', error));
        });

        // Add more event listeners and functionality as needed
    </script>
</body>
</html>
