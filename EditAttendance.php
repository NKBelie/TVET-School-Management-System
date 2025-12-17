<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = intval($_GET['id'] ?? 0); // INT PK
$message = "";
$errors = [];
$success = false;
$attendance = null;

// Quick connection check
if (!isset($conn) || !$conn || $conn->connect_error) {
    $message = "Database connection failed: " . htmlspecialchars($conn->connect_error ?? 'Unknown error');
    error_log("DB Connect Error in EditAttendance: " . ($conn->connect_error ?? 'Unknown'));
} else {
    // Fetch students and courses for dropdowns
    $studentList = [];
    $courseList = [];
    $studentsQuery = $conn->query("SELECT Student_Id, Full_Name FROM `student` WHERE Status='Active' ORDER BY Full_Name");
    if ($studentsQuery) {
        while ($row = $studentsQuery->fetch_assoc()) {
            $studentList[] = $row;
        }
        $studentsQuery->free();
    } else {
        $errors[] = "Failed to fetch students: " . htmlspecialchars($conn->error);
        error_log("Students Query Error: " . $conn->error);
    }

    $coursesQuery = $conn->query("SELECT Course_Id, Title FROM `course` ORDER BY Title");
    if ($coursesQuery) {
        while ($row = $coursesQuery->fetch_assoc()) {
            $courseList[] = $row;
        }
        $coursesQuery->free();
    } else {
        $errors[] = "Failed to fetch courses: " . htmlspecialchars($conn->error);
        error_log("Courses Query Error: " . $conn->error);
    }

    if ($id <= 0) {
        $errors[] = "Invalid ID.";
    } else {
        // Fetch existing attendance
        $stmt = $conn->prepare("SELECT * FROM `attendance` WHERE Attendance_Id = ?");
        if (!$stmt) {
            $errors[] = "Prepare failed for fetch: " . htmlspecialchars($conn->error);
            error_log("Fetch Prepare Error: " . $conn->error);
        } else {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                $errors[] = "Execute failed for fetch: " . htmlspecialchars($stmt->error);
                error_log("Fetch Execute Error: " . $stmt->error);
            } else {
                $result = $stmt->get_result();
                $attendance = $result->fetch_assoc();
                if (!$attendance) {
                    $errors[] = "Attendance record not found.";
                }
            }
            $stmt->close();
        }

        if (empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST" && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            // Sanitize inputs
            $studentId = trim(htmlspecialchars($_POST['Student_Id'] ?? ''));
            $courseId = trim(htmlspecialchars($_POST['Course_Id'] ?? ''));
            $aDate = trim($_POST['Adate'] ?? '');
            $status = trim(htmlspecialchars($_POST['Status'] ?? ''));

            // Validation
            $errors = [];
            if (empty($studentId)) $errors[] = "Student is required.";
            if (empty($courseId)) $errors[] = "Course is required.";
            if (empty($status) || !in_array($status, ['Present', 'Absent', 'Late', 'Excused'])) $errors[] = "Valid Status required.";
            $dateObj = DateTime::createFromFormat('Y-m-d', $aDate);
            if (!$dateObj || $dateObj > new DateTime()) $errors[] = "Valid past/today Attendance Date required.";

            if (empty($errors)) {
                $aDateFormatted = $dateObj->format('Y-m-d');
                // UPDATE query with prepare check
                $updateStmt = $conn->prepare("UPDATE `attendance` SET Student_Id=?, Course_Id=?, Session_Date=?, Status=? WHERE Attendance_Id=?");
                if (!$updateStmt) {
                    $errors[] = "Prepare failed for update: " . htmlspecialchars($conn->error);
                    error_log("Update Prepare Error: " . $conn->error);
                } else {
                    $updateStmt->bind_param("ssssi", $studentId, $courseId, $aDateFormatted, $status, $id);
                    if (!$updateStmt->execute()) {
                        $errors[] = "Execute failed for update: " . htmlspecialchars($updateStmt->error);
                        error_log("Update Execute Error: " . $updateStmt->error);
                    } else {
                        $message = "Attendance updated successfully.";
                        $success = true;
                        header("Location: VAttendance.php?success=updated");
                        exit;
                    }
                    $updateStmt->close();
                }
            }
        } else {
            // Generate CSRF
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
        }
    }
}

// Combine errors
if (!empty($errors)) {
    $message = implode("<br>", $errors);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Attendance</title>
    <link rel="stylesheet" href="style.css">
    <style> .error { color: red; } .success { color: green; } </style>
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
    <center><br><br>
        <h2>Edit Attendance ID: <?php echo $id; ?></h2>
        <?php if ($message): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
            <a href="VAttendance.php"><button>Back to List</button></a>
        <?php endif; ?>

        <?php if ($attendance && empty($errors)): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Student ID:</label><br>
                <select name="Student_Id" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($studentList as $student): ?>
                        <option value="<?php echo htmlspecialchars($student['Student_Id']); ?>" <?php echo ($attendance['Student_Id'] === $student['Student_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($student['Full_Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Course ID:</label><br>
                <select name="Course_Id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courseList as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['Course_Id']); ?>" <?php echo ($attendance['Course_Id'] === $course['Course_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['Title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Attendance Date:</label><br>
                <input type="date" name="Adate" value="<?php echo htmlspecialchars($attendance['Adate']); ?>" max="<?php echo date('Y-m-d'); ?>" required><br><br>

                <label>Status:</label><br>
                <select name="Status" required>
                    <option value="Present" <?php echo ($attendance['Status'] === 'Present') ? 'selected' : ''; ?>>Present</option>
                    <option value="Absent" <?php echo ($attendance['Status'] === 'Absent') ? 'selected' : ''; ?>>Absent</option>
                    <option value="Late" <?php echo ($attendance['Status'] === 'Late') ? 'selected' : ''; ?>>Late</option>
                    <option value="Excused" <?php echo ($attendance['Status'] === 'Excused') ? 'selected' : ''; ?>>Excused</option>
                </select><br><br>

                <button type="submit">Update Attendance</button>
                <a href="VAttendance.php"><button type="button">Cancel</button></a>
            </form>
        <?php endif; ?>
    </center>
</div>

<div class="footer">
    <!-- Footer -->
</div>

</body>
</html>