USE occ_enrollment;

DROP TABLE IF EXISTS entrance_exam_schedules;
CREATE TABLE entrance_exam_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollee_id INT NOT NULL,
    exam_date DATE NOT NULL,
    exam_time TIME NOT NULL,
    exam_venue VARCHAR(100) NOT NULL DEFAULT 'OCC Campus',
    exam_type VARCHAR(50) NOT NULL DEFAULT 'Entrance Exam',
    status ENUM('scheduled', 'completed', 'passed', 'failed', 'no_show') DEFAULT 'scheduled',
    remarks TEXT,
    scheduled_by INT,
    scheduled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollee_id) REFERENCES enrollees(id) ON DELETE CASCADE,
    FOREIGN KEY (scheduled_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
