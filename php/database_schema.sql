-- School Attendance System Database Schema

-- Drop existing tables if they exist
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS evidence;
DROP TABLE IF EXISTS school_settings;
SET FOREIGN_KEY_CHECKS = 1;

-- Create Classes table
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Teachers table
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    assigned_class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_class_id) REFERENCES classes(id) ON DELETE SET NULL,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    INDEX idx_class (class_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Attendance table
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location_lat DECIMAL(10, 7) NOT NULL,
    location_lng DECIMAL(10, 7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_date (date),
    INDEX idx_teacher_date (teacher_id, date),
    INDEX idx_student_date (student_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create Evidence table
CREATE TABLE evidence (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    class_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    description TEXT,
    timestamp DATETIME NOT NULL,
    location_lat DECIMAL(10, 7) NOT NULL,
    location_lng DECIMAL(10, 7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_teacher_timestamp (teacher_id, timestamp),
    INDEX idx_class_timestamp (class_id, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create School Settings table
CREATE TABLE school_settings (
    id INT PRIMARY KEY DEFAULT 1,
    school_name VARCHAR(100) NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    allowed_radius_meters INT NOT NULL DEFAULT 100,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CHECK (id = 1) -- Ensure only one row exists
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default school settings
INSERT INTO school_settings (school_name, latitude, longitude, allowed_radius_meters)
VALUES ('My School', 40.712776, -74.005974, 100);

-- Create views for reporting
CREATE OR REPLACE VIEW attendance_summary AS
SELECT 
    a.date,
    c.class_name,
    COUNT(DISTINCT a.student_id) as present_count,
    (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id) as total_students,
    ROUND((COUNT(DISTINCT a.student_id) / 
           (SELECT COUNT(*) FROM students s WHERE s.class_id = c.id) * 100), 2) as attendance_percentage
FROM attendance a
JOIN classes c ON a.class_id = c.id
GROUP BY a.date, c.id;

CREATE OR REPLACE VIEW teacher_activity AS
SELECT 
    t.full_name as teacher_name,
    c.class_name,
    COUNT(DISTINCT a.date) as days_present,
    COUNT(DISTINCT e.id) as evidence_submitted
FROM teachers t
LEFT JOIN attendance a ON t.id = a.teacher_id
LEFT JOIN evidence e ON t.id = e.teacher_id
LEFT JOIN classes c ON t.assigned_class_id = c.id
GROUP BY t.id;

-- Create triggers for data integrity
DELIMITER //

CREATE TRIGGER before_attendance_insert
BEFORE INSERT ON attendance
FOR EACH ROW
BEGIN
    -- Ensure teacher is assigned to the class
    IF NOT EXISTS (
        SELECT 1 FROM teachers 
        WHERE id = NEW.teacher_id 
        AND assigned_class_id = NEW.class_id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Teacher is not assigned to this class';
    END IF;
    
    -- Ensure student belongs to the class
    IF NOT EXISTS (
        SELECT 1 FROM students 
        WHERE id = NEW.student_id 
        AND class_id = NEW.class_id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Student does not belong to this class';
    END IF;
END//

CREATE TRIGGER before_evidence_insert
BEFORE INSERT ON evidence
FOR EACH ROW
BEGIN
    -- Ensure teacher is assigned to the class
    IF NOT EXISTS (
        SELECT 1 FROM teachers 
        WHERE id = NEW.teacher_id 
        AND assigned_class_id = NEW.class_id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Teacher is not assigned to this class';
    END IF;
END//

DELIMITER ;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE GetClassAttendance(
    IN p_class_id INT,
    IN p_date DATE
)
BEGIN
    SELECT 
        s.id as student_id,
        s.full_name as student_name,
        CASE WHEN a.id IS NOT NULL THEN 'Present' ELSE 'Absent' END as status,
        a.time as marked_time,
        t.full_name as marked_by
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id AND a.date = p_date
    LEFT JOIN teachers t ON a.teacher_id = t.id
    WHERE s.class_id = p_class_id
    ORDER BY s.full_name;
END//

CREATE PROCEDURE GetTeacherStats(
    IN p_teacher_id INT,
    IN p_start_date DATE,
    IN p_end_date DATE
)
BEGIN
    SELECT 
        DATE_FORMAT(a.date, '%Y-%m-%d') as date,
        COUNT(DISTINCT a.student_id) as students_marked,
        COUNT(DISTINCT e.id) as evidence_submitted
    FROM teachers t
    LEFT JOIN attendance a ON t.id = a.teacher_id 
        AND a.date BETWEEN p_start_date AND p_end_date
    LEFT JOIN evidence e ON t.id = e.teacher_id 
        AND DATE(e.timestamp) BETWEEN p_start_date AND p_end_date
    WHERE t.id = p_teacher_id
    GROUP BY a.date
    ORDER BY a.date DESC;
END//

DELIMITER ;
