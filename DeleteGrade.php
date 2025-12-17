<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = intval($_GET['id'] ?? 0); // INT PK
$message = "";
$success = false;
$grade = null;

if ($id <= 0) {
    $message = "Invalid ID.";
} else {
    // Fetch for confirmation
    $stmt = $conn->prepare("SELECT Grade_Id, Student_Id, Course_Id, Score, Grade, Gdate FROM `grade` WHERE Grade_Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $grade = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$grade) {
        $message = "Grade record not found.";
    } else {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            // DELETE
            $deleteStmt = $conn->prepare("DELETE FROM `grade` WHERE Grade_Id = ?");
            $deleteStmt->bind_param("i", $id);
            if ($deleteStmt->execute()) {
                $message = "Grade deleted successfully.";
                $success = true;
                header("Location: VGrade.php?success=deleted");
                exit;
            } else {
                $message = "Delete failed: " . htmlspecialchars($conn->error);
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
    <title>Delete Grade</title>
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
        <h2>Delete Grade</h2>
        <?php if ($message): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
            <a href="VGrade.php"><button>Back to List</button></a>
        <?php else: ?>
            <p>Are you sure you want to delete grade for Student ID: <?php echo htmlspecialchars($grade['Student_Id']); ?> in Course ID: <?php echo htmlspecialchars($grade['Course_Id']); ?> (Score: <?php echo htmlspecialchars($grade['Score']); ?>, Grade: <?php echo htmlspecialchars($grade['Grade']); ?>, Date: <?php echo htmlspecialchars($grade['Gdate']); ?>)?</p>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="confirm_delete" onclick="return confirm('Permanent delete?');">Yes, Delete</button>
                <a href="VGrade.php"><button type="button">Cancel</button></a>
            </form>
        <?php endif; ?>
    </center>
</div>

<div class="footer">
    <!-- Footer -->
</div>

</body>
</html>