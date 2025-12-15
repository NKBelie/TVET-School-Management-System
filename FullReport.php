<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$message = "";
$result = false;

// Quick connection check
if (!isset($conn) || !$conn || $conn->connect_error) {
    $message = "Database connection failed: " . htmlspecialchars($conn->connect_error ?? 'Unknown error');
    error_log("DB Connect Error: " . ($conn->connect_error ?? 'Unknown'));
} else {
    $result = $conn->query("
        SELECT 
            s.Student_Id, s.Full_Name as Student_Name, s.Gender,
            c.Course_Id, c.Title as Course_Name, c.Description,
            e.Enrollment_Date, e.Status as Enrollment_Status,
            a.Session_Date, a.Status as Attendance_Status,
            g.Score, g.Grade_Letter, g.Graded_Date
        FROM `student` s
        LEFT JOIN `enrollment` e ON s.Student_Id = e.Student_Id
        LEFT JOIN `course` c ON e.Course_Id = c.Course_Id
        LEFT JOIN `attendance` a ON s.Student_Id = a.Student_Id AND a.Course_Id = c.Course_Id
        LEFT JOIN `grade` g ON s.Student_Id = g.Student_Id AND g.Course_Id = c.Course_Id
        ORDER BY s.Student_Id, c.Course_Id
    ");
    if ($result === false) {
        $message = "Failed to fetch full report data: " . htmlspecialchars($conn->error);
        error_log("Full Report Query Error: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Full System Report</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width:95%; border-collapse:collapse; }
        th, td { border:1px solid black; padding:6px; text-align:left; }
        th { background:#4CAF50; color:white; }
        tr:nth-child(even) { background:#f2f2f2; }
        .error { color: red; }
        .group { font-weight: bold; background: #e0e0e0; }
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
        <h2>Full System Report</h2>

        <?php if ($message != ""): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php elseif ($result && $result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Gender</th>
                    <th>Course ID</th>
                    <th>Course Name</th>
                    <th>Enroll Date</th>
                    <th>EnrollStatus</th>
                    <th>Attend Date</th>
                    <th>Attend Status</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Grade Date</th>
                </tr>
                <?php 
                $lastStudent = '';
                while ($row = $result->fetch_assoc()): 
                    if ($row['Student_Id'] !== $lastStudent): 
                        $lastStudent = $row['Student_Id'];
                ?>
                    <tr class="group">
                        <td colspan="12"><?php echo htmlspecialchars($row['Student_Id'] . ' - ' . $row['Student_Name']); ?></td>
                    </tr>
                <?php endif; ?>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><?php echo htmlspecialchars($row['Gender'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Course_Id'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Course_Name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Enrollment_Date'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Enrollment_Status'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Session_Date'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Attendance_Status'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Score'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Grade_Letter'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['Graded_Date'] ?? ''); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <?php $result->free(); ?>
        <?php else: ?>
            <p style="color:red;">No full report data found. Record some students, enrollments, attendances, and grades first.</p>
        <?php endif; ?>
    </center>
</div>
<br>

<div class="footer">
    <hr>
    <center>&copy; TVET School Management System</center>
    <hr>
</div>

</body>
</html>