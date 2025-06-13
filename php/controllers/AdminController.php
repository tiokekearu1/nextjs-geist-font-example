<?php
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../models/Evidence.php';

class AdminController {
    private $teacherModel;
    private $studentModel;
    private $classModel;
    private $attendanceModel;
    private $evidenceModel;

    public function __construct() {
        $this->teacherModel = new Teacher();
        $this->studentModel = new Student();
        $this->classModel = new ClassModel();
        $this->attendanceModel = new Attendance();
        $this->evidenceModel = new Evidence();

        // Ensure admin is logged in for all actions except login
        if ($_SERVER['REQUEST_URI'] !== '/admin/login.php' && 
            (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin')) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return ['error' => 'Invalid security token'];
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // In a real application, you would have a separate admin table/model
        // For this example, we'll use hardcoded admin credentials
        if ($email === 'admin@school.com' && password_verify($password, ADMIN_PASSWORD_HASH)) {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_type'] = 'admin';
            $_SESSION['user_name'] = 'Administrator';
            
            // Generate new CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            return [
                'success' => true,
                'redirect' => 'dashboard.php'
            ];
        }

        return ['error' => 'Invalid credentials'];
    }

    // Teacher Management
    public function addTeacher() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $this->validateCSRFToken();

        $data = [
            'full_name' => filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'password' => $_POST['password'],
            'assigned_class_id' => filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_NUMBER_INT)
        ];

        if (!$data['full_name'] || !$data['email'] || !$data['password']) {
            return ['error' => 'All fields are required'];
        }

        try {
            if ($this->teacherModel->create($data)) {
                return [
                    'success' => true,
                    'message' => 'Teacher added successfully'
                ];
            }
            return ['error' => 'Failed to add teacher'];
        } catch (Exception $e) {
            error_log("Failed to add teacher: " . $e->getMessage());
            return ['error' => 'An error occurred while adding the teacher'];
        }
    }

    public function updateTeacher() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $this->validateCSRFToken();

        $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $data = [
            'full_name' => filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING),
            'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
            'assigned_class_id' => filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_NUMBER_INT)
        ];

        if (isset($_POST['password']) && !empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        try {
            if ($this->teacherModel->update($id, $data)) {
                return [
                    'success' => true,
                    'message' => 'Teacher updated successfully'
                ];
            }
            return ['error' => 'Failed to update teacher'];
        } catch (Exception $e) {
            error_log("Failed to update teacher: " . $e->getMessage());
            return ['error' => 'An error occurred while updating the teacher'];
        }
    }

    // Student Management
    public function addStudent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $this->validateCSRFToken();

        $data = [
            'full_name' => filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING),
            'class_id' => filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_NUMBER_INT)
        ];

        if (!$data['full_name'] || !$data['class_id']) {
            return ['error' => 'All fields are required'];
        }

        try {
            if ($this->studentModel->create($data)) {
                return [
                    'success' => true,
                    'message' => 'Student added successfully'
                ];
            }
            return ['error' => 'Failed to add student'];
        } catch (Exception $e) {
            error_log("Failed to add student: " . $e->getMessage());
            return ['error' => 'An error occurred while adding the student'];
        }
    }

    // Class Management
    public function addClass() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $this->validateCSRFToken();

        $className = filter_input(INPUT_POST, 'class_name', FILTER_SANITIZE_STRING);

        if (!$className) {
            return ['error' => 'Class name is required'];
        }

        try {
            if ($this->classModel->create($className)) {
                return [
                    'success' => true,
                    'message' => 'Class added successfully'
                ];
            }
            return ['error' => 'Failed to add class'];
        } catch (Exception $e) {
            error_log("Failed to add class: " . $e->getMessage());
            return ['error' => 'An error occurred while adding the class'];
        }
    }

    // Reports
    public function getAttendanceReport() {
        $classId = filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_NUMBER_INT);
        $startDate = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_STRING);
        $endDate = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_STRING);

        if (!$classId || !$startDate || !$endDate) {
            return ['error' => 'Missing required parameters'];
        }

        try {
            return [
                'success' => true,
                'data' => $this->attendanceModel->getByClass($classId, $startDate, $endDate)
            ];
        } catch (Exception $e) {
            error_log("Failed to get attendance report: " . $e->getMessage());
            return ['error' => 'An error occurred while generating the report'];
        }
    }

    public function getEvidenceReport() {
        $classId = filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_NUMBER_INT);
        $startDate = filter_input(INPUT_GET, 'start_date', FILTER_SANITIZE_STRING);
        $endDate = filter_input(INPUT_GET, 'end_date', FILTER_SANITIZE_STRING);

        if (!$classId) {
            return ['error' => 'Class ID is required'];
        }

        try {
            return [
                'success' => true,
                'data' => $this->evidenceModel->getByClass($classId, $startDate, $endDate)
            ];
        } catch (Exception $e) {
            error_log("Failed to get evidence report: " . $e->getMessage());
            return ['error' => 'An error occurred while generating the report'];
        }
    }

    // School Settings
    public function updateSchoolSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        $this->validateCSRFToken();

        $settings = [
            'school_name' => filter_input(INPUT_POST, 'school_name', FILTER_SANITIZE_STRING),
            'latitude' => filter_var($_POST['latitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'longitude' => filter_var($_POST['longitude'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'allowed_radius_meters' => filter_input(INPUT_POST, 'allowed_radius', FILTER_SANITIZE_NUMBER_INT)
        ];

        // In a real application, you would save these to a settings table
        // For this example, we'll assume success
        return [
            'success' => true,
            'message' => 'School settings updated successfully'
        ];
    }

    private function validateCSRFToken() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Invalid security token']);
            exit;
        }
    }

    public function logout() {
        session_destroy();
        return [
            'success' => true,
            'redirect' => 'login.php'
        ];
    }
}
