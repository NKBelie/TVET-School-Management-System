<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = trim(htmlspecialchars($_GET['id'] ?? '')); // VARCHAR PK
$message = "";
$errors = [];
$success = false;
$student = null;

if (empty($id)) {
    $message = "Invalid ID.";
} else {
    // Fetch existing student
    $stmt = $conn->prepare("SELECT * FROM `student` WHERE Student_Id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();

    if (!$student) {
        $message = "Student not found.";
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        // Sanitize inputs
        $fullName = trim(htmlspecialchars($_POST['Full_Name'] ?? ''));
        $gender = trim(htmlspecialchars($_POST['Gender'] ?? ''));
        $email = trim(filter_var($_POST['Email'] ?? '', FILTER_SANITIZE_EMAIL));
        $phone = trim(htmlspecialchars($_POST['Phone'] ?? ''));
        $status = trim(htmlspecialchars($_POST['Status'] ?? ''));

        // Validation
        $errors = [];
        if (empty($fullName)) $errors[] = "Full Name is required.";
        if (!in_array($gender, ['Male', 'Female', 'Other'])) $errors[] = "Valid Gender required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email required.";
        if (empty($phone)) $errors[] = "Phone is required.";
        if (!in_array($status, ['Active', 'Inactive', 'Graduated'])) $errors[] = "Valid Status required.";

        if (empty($errors)) {
            // UPDATE query
            $updateStmt = $conn->prepare("UPDATE `student` SET Full_Name=?, Gender=?, Email=?, Phone=?, Status=? WHERE Student_Id=?");
            $updateStmt->bind_param("ssssss", $fullName, $gender, $email, $phone, $status, $id);
            if ($updateStmt->execute()) {
                $message = "Student updated successfully.";
                $success = true;
                header("Location: VStudent.php?success=updated");
                exit;
            } else {
                $errors[] = "Update failed: " . htmlspecialchars($conn->error);
            }
            $updateStmt->close();
        }
    } else {
        // Generate CSRF
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
    <title>Edit Student</title>
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
        <h2>Edit Student ID: <?php echo htmlspecialchars($id); ?></h2>
        <?php if ($message): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($student): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Full Name:</label><br>
                <input type="text" name="Full_Name" value="<?php echo htmlspecialchars($student['Full_Name']); ?>" required><br><br>

                <label>Gender:</label><br>
                <select name="Gender" required>
                    <option value="Male" <?php echo ($student['Gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($student['Gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($student['Gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select><br><br>

                <label>Email:</label><br>
                <input type="email" name="Email" value="<?php echo htmlspecialchars($student['Email']); ?>" required><br><br>

                <label>Phone:</label><br>
                <input type="tel" name="Phone" value="<?php echo htmlspecialchars($student['Phone']); ?>" required><br><br>

                <label>Status:</label><br>
                <select name="Status" required>
                    <option value="Active" <?php echo ($student['Status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo ($student['Status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    <option value="Graduated" <?php echo ($student['Status'] === 'Graduated') ? 'selected' : ''; ?>>Graduated</option>
                </select><br><br>

                <button type="submit">Update Student</button>
                <a href="VStudent.php"><button type="button">Cancel</button></a>
            </form>
        <?php endif; ?>
    </center>
</div>

<div class="footer">
    <!-- Footer -->
</div>

</body>
</html>