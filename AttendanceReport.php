<?php
session_start();
require 'db.php'; // DB connection file

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$attendanceRecords = [];
$message = "";

// Handle form submit
$fromDate = $_POST['from_date'] ?? date('Y-m-d'); 
$toDate = $_POST['to_date'] ?? date('Y-m-d'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    if (empty($fromDate) || strtotime($fromDate) > strtotime($toDate)) $errors[] = "Valid From Date (before To Date) required.";
    if (empty($toDate) || strtotime($toDate) > time()) $errors[] = "Valid To Date required.";

    if (empty($errors)) {
        // Safe query with date filter
        $reportQuery = "SELECT a.Attendance_Id, s.Full_Name, c.Title as Course_Name, a.Session_Date, a.Status 
                        FROM attendance a 
                        LEFT JOIN student s ON a.student_id = s.Student_Id 
                        LEFT JOIN course c ON a.course_id = c.Course_Id 
                        WHERE a.Session_Date BETWEEN ? AND ? 
                        ORDER BY a.Session_Date DESC, s.Full_Name";

        $stmt = $conn->prepare($reportQuery);
        if (!$stmt) {
            $message = "<span style='color:red;'>Prepare failed: " . htmlspecialchars($conn->error) . "</span>";
        } else {
            $stmt->bind_param("ss", $fromDate, $toDate);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result === false) {
                $message = "<span style='color:red;'>Execute failed: " . htmlspecialchars($stmt->error) . "</span>";
            } else if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $attendanceRecords[] = $row;
                }
            } else {
                $message = "<span style='color:red;'>No records for " . htmlspecialchars($fromDate) . " to " . htmlspecialchars($toDate) . ".</span>";
            }
            $stmt->close();
        }
    } else {
        $message = "<span style='color:red;'>" . implode("<br>", $errors) . "</span>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report by Date - TVET School Management</title>
    <link rel="stylesheet" href="style.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .filter-form { margin: 20px 0; background: #f9f9f9; padding: 15px; border-radius: 5px; }
        .export-btn { background: green; color: white; padding: 10px; text-decoration: none; border-radius: 5px; }
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
            <h2>Attendance Report by Date</h2>

            <!-- Date Range Form -->
            <form method="POST" action="AttendanceReport.php" class="filter-form">
                <label>From Date:</label>
                <input type="date" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>" required><br><br>

                <label>To Date:</label>
                <input type="date" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>" required><br><br>

                <button type="submit" name="submit">Generate Report</button>
                <?php if (!empty($attendanceRecords)): ?>
                <a href="#" class="export-btn" onclick="exportToPDF()">Export PDF</a>
                <?php endif; ?>
            </form>

            <!-- Display message -->
            <?php if ($message != "") { echo "<p>$message</p>"; } ?>

            <?php if (!empty($attendanceRecords)): ?>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Course Name</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($attendanceRecords as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['Full_Name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($record['Course_Name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($record['Session_Date']); ?></td>
                    <td><?php echo htmlspecialchars($record['Status']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <p>Showing <?php echo count($attendanceRecords); ?> records from <?php echo htmlspecialchars($fromDate); ?> to <?php echo htmlspecialchars($toDate); ?>.</p>
            <?php else: ?>
            <p>No attendance records found for the selected dates.</p>
            <?php endif; ?>
        </center><br>
    </div>

    <div class="footer">
        <hr>
        <center>&copy TVET School Management System - 2025</center>
        <hr>
    </div>

    <script>
        function exportToPDF() {
            // Stub for PDF export (use FPDF or jsPDF library)
            alert('PDF export feature - implement with FPDF!');
            // Example: window.print(); for simple print
            window.print();
        }
    </script>
</body>
</html>