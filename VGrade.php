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
        SELECT grade.Grade_Id, grade.Student_Id, grade.Course_Id, grade.Score, grade.Grade_Letter, grade.Graded_Date, 
               student.Full_Name, course.Title
        FROM grade
        JOIN student ON grade.Student_Id = student.Student_Id
        JOIN course ON grade.Course_Id = course.Course_Id
        ORDER BY Graded_Date DESC
    ");
    if ($result === false) {
        $message = "Failed to fetch grade records: " . htmlspecialchars($conn->error);
        error_log("Grade Query Error: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Grades</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table { width:90%; border-collapse:collapse; }
        th, td { border:1px solid black; padding:8px; text-align:center; }
        th { background:#4CAF50; color:white; }
        tr:nth-child(even) { background:#f2f2f2; }
        .error { color: red; }
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
        <h2>Recorded Grades</h2>

        <?php if ($message != ""): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php elseif ($result && $result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Grade_Id']); ?></td>
                        <td><?php echo htmlspecialchars($row['Full_Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Title']); ?></td>
                        <td><?php echo htmlspecialchars($row['Score']); ?></td>
                        <td><?php echo htmlspecialchars($row['Grade_Letter']); ?></td>
                        <td><?php echo htmlspecialchars($row['Graded_Date']); ?></td>
                        <td>
                            <a href="EditGrade.php?id=<?php echo urlencode($row['Grade_Id']); ?>"><button>Edit</button></a>
                            <a href="DeleteGrade.php?id=<?php echo urlencode($row['Grade_Id']); ?>" onclick="return confirm('Are you sure?');"><button>Delete</button></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <?php $result->free(); ?>
        <?php else: ?>
            <p style="color:red;">No grade records found.</p>
        <?php endif; ?>
    </center>
</div>

<div class="footer">
    <hr>
    <center>&copy; TVET School Management System</center>
    <hr>
</div>

</body>
</html>