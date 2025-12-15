<?php
session_start();
require 'db.php'; // DB connection file

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize/validate inputs
    $id = htmlspecialchars($_POST['Id'] ?? '');
    $FName = htmlspecialchars(trim($_POST['Fname'] ?? ''));
    $Gender = htmlspecialchars($_POST['Gender'] ?? '');
    $Email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $Specialization = htmlspecialchars(trim($_POST['Specialization'] ?? ''));
    $hireDate = $_POST['hire_date'] ?? '';
    // No status—add if needed

    // Validation
    $errors = [];
    //if ($id <= 0) $errors[] = "Valid Instructor ID required.";
    if (empty($FName)) $errors[] = "Full Name required.";
    if (!in_array($Gender, ['Male', 'Female'])) $errors[] = "Valid Gender required.";
    if (empty($Email) || !filter_var($Email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid Email Format.";
    if (empty($Specialization)) $errors[] = "Specialization required.";
    if (empty($hireDate) || strtotime($hireDate) >= time()) $errors[] = "Valid Hire Date required (not in future).";

    if (empty($errors)) {
        // Check for existing instructor
        $checkStmt = $conn->prepare("SELECT Instructor_Id FROM instructor WHERE Instructor_Id = ? OR Email = ?");
        if (!$checkStmt) {
            $message = "<span style='color:red;'>Check prepare failed: " . htmlspecialchars($conn->error) . "</span>";
        } else {
            $checkStmt->bind_param("ss", $id, $Email);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $message = "<span style='color:red;'>Error: Instructor Already Exists (ID or Email taken).</span>";
            } else {
                // Secure INSERT (6 params: Id, Name, Gender, Email, Spec, Hire_Date)
                $stmt = $conn->prepare("INSERT INTO instructor (Instructor_Id, Full_Name, Gender, Email, Specialization, Hire_Date) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $message = "<span style='color:red;'>INSERT prepare failed: " . htmlspecialchars($conn->error) . ". Verify 'instructor' table (Instructor_Id as PK).</span>";
                } else {
                    $stmt->bind_param("ssssss", $id, $FName, $Gender, $Email, $Specialization, $hireDate); // 6 's' for strings/date
                    if ($stmt->execute()) {
                        $message = "<span style='color:green;'>Instructor Registered Successfully!</span>";
                        $_POST = []; // Clear form
                    } else {
                        $message = "<span style='color:red;'>INSERT execute failed: " . htmlspecialchars($stmt->error) . " (e.g., duplicate PK?).</span>";
                    }
                    $stmt->close();
                }
            }
            $checkStmt->close();
        }
    } else {
        $message = "<span style='color:red;'>" . implode("<br>", $errors) . "</span>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Instructor</title>
    <link rel="stylesheet" href="style.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <h2>Register Instructor</h2>

            <!-- Display message -->
            <?php if ($message != "") { echo "<p>$message</p>"; } ?>

            <form method="POST" action="Instructor.php">
                <label>Instructor ID:</label><br>
                <input type="text" name="Id" value="<?php echo htmlspecialchars($_POST['Id'] ?? ''); ?>" required><br><br>

                <label>Full Name:</label><br>
                <input type="text" name="Fname" value="<?php echo htmlspecialchars($_POST['Fname'] ?? ''); ?>" required><br><br>

                <label>Gender:</label><br>
                <select name="Gender" required>
                    <option value="">Choose</option>
                    <option value="Male" <?php echo ($_POST['Gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($_POST['Gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                </select><br><br>

                <label>Email:</label><br>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required><br><br>

                <label>Specialization:</label><br>
                <input type="text" name="Specialization" value="<?php echo htmlspecialchars($_POST['Specialization'] ?? ''); ?>" placeholder="e.g., IT Welding, Plumbing" required><br><br>

                <label>Hire Date:</label><br>
                <input type="date" name="hire_date" value="<?php echo htmlspecialchars($_POST['hire_date'] ?? date('Y-m-d')); ?>" required><br><br>

                <button type="submit" name="submit">Register</button>
            </form>
        </center>
    </div>

    <div class="footer">
        <hr>
        <center>&copy TVET School Management System - 2025</center>
        <hr>
    </div>
</body>
</html>