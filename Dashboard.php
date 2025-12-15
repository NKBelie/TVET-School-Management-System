<?php
session_start();
require 'db.php'; // DB connection file

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$stats = [];
$message = "";

// Example dashboard queries (safe - customize as needed)
$studentQuery = "SELECT COUNT(*) as total_students FROM student";
$courseQuery = "SELECT COUNT(*) as total_courses FROM course";
$enrollmentQuery = "SELECT COUNT(*) as total_enrollments FROM enrollment";

// Fetch student count
$studentResult = $conn->query($studentQuery);
if ($studentResult === false) {
    $message = "<span style='color:red;'>Student query failed: " . htmlspecialchars($conn->error) . "</span>";
} else {
    $stats['students'] = $studentResult->fetch_assoc()['total_students'] ?? 0;
}

// Fetch course count
$courseResult = $conn->query($courseQuery);
if ($courseResult === false) {
    $message .= "<br>Course query failed: " . htmlspecialchars($conn->error);
} else {
    $stats['courses'] = $courseResult->fetch_assoc()['total_courses'] ?? 0;
}

// Fetch enrollment count
$enrollmentResult = $conn->query($enrollmentQuery);
if ($enrollmentResult === false) {
    $message .= "<br>Enrollment query failed: " . htmlspecialchars($conn->error);
} else {
    $stats['enrollments'] = $enrollmentResult->fetch_assoc()['total_enrollments'] ?? 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - TVET School Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats { display: flex; justify-content: space-around; margin: 20px; }
        .stat-box { background: #f0f0f0; padding: 20px; border-radius: 10px; text-align: center; width: 30%; }
        .stat-number { font-size: 2em; color: blue; }
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
        <center><br><br>
            <h2>Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'User'); ?></p>

            <!-- Display message if errors -->
            <?php if ($message != "") { echo "<p style='color: red;'>$message</p>"; } ?>

            <div class="stats">
                <div class="stat-box">
                    <h3>Total Students</h3>
                    <div class="stat-number"><?php echo $stats['students'] ?? 0; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Total Courses</h3>
                    <div class="stat-number"><?php echo $stats['courses'] ?? 0; ?></div>
                </div>
                <div class="stat-box">
                    <h3>Total Enrollments</h3>
                    <div class="stat-number"><?php echo $stats['enrollments'] ?? 0; ?></div>
                </div>
            </div>

            <h3>Quick Actions</h3>
            <a href="Student.php"><button>Register Student</button></a>
            <a href="Instructor.php"><button>Register Instructor</button></a>
            <a href="Course.php"><button>Register Course</button></a>
            <a href="Enrollment.php"><button>Enroll Student</button></a>
        </center><br>
    </div>

    <div class="footer">
        <hr>
        <center>&copy TVET School Management System - 2025</center>
        <hr>
    </div>
</body>
</html>