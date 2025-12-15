<?php
session_start();
require 'db.php'; // DB connection file (if needed for future features)

// Check if logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TVET School Management System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
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
        <br><br><br>
        <center style="font-size: 25px;">
            <br><br><br>
            Hello!!<br>
            Welcome to TVET School Management System <br>
            <?php echo htmlspecialchars($_SESSION['user']['username']); ?>
        </center>
        <br><br><br><br>
    </div>
    <div class="footer">
        <hr>
        <center>&copy TVET School Management System - 2025</center>
        <hr>
    </div>
</body>
</html>