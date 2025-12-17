<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = trim(htmlspecialchars($_GET['id'] ?? ''));
$message = "";
$success = false;

if (empty($id)) {
    $message = "Invalid ID.";
} else {
    // Fetch for confirmation
    $stmt = $conn->prepare("SELECT Course_Id, Title, Instructor_Id FROM `course` WHERE Course_Id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$course) {
        $message = "Course not found.";
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            // DELETE (cascade to enrollments if FK set)
            $deleteStmt = $conn->prepare("DELETE FROM `course` WHERE Course_Id = ?");
            $deleteStmt->bind_param("s", $id);
            if ($deleteStmt->execute()) {
                $message = "Course deleted successfully.";
                $success = true;
                header("Location: VCourse.php?success=deleted");
                exit;
            } else {
                $message = "Delete failed: " . htmlspecialchars($conn->error); // e.g., FK constraint
            }
            $deleteStmt->close();
        } else {
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Delete Course</title>
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
        <h2>Delete Course</h2>
        <?php if ($message): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
            <a href="VCourse.php"><button>Back to List</button></a>
        <?php else: ?>
            <p>Are you sure you want to delete course "<?php echo htmlspecialchars($course['Title']); ?>" (Instructor ID: <?php echo htmlspecialchars($course['Instructor_Id']); ?>)? This may affect enrollments.</p>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="confirm_delete" onclick="return confirm('Permanent delete?');">Yes, Delete</button>
                <a href="VCourse.php"><button type="button">Cancel</button></a>
            </form>
        <?php endif; ?>
    </center>
</div>

<div class="footer">
    <!-- Footer -->
</div>

</body>
</html>