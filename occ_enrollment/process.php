<?php
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->connect();

    try {
        // Basic password validation
        if (!isset($_POST['account_password']) || strlen((string)$_POST['account_password']) < 8) {
            header("Location: index.php?status=error&message=" . urlencode('Password must be at least 8 characters.'));
            exit();
        }

        $passwordHash = password_hash((string)$_POST['account_password'], PASSWORD_BCRYPT);

        // Ensure required tables exist (idempotent)
        $db->exec("CREATE TABLE IF NOT EXISTS enrollees (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            lastname VARCHAR(50) NOT NULL,
            firstname VARCHAR(50) NOT NULL,
            middlename VARCHAR(50),
            lrn VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,
            gender CHAR(1) NOT NULL,
            date_of_birth DATE NOT NULL,
            age INT NOT NULL,
            civil_status VARCHAR(20) NOT NULL,
            contact_no VARCHAR(20) NOT NULL,
            last_school VARCHAR(100) NOT NULL,
            school_address TEXT NOT NULL,
            strand VARCHAR(50) NOT NULL,
            preferred_program VARCHAR(20) NOT NULL,
            status VARCHAR(20) DEFAULT 'Pending',
            is_working CHAR(1) NOT NULL DEFAULT 'N',
            employer VARCHAR(100),
            position VARCHAR(100),
            working_hours VARCHAR(50),
            preferred_schedule VARCHAR(20) NOT NULL,
            father_name VARCHAR(100) NOT NULL,
            father_occupation VARCHAR(100),
            father_education VARCHAR(100),
            father_contact VARCHAR(20),
            num_brothers INT DEFAULT 0,
            family_income DECIMAL(10,2) NOT NULL,
            mother_name VARCHAR(100) NOT NULL,
            mother_occupation VARCHAR(100),
            mother_education VARCHAR(100),
            mother_contact VARCHAR(20),
            num_sisters INT DEFAULT 0,
            guardian_name VARCHAR(100) NOT NULL,
            guardian_contact VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','registrar','faculty','student') NOT NULL,
            status ENUM('active','inactive','pending') DEFAULT 'pending',
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->exec("CREATE TABLE IF NOT EXISTS old_students (
            student_id VARCHAR(20) NOT NULL,
            lastname VARCHAR(50) NOT NULL,
            firstname VARCHAR(50) NOT NULL,
            middlename VARCHAR(50),
            course VARCHAR(10) NOT NULL,
            year_level INT NOT NULL,
            section VARCHAR(5) NOT NULL,
            current_semester INT NOT NULL,
            school_year VARCHAR(20) NOT NULL,
            units_earned INT NOT NULL DEFAULT 0,
            gpa DECIMAL(3,2),
            status VARCHAR(20) DEFAULT 'Regular',
            email VARCHAR(100) NOT NULL,
            lrn VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,
            gender CHAR(1) NOT NULL,
            date_of_birth DATE NOT NULL,
            age INT NOT NULL,
            civil_status VARCHAR(20) NOT NULL,
            contact_no VARCHAR(20) NOT NULL,
            last_school VARCHAR(100) NOT NULL,
            school_address TEXT NOT NULL,
            strand VARCHAR(50) NOT NULL,
            preferred_program VARCHAR(20) NOT NULL,
            is_working CHAR(1) NOT NULL DEFAULT 'N',
            employer VARCHAR(100),
            position VARCHAR(100),
            working_hours VARCHAR(50),
            preferred_schedule VARCHAR(20) NOT NULL,
            father_name VARCHAR(100) NOT NULL,
            father_occupation VARCHAR(100),
            father_contact VARCHAR(20),
            num_brothers INT DEFAULT 0,
            family_income DECIMAL(10,2) NOT NULL,
            mother_name VARCHAR(100) NOT NULL,
            mother_occupation VARCHAR(100),
            mother_contact VARCHAR(20),
            num_sisters INT DEFAULT 0,
            guardian_name VARCHAR(100) NOT NULL,
            guardian_contact VARCHAR(20) NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Use a transaction to keep all inserts consistent
        $db->beginTransaction();

        // Insert directly into enrollees (no email verification)
        $query = "INSERT INTO enrollees (
            email, lastname, firstname, middlename, lrn, address, gender,
            date_of_birth, age, civil_status, contact_no, last_school,
            school_address, strand, preferred_program, status, is_working, employer,
            position, working_hours, preferred_schedule, father_name,
            father_occupation, father_education, father_contact, num_brothers,
            family_income, mother_name, mother_occupation, mother_education,
            mother_contact, num_sisters, guardian_name, guardian_contact
        ) VALUES (
            :email, :lastname, :firstname, :middlename, :lrn, :address, :gender,
            :dob, :age, :civil_status, :contact, :last_school,
            :school_address, :strand, :program, 'Pending', :is_working, :employer,
            :position, :working_hours, :preferred_schedule, :father_name,
            :father_occupation, :father_education, :father_contact, :num_brothers,
            :family_income, :mother_name, :mother_occupation, :mother_education,
            :mother_contact, :num_sisters, :guardian_name, :guardian_contact
        )";

        $stmt = $db->prepare($query);
        $stmt->execute([
            ':email' => $_POST['email'] ?? '',
            ':lastname' => $_POST['lastname'] ?? '',
            ':firstname' => $_POST['firstname'] ?? '',
            ':middlename' => $_POST['middlename'] ?? null,
            ':lrn' => $_POST['lrn'] ?? '',
            ':address' => $_POST['address'] ?? '',
            ':gender' => $_POST['gender'] ?? '',
            ':dob' => $_POST['dob'] ?? '',
            ':age' => $_POST['age'] ?? 0,
            ':civil_status' => $_POST['civil_status'] ?? '',
            ':contact' => $_POST['contact'] ?? '',
            ':last_school' => $_POST['last_school'] ?? '',
            ':school_address' => $_POST['school_address'] ?? '',
            ':strand' => $_POST['strand'] ?? '',
            ':program' => $_POST['program'] ?? '',
            ':is_working' => $_POST['is_working'] ?? 'N',
            ':employer' => $_POST['employer'] ?? null,
            ':position' => $_POST['position'] ?? null,
            ':working_hours' => $_POST['working_hours'] ?? null,
            ':preferred_schedule' => $_POST['preferred_schedule'] ?? '',
            ':father_name' => $_POST['father_name'] ?? '',
            ':father_occupation' => $_POST['father_occupation'] ?? null,
            ':father_education' => $_POST['father_education'] ?? null,
            ':father_contact' => $_POST['father_contact'] ?? null,
            ':num_brothers' => $_POST['num_brothers'] ?? 0,
            ':family_income' => $_POST['family_income'] ?? 0,
            ':mother_name' => $_POST['mother_name'] ?? '',
            ':mother_occupation' => $_POST['mother_occupation'] ?? null,
            ':mother_education' => $_POST['mother_education'] ?? null,
            ':mother_contact' => $_POST['mother_contact'] ?? null,
            ':num_sisters' => $_POST['num_sisters'] ?? 0,
            ':guardian_name' => $_POST['guardian_name'] ?? '',
            ':guardian_contact' => $_POST['guardian_contact'] ?? ''
        ]);

        // Create or update users account (role=student, active; username=email)
        $userCheck = $db->prepare('SELECT id FROM users WHERE username = :u LIMIT 1');
        $userCheck->execute([':u' => $_POST['email']]);
        $user = $userCheck->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $upd = $db->prepare('UPDATE users SET email = :e, role = "student", status = "active", password = :p WHERE id = :id');
            $upd->execute([':e' => $_POST['email'], ':p' => $passwordHash, ':id' => $user['id']]);
            $userId = (int)$user['id'];
        } else {
            $ins = $db->prepare('INSERT INTO users (username, email, password, role, status) VALUES (:u, :e, :p, "student", "active")');
            $ins->execute([':u' => $_POST['email'], ':e' => $_POST['email'], ':p' => $passwordHash]);
            $userId = (int)$db->lastInsertId();
        }

        // Ensure old_students row exists (minimal defaults)
        $oldCheck = $db->prepare('SELECT student_id FROM old_students WHERE username = :u LIMIT 1');
        $oldCheck->execute([':u' => $_POST['email']]);
        $old = $oldCheck->fetch(PDO::FETCH_ASSOC);

        if (!$old) {
            $studentId = date('Y') . '-' . str_pad((string)rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $nowYear = (int)date('Y');
            $schoolYear = $nowYear . '-' . ($nowYear + 1);

            $insOld = $db->prepare('INSERT INTO old_students (
                student_id, lastname, firstname, middlename, course, year_level, section,
                current_semester, school_year, units_earned, gpa, status, email, lrn,
                address, gender, date_of_birth, age, civil_status, contact_no,
                last_school, school_address, strand, preferred_program, is_working,
                employer, position, working_hours, preferred_schedule,
                father_name, father_occupation, father_contact, num_brothers, family_income,
                mother_name, mother_occupation, mother_contact, num_sisters,
                guardian_name, guardian_contact, username, password
            ) VALUES (
                :student_id, :lastname, :firstname, :middlename, :course, :year_level, :section,
                :current_semester, :school_year, :units_earned, :gpa, :status, :email, :lrn,
                :address, :gender, :date_of_birth, :age, :civil_status, :contact_no,
                :last_school, :school_address, :strand, :preferred_program, :is_working,
                :employer, :position, :working_hours, :preferred_schedule,
                :father_name, :father_occupation, :father_contact, :num_brothers, :family_income,
                :mother_name, :mother_occupation, :mother_contact, :num_sisters,
                :guardian_name, :guardian_contact, :username, :password
            )');

            $insOld->execute([
                ':student_id' => $studentId,
                ':lastname' => $_POST['lastname'],
                ':firstname' => $_POST['firstname'],
                ':middlename' => $_POST['middlename'],
                ':course' => $_POST['program'],
                ':year_level' => 1,
                ':section' => 'A',
                ':current_semester' => 1,
                ':school_year' => $schoolYear,
                ':units_earned' => 0,
                ':gpa' => null,
                ':status' => 'Regular',
                ':email' => $_POST['email'],
                ':lrn' => $_POST['lrn'],
                ':address' => $_POST['address'],
                ':gender' => $_POST['gender'],
                ':date_of_birth' => $_POST['dob'],
                ':age' => $_POST['age'],
                ':civil_status' => $_POST['civil_status'],
                ':contact_no' => $_POST['contact'],
                ':last_school' => $_POST['last_school'],
                ':school_address' => $_POST['school_address'],
                ':strand' => $_POST['strand'],
                ':preferred_program' => $_POST['program'],
                ':is_working' => $_POST['is_working'],
                ':employer' => $_POST['employer'],
                ':position' => $_POST['position'],
                ':working_hours' => $_POST['working_hours'],
                ':preferred_schedule' => $_POST['preferred_schedule'],
                ':father_name' => $_POST['father_name'],
                ':father_occupation' => $_POST['father_occupation'],
                ':father_contact' => $_POST['father_contact'],
                ':num_brothers' => $_POST['num_brothers'],
                ':family_income' => $_POST['family_income'],
                ':mother_name' => $_POST['mother_name'],
                ':mother_occupation' => $_POST['mother_occupation'],
                ':mother_contact' => $_POST['mother_contact'],
                ':num_sisters' => $_POST['num_sisters'],
                ':guardian_name' => $_POST['guardian_name'],
                ':guardian_contact' => $_POST['guardian_contact'],
                ':username' => $_POST['email'],
                ':password' => $passwordHash
            ]);
        }

        $db->commit();

        // Redirect to success message
        header("Location: index.php?status=registered_no_verification");
        exit();
        
    } catch(PDOException $e) {
        // Log the detailed reason to help diagnose
        $log = __DIR__ . '/email_log.txt';
        @file_put_contents($log, "[" . date('Y-m-d H:i:s') . "] REGISTRATION ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        header("Location: index.php?status=error&message=" . urlencode($e->getMessage()));
        exit();
    }
}
?>
