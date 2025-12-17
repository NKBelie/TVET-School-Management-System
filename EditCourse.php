<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = trim(htmlspecialchars($_GET['id'] ?? '')); // VARCHAR PK, so string
$message = "";
$errors = [];
$success = false;
$course = null;

// Fetch instructors for dropdown
$instructorList = [];
$instructors = $conn->query("SELECT Instructor_Id, Full_Name FROM `instructor` ORDER BY Full_Name");
if ($instructors) {
    while ($row = $instructors->fetch_assoc()) {
        $instructorList[] = $row;
    }
    $instructors->free();
}

if (empty($id)) {
    $message = "Invalid ID.";
} else {
    // Fetch existing course
    $stmt = $conn->prepare("SELECT * FROM `course` WHERE Course_Id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();

    if (!$course) {
        $message = "Course not found.";
    } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        // Sanitize inputs
        $instructorId = trim(htmlspecialchars($_POST['Instructor_Id'] ?? ''));
        $title = trim(htmlspecialchars($_POST['Title'] ?? ''));
        $description = trim(htmlspecialchars($_POST['Description'] ?? ''));
        $durationInput = trim($_POST['Duration'] ?? '');
        $startDate = trim($_POST['Start_Date'] ?? '');

        // Validation
        $errors = [];
        if (empty($instructorId)) $errors[] = "Instructor is required.";
        if (empty($title)) $errors[] = "Title is required.";
        if (empty($description)) $errors[] = "Description is required.";
        $duration = intval($durationInput);
        if ($duration <= 0) $errors[] = "Duration must be >0 weeks.";
        $dateObj = DateTime::createFromFormat('Y-m-d', $startDate);
        if (!$dateObj || $dateObj > new DateTime()) $errors[] = "Valid past/future Start Date required.";

        if (empty($errors)) {
            $startDateFormatted = $dateObj->format('Y-m-d');
            // UPDATE query
            $updateStmt = $conn->prepare("UPDATE `course` SET Instructor_Id=?, Title=?, Description=?, Duration=?, Start_Date=? WHERE Course_Id=?");
            $updateStmt->bind_param("sssiss", $instructorId, $title, $description, $duration, $startDateFormatted, $id);
            if ($updateStmt->execute()) {
                $message = "Course updated successfully.";
                $success = true;
                header("Location: VCourse.php?success=updated");
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
    <title>Edit Course</title>
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
        <h2>Edit Course ID: <?php echo htmlspecialchars($id); ?></h2>
        <?php if ($message): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <?php if ($course): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Instructor ID:</label><br>
                <select name="Instructor_Id" required>
                    <?php foreach ($instructorList as $instructor): ?>
                        <option value="<?php echo htmlspecialchars($instructor['Instructor_Id']); ?>" <?php echo ($course['Instructor_Id'] === $instructor['Instructor_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($instructor['Full_Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Title:</label><br>
                <input type="text" name="Title" value="<?php echo htmlspecialchars($course['Title']); ?>" required><br><br>

                <label>Description:</label><br>
                <textarea name="Description" rows="3" required><?php echo htmlspecialchars($course['Description']); ?></textarea><br><br>

                <label>Duration (Weeks):</label><br>
                <input type="number" name="Duration" min="1" value="<?php echo htmlspecialchars($course['Duration']); ?>" required><br><br>

                <label>Start Date:</label><br>
                <input type="date" name="Start_Date" value="<?php echo htmlspecialchars($course['Start_Date']); ?>" required><br><br>

                <button type="submit">Update Course</button>
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