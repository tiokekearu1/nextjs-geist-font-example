<?php
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../models/Evidence.php';

class TeacherController {
    private $teacherModel;
    private $studentModel;
    private $attendanceModel;
    private $evidenceModel;

    public function __construct() {
        $this->teacherModel = new Teacher();
        $this->studentModel = new Student();
        $this->attendanceModel = new Attendance();
        $this->evidenceModel = new Evidence();
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

        if (!$email || !$password) {
            return ['error' => 'Email and password are required'];
        }

        $teacher = $this->teacherModel->authenticate($email, $password);
        if ($teacher) {
            $_SESSION['user_id'] = $teacher['id'];
            $_SESSION['user_type'] = 'teacher';
            $_SESSION['user_name'] = $teacher['full_name'];
            
            // Generate new CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            return [
                'success' => true,
                'redirect' => 'dashboard.php'
            ];
        }

        return ['error' => 'Invalid credentials'];
    }

    public function markAttendance() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        // Authentication check
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
            return ['error' => 'Unauthorized access'];
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return ['error' => 'Invalid security token'];
        }

        // Validate required fields
        if (!isset($_POST['class_id']) || !isset($_POST['students']) || 
            !isset($_POST['location_lat']) || !isset($_POST['location_lng'])) {
            return ['error' => 'Missing required fields'];
        }

        $teacherId = $_SESSION['user_id'];
        $classId = filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_NUMBER_INT);
        $students = $_POST['students']; // Array of student IDs
        $lat = filter_var($_POST['location_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $lng = filter_var($_POST['location_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Validate location
        if (!$this->teacherModel->isWithinSchoolRadius($lat, $lng)) {
            return ['error' => 'You must be within school premises to mark attendance'];
        }

        try {
            $success = $this->attendanceModel->bulkRecord($teacherId, $classId, $students, $lat, $lng);
            if ($success) {
                return [
                    'success' => true,
                    'message' => 'Attendance marked successfully'
                ];
            }
            return ['error' => 'Failed to mark attendance'];
        } catch (Exception $e) {
            error_log("Attendance marking failed: " . $e->getMessage());
            return ['error' => 'An error occurred while marking attendance'];
        }
    }

    public function submitEvidence() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['error' => 'Invalid request method'];
        }

        // Authentication check
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
            return ['error' => 'Unauthorized access'];
        }

        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return ['error' => 'Invalid security token'];
        }

        // Validate required fields
        if (!isset($_POST['class_id']) || !isset($_FILES['photo']) || 
            !isset($_POST['location_lat']) || !isset($_POST['location_lng'])) {
            return ['error' => 'Missing required fields'];
        }

        $teacherId = $_SESSION['user_id'];
        $classId = filter_input(INPUT_POST, 'class_id', FILTER_SANITIZE_NUMBER_INT);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $lat = filter_var($_POST['location_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $lng = filter_var($_POST['location_lng'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Validate location
        if (!$this->teacherModel->isWithinSchoolRadius($lat, $lng)) {
            return ['error' => 'You must be within school premises to submit evidence'];
        }

        try {
            $evidenceData = [
                'teacher_id' => $teacherId,
                'class_id' => $classId,
                'description' => $description,
                'location_lat' => $lat,
                'location_lng' => $lng
            ];

            $result = $this->evidenceModel->submit($evidenceData, $_FILES['photo']);
            
            return [
                'success' => true,
                'message' => 'Evidence submitted successfully',
                'evidence_id' => $result['id'],
                'photo_path' => $result['photo_path']
            ];
        } catch (Exception $e) {
            error_log("Evidence submission failed: " . $e->getMessage());
            return ['error' => 'An error occurred while submitting evidence'];
        }
    }

    public function getAssignedClasses() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
            return ['error' => 'Unauthorized access'];
        }

        $teacherId = $_SESSION['user_id'];
        return $this->teacherModel->getAssignedClasses($teacherId);
    }

    public function getClassStudents($classId) {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
            return ['error' => 'Unauthorized access'];
        }

        $classId = filter_input(INPUT_GET, 'class_id', FILTER_SANITIZE_NUMBER_INT);
        return $this->studentModel->getByClass($classId);
    }

    public function getTeacherEvidence() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
            return ['error' => 'Unauthorized access'];
        }

        $teacherId = $_SESSION['user_id'];
        return $this->evidenceModel->getByTeacher($teacherId);
    }

    public function logout() {
        session_destroy();
        return [
            'success' => true,
            'redirect' => 'login.php'
        ];
    }
}
