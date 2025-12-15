<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = "";
$errors = [];
$success = false;

// Quick connection check
if (!isset($conn) || !$conn || $conn->connect_error) {
    $message = "Database connection failed: " . htmlspecialchars($conn->connect_error ?? 'Unknown error');
    error_log("DB Connect Error: " . ($conn->connect_error ?? 'Unknown'));
}

// Fetch active students and available courses into arrays (before POST to avoid exhaustion)
$studentList = [];
$courseList = [];
if (empty($message)) {
    $students = $conn->query("SELECT Student_Id, Full_Name FROM `student` WHERE Status='Active'");
    if ($students) {
        while ($row = $students->fetch_assoc()) {
            $studentList[] = $row;
        }
        $students->free();
    } else {
        $message = "Failed to fetch students: " . htmlspecialchars($conn->error);
        error_log("Students Query Error: " . $conn->error);
    }

    $courses = $conn->query("SELECT Course_Id, Title FROM `course`");
    if ($courses) {
        while ($row = $courses->fetch_assoc()) {
            $courseList[] = $row;
        }
        $courses->free();
    } else {
        $message = "Failed to fetch courses: " . htmlspecialchars($conn->error);
        error_log("Courses Query Error: " . $conn->error);
    }
}

// Handle submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message)) {
    // CSRF Check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $student = trim(htmlspecialchars($_POST['Student_Id'] ?? ''));
        $course = trim(htmlspecialchars($_POST['Course_Id'] ?? ''));
        $enrollDate = trim($_POST['Enroll_Date'] ?? '');
        $status = trim(htmlspecialchars($_POST['Status'] ?? ''));

        // Validation
        if (empty($student)) $errors[] = "Student is required.";
        if (empty($course)) $errors[] = "Course is required.";
        if (empty($status) || !in_array($status, ['Active', 'Completed', 'Dropped'])) $errors[] = "Valid Status required.";

        // Simple date validation (expects YYYY-MM-DD from <input type="date">)
        if (empty($enrollDate)) {
            $errors[] = "Enrollment Date is required.";
        } elseif (!DateTime::createFromFormat('Y-m-d', $enrollDate)) {
            $errors[] = "Invalid Enrollment Date format (use YYYY-MM-DD).";
        } else {
            $dateObj = DateTime::createFromFormat('Y-m-d', $enrollDate);
            $today = new DateTime();
            if ($dateObj > $today) {
                $errors[] = "Enrollment Date cannot be in the future.";
            }
            $enrollDateFormatted = $enrollDate; // Already in Y-m-d
        }

        if (empty($errors)) {
            // Prevent duplicate enrollment
            $check = $conn->prepare("SELECT COUNT(*) as count FROM enrollment WHERE Student_Id=? AND Course_Id=?");
            if (!$check) {
                $errors[] = "Check query failed: " . htmlspecialchars($conn->error);
                error_log("Check Prepare Error: " . $conn->error);
            } else {
                $check->bind_param("ss", $student, $course);
                if (!$check->execute()) {
                    $errors[] = "Check execute failed: " . htmlspecialchars($check->error);
                    error_log("Check Execute Error: " . $check->error);
                } else {
                    $result = $check->get_result()->fetch_assoc();
                    if ($result['count'] > 0) {
                        $errors[] = "This student is already enrolled in this course.";
                    } else {
                        // Insert
                        $stmt = $conn->prepare("INSERT INTO `enrollment` (Student_Id, Course_Id, Enrollment_Date, Status) VALUES (?, ?, ?, ?)");
                        if (!$stmt) {
                            $errors[] = "Insert prepare failed: " . htmlspecialchars($conn->error);
                            error_log("Insert Prepare Error: " . $conn->error);
                        } else {
                            $stmt->bind_param("ssss", $student, $course, $enrollDateFormatted, $status);
                            if ($stmt->execute()) {
                                $message = "Enrollment Successful";
                                $success = true;
                                $_POST = []; // Reset form
                                $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate token
                            } else {
                                $errors[] = "Enrollment failed: " . htmlspecialchars($stmt->error);
                                error_log("Insert Execute Error: " . $stmt->error);
                            }
                            $stmt->close();
                        }
                    }
                }
                $check->close();
            }
        }
    }
}

// Combine errors into message
if (!empty($errors)) {
    $message = implode("<br>", $errors);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enroll Student</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>

<div class="header">
    <div class="navbar">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#">Student</a>
                <div class="submenu">
                    <ul>
                        <li><a href="Student.php">Register</a></li>
                        <li><a href="VStudent.php">View</a></li>
                    </ul>
                </div>
            </li>
            <li><a href="#">Instructor</a>
                <div class="submenu">
                    <ul>
                        <li><a href="Instructor.php">Register</a></li>
                        <li><a href="VInstructor.php">View</a></li>
                    </ul>
                </div>
            </li>
            <li><a href="#">Course</a>
                <div class="submenu">
                    <ul>
                        <li><a href="Course.php">Register</a></li>
                        <li><a href="VCourse.php">View</a></li>
                    </ul>
                </div>
            </li>
            <li><a href="#">Enrollment</a>
                <div class="submenu">
                    <ul>
                        <li><a href="Enrollment.php">Register</a></li>
                        <li><a href="VEnrollment.php">View</a></li>
                    </ul>
                </div>
            </li>
            <li><a href="#">Attendance</a>
                <div class="submenu">
                    <ul>
                        <li><a href="Attendance.php">Register</a></li>
                        <li><a href="VAttendance.php">View</a></li>
                    </ul>
                </div>
            </li>
            <li><a href="#">Grade</a>
                <div class="submenu">
                    <ul>
                        <li><a href="Grade.php">Register</a></li>
                        <li><a href="VGrade.php">View</a></li>
                    </ul>
                </div>
            </li>
            <li><a href="#">Report</a>
                <div class="submenu">
                    <ul>
                        <li><a href="SingleReport.php">Single</a></li>
                        <li><a href="FullReport.php">Full</a></li>
                        <li><a href="AttendanceReport.php">Attendance</a></li>
                        <li><a href="Dashboard.php">Dashboard</a></li>
                    </ul>
                </div>
            </li>
            <li><a href="logout.php">LogOut</a></li>
        </ul>
    </div>
</div>

<div class="content">
    <center><br><br><br>
        <h2>Enroll Student Into Course</h2>

        <!-- Display message -->
        <?php if ($message != "") { 
            $class = $success ? 'success' : 'error';
            echo "<p class='$class'>" . htmlspecialchars($message) . "</p>"; 
        } ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label>Student:</label><br>
            <select name="Student_Id" required>
                <option value="">-- Select Student --</option>
                <?php foreach ($studentList as $row): ?>
                    <option value="<?php echo htmlspecialchars($row['Student_Id']); ?>" <?php echo (($_POST['Student_Id'] ?? '') === $row['Student_Id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['Student_Id'] . '||' . $row['Full_Name']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Course:</label><br>
            <select name="Course_Id" required>
                <option value="">-- Select Course --</option>
                <?php foreach ($courseList as $row): ?>
                    <option value="<?php echo htmlspecialchars($row['Course_Id']); ?>" <?php echo (($_POST['Course_Id'] ?? '') === $row['Course_Id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['Course_Id'] . '||' . $row['Title']); ?>
                    </option>
                <?php endforeach; ?>
            </select><br><br>

            <label>Enrollment Date:</label><br>
            <input type="date" name="Enroll_Date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_POST['Enrollment_Date'] ?? date('Y-m-d')); ?>" required><br><br>

            <label>Status:</label><br>
            <select name="Status" required>
                <option value="Active" <?php echo (($_POST['Status'] ?? 'Active') === 'Active') ? 'selected' : ''; ?>>Active</option>
                <option value="Completed" <?php echo (($_POST['Status'] ?? '') === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                <option value="Dropped" <?php echo (($_POST['Status'] ?? '') === 'Dropped') ? 'selected' : ''; ?>>Dropped</option>
            </select><br><br>

            <button type="submit">Submit Enrollment</button>
        </form>
    </center>
</div>

<div class="footer">
    <hr>
    <center>&copy; TVET School Management System</center>
    <hr>
</div>

</body>
</html>