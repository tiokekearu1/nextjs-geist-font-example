<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

require_once '../../controllers/TeacherController.php';
$controller = new TeacherController();
$classes = $controller->getAssignedClasses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - School Attendance System</title>
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
        .location-status {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .student-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .evidence-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar py-3">
        <div class="text-center mb-4 p-3">
            <h4 class="mb-1">School Attendance</h4>
            <p class="text-muted mb-0">Teacher Dashboard</p>
        </div>
        <div class="nav flex-column">
            <a class="nav-link active" href="#attendance" data-bs-toggle="tab">
                Mark Attendance
            </a>
            <a class="nav-link" href="#evidence" data-bs-toggle="tab">
                Submit Evidence
            </a>
            <a class="nav-link" href="#history" data-bs-toggle="tab">
                History
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
                            <h2 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
                            <p class="text-muted mb-0">
                                <?php echo date('l, F j, Y'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Attendance Tab -->
                <div class="tab-pane fade show active" id="attendance">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Select Class</h5>
                                    <select class="form-select mb-3" id="classSelect">
                                        <option value="">Choose a class...</option>
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo $class['id']; ?>">
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Mark Attendance</h5>
                                    <form id="attendanceForm" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="location_lat" id="locationLat">
                                        <input type="hidden" name="location_lng" id="locationLng">
                                        
                                        <div class="student-list" id="studentList">
                                            <p class="text-muted">Please select a class to view students.</p>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary mt-3" disabled id="submitAttendance">
                                            Submit Attendance
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evidence Tab -->
                <div class="tab-pane fade" id="evidence">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Submit Teaching Evidence</h5>
                                    <form id="evidenceForm" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="location_lat" id="evidenceLocationLat">
                                        <input type="hidden" name="location_lng" id="evidenceLocationLng">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Class</label>
                                                    <select class="form-select" name="class_id" required>
                                                        <option value="">Select class...</option>
                                                        <?php foreach ($classes as $class): ?>
                                                            <option value="<?php echo $class['id']; ?>">
                                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Photo Evidence</label>
                                                    <input type="file" class="form-control" name="photo" 
                                                           accept="image/*" required id="evidencePhoto">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="description" rows="4" 
                                                              required placeholder="Describe the teaching activity..."></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="text-center">
                                                    <img id="photoPreview" class="evidence-preview d-none mb-3">
                                                    <p class="text-muted">Preview will appear here</p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary" disabled id="submitEvidence">
                                            Submit Evidence
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Tab -->
                <div class="tab-pane fade" id="history">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Recent Activity</h5>
                                    <div id="historyContent">
                                        Loading...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Status Toast -->
    <div class="location-status">
        <div class="toast" role="alert" id="locationToast">
            <div class="toast-header">
                <strong class="me-auto">Location Status</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="locationMessage">
                Checking your location...
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Location checking
        let locationValid = false;
        const toast = new bootstrap.Toast(document.getElementById('locationToast'));
        
        function checkLocation() {
            if (!navigator.geolocation) {
                updateLocationStatus('Geolocation is not supported by your browser', false);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                position => {
                    document.getElementById('locationLat').value = position.coords.latitude;
                    document.getElementById('locationLng').value = position.coords.longitude;
                    document.getElementById('evidenceLocationLat').value = position.coords.latitude;
                    document.getElementById('evidenceLocationLng').value = position.coords.longitude;
                    
                    // Here you would typically validate against school coordinates
                    // For demo, we'll assume it's valid
                    locationValid = true;
                    updateLocationStatus('Location verified - You can mark attendance', true);
                    document.getElementById('submitAttendance').disabled = false;
                    document.getElementById('submitEvidence').disabled = false;
                },
                () => {
                    updateLocationStatus('Unable to get your location', false);
                }
            );
        }

        function updateLocationStatus(message, success) {
            const messageElement = document.getElementById('locationMessage');
            messageElement.textContent = message;
            messageElement.className = 'toast-body ' + (success ? 'text-success' : 'text-danger');
            toast.show();
        }

        // Class selection and student list loading
        document.getElementById('classSelect').addEventListener('change', function() {
            const classId = this.value;
            if (!classId) {
                document.getElementById('studentList').innerHTML = '<p class="text-muted">Please select a class to view students.</p>';
                return;
            }

            fetch(`../../controllers/TeacherController.php?action=getClassStudents&class_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    const studentList = data.map(student => `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="students[]" 
                                   value="${student.id}" id="student${student.id}">
                            <label class="form-check-label" for="student${student.id}">
                                ${student.full_name}
                            </label>
                        </div>
                    `).join('');
                    
                    document.getElementById('studentList').innerHTML = studentList;
                })
                .catch(error => {
                    document.getElementById('studentList').innerHTML = `
                        <div class="alert alert-danger">
                            ${error.message}
                        </div>
                    `;
                });
        });

        // Evidence photo preview
        document.getElementById('evidencePhoto').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('photoPreview');
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Form submissions
        document.getElementById('attendanceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!locationValid) {
                updateLocationStatus('Please enable location services', false);
                return;
            }
            // Submit form logic here
        });

        document.getElementById('evidenceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!locationValid) {
                updateLocationStatus('Please enable location services', false);
                return;
            }
            // Submit form logic here
        });

        // Initialize
        checkLocation();
        setInterval(checkLocation, 60000); // Check location every minute
    </script>
</body>
</html>
