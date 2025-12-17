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
$instructor = null;

if (empty($id)) {
    $message = "Invalid ID.";
} else {
    // Fetch existing instructor
    $stmt = $conn->prepare("SELECT * FROM `instructor` WHERE Instructor_Id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $instructor = $result->fetch_assoc();
    $stmt->close();

    if (!$instructor) {
        $message = "Instructor not found.";
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        // Sanitize inputs
        $fullName = trim(htmlspecialchars($_POST['Full_Name'] ?? ''));
        $email = trim(filter_var($_POST['Email'] ?? '', FILTER_SANITIZE_EMAIL));
        $specialization = trim(htmlspecialchars($_POST['Specialization'] ?? ''));
        $hireDate = trim($_POST['Hire_Date'] ?? '');

        // Validation
        $errors = [];
        if (empty($fullName)) $errors[] = "Full Name is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid Email required.";
        $dateObj = DateTime::createFromFormat('Y-m-d', $hireDate);
        if (!empty($hireDate) && (!$dateObj || $dateObj > new DateTime())) $errors[] = "Valid past Hire Date required (or leave blank).";

        if (empty($errors)) {
            $hireDateFormatted = $dateObj ? $dateObj->format('Y-m-d') : null;
            // UPDATE query
            if ($hireDateFormatted) {
                $updateStmt = $conn->prepare("UPDATE `instructor` SET Full_Name=?, Email=?, Specialization=?, Hire_Date=? WHERE Instructor_Id=?");
                $updateStmt->bind_param("sssss", $fullName, $email, $specialization, $hireDateFormatted, $id);
            } else {
                $updateStmt = $conn->prepare("UPDATE `instructor` SET Full_Name=?, Email=?, Specialization=? WHERE Instructor_Id=?");
                $updateStmt->bind_param("ssss", $fullName, $email, $specialization, $id);
            }
            if ($updateStmt->execute()) {
                $message = "Instructor updated successfully.";
                $success = true;
                header("Location: VInstructor.php?success=updated");
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
    <title>Edit Instructor</title>
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
        <h2>Edit Instructor ID: <?php echo htmlspecialchars($id); ?></h2>
        <?php if ($message): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($instructor): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Full Name:</label><br>
                <input type="text" name="Full_Name" value="<?php echo htmlspecialchars($instructor['Full_Name']); ?>" required><br><br>

                <label>Email:</label><br>
                <input type="email" name="Email" value="<?php echo htmlspecialchars($instructor['Email']); ?>" required><br><br>

                <label>Specialization:</label><br>
                <input type="text" name="Specialization" value="<?php echo htmlspecialchars($instructor['Specialization']); ?>"><br><br>

                <label>Hire Date (YYYY-MM-DD):</label><br>
                <input type="date" name="Hire_Date" value="<?php echo htmlspecialchars($instructor['Hire_Date']); ?>"><br><br>

                <button type="submit">Update Instructor</button>
                <a href="VInstructor.php"><button type="button">Cancel</button></a>
            </form>
        <?php endif; ?>
    </center>
</div>

<div class="footer">
    <!-- Footer -->
</div>

</body>
</html>