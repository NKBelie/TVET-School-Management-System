<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$id = intval($_GET['id'] ?? 0); // INT PK
$message = "";
$errors = [];
$success = false;
$enroll = null;

// Quick connection check
if (!isset($conn) || !$conn || $conn->connect_error) {
    $message = "Database connection failed: " . htmlspecialchars($conn->connect_error ?? 'Unknown error');
    error_log("DB Connect Error in EditEnrollment: " . ($conn->connect_error ?? 'Unknown'));
} else {
    // Fetch courses for dropdown (as in Enrollment.php)
    $courseList = [];
    $coursesQuery = $conn->query("SELECT Course_Id, Title FROM `course` ORDER BY Title");
    if ($coursesQuery) {
        while ($row = $coursesQuery->fetch_assoc()) {
            $courseList[] = $row;
        }
        $coursesQuery->free();
    } else {
        $errors[] = "Failed to fetch courses: " . htmlspecialchars($conn->error);
        error_log("Courses Query Error: " . $conn->error);
    }

    if ($id <= 0) {
        $errors[] = "Invalid ID.";
    } else {
        // Fetch existing enrollment
        $stmt = $conn->prepare("SELECT * FROM `enrollment` WHERE Enroll_Id = ?");
        if (!$stmt) {
            $errors[] = "Prepare failed for fetch: " . htmlspecialchars($conn->error);
            error_log("Fetch Prepare Error: " . $conn->error);
        } else {
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                $errors[] = "Execute failed for fetch: " . htmlspecialchars($stmt->error);
                error_log("Fetch Execute Error: " . $stmt->error);
            } else {
                $result = $stmt->get_result();
                $enroll = $result->fetch_assoc();
                if (!$enroll) {
                    $errors[] = "Enrollment record not found.";
                }
            }
            $stmt->close();
        }

        if (empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST" && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            // Sanitize inputs
            $studentId = trim(htmlspecialchars($_POST['Student_Id'] ?? ''));
            $courseId = trim(htmlspecialchars($_POST['Course_Id'] ?? ''));
            $enrollDate = trim($_POST['Enrollment_Date'] ?? '');
            $status = trim(htmlspecialchars($_POST['Status'] ?? ''));

            // Validation
            $errors = [];
            if (empty($studentId)) $errors[] = "Student is required.";
            if (empty($courseId)) $errors[] = "Course is required.";
            if (empty($status) || !in_array($status, ['Active', 'Completed', 'Dropped'])) $errors[] = "Valid Status required.";
            $dateObj = DateTime::createFromFormat('Y-m-d', $enrollDate);
            if (!$dateObj || $dateObj > new DateTime()) $errors[] = "Valid past/today date required.";

            if (empty($errors)) {
                $enrollDateFormatted = $dateObj->format('Y-m-d');
                // UPDATE query with prepare check
                $updateStmt = $conn->prepare("UPDATE `enrollment` SET Student_Id=?, Course_Id=?, Enrollment_Date=?, Status=? WHERE Enroll_Id=?");
                if (!$updateStmt) {
                    $errors[] = "Prepare failed for update: " . htmlspecialchars($conn->error);
                    error_log("Update Prepare Error: " . $conn->error);
                } else {
                    $updateStmt->bind_param("ssssi", $studentId, $courseId, $enrollDateFormatted, $status, $id);
                    if (!$updateStmt->execute()) {
                        $errors[] = "Execute failed for update: " . htmlspecialchars($updateStmt->error);
                        error_log("Update Execute Error: " . $updateStmt->error);
                    } else {
                        $message = "Enrollment updated successfully.";
                        $success = true;
                        header("Location: VEnrollment.php?success=updated");
                        exit;
                    }
                    $updateStmt->close();
                }
            }
        } else {
            // Generate CSRF
            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
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
    <title>Edit Enrollment</title>
    <link rel="stylesheet" href="style.css">
    <style> .error { color: red; } .success { color: green; } </style>
</head>
<body>

<div class="header">
    <!-- Navbar as before -->
</div>

<div class="content">
    <center><br><br>
        <h2>Edit Enrollment ID: <?php echo $id; ?></h2>
        <?php if ($message): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
            <a href="VEnrollment.php"><button>Back to List</button></a>
        <?php endif; ?>

        <?php if ($enroll && empty($errors)): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Student ID:</label><br>
                <input type="text" name="Student_Id" value="<?php echo htmlspecialchars($enroll['Student_Id']); ?>" required><br><br>

                <label>Course ID:</label><br>
                <select name="Course_Id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courseList as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['Course_Id']); ?>" <?php echo ($enroll['Course_Id'] === $course['Course_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['Title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Enrollment Date:</label><br>
                <input type="date" name="Enroll_Date" value="<?php echo htmlspecialchars($enroll['Enroll_Date']); ?>" max="<?php echo date('Y-m-d'); ?>" required><br><br>

                <label>Status:</label><br>
                <select name="Status" required>
                    <option value="Active" <?php echo ($enroll['Status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                    <option value="Completed" <?php echo ($enroll['Status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                    <option value="Dropped" <?php echo ($enroll['Status'] === 'Dropped') ? 'selected' : ''; ?>>Dropped</option>
                </select><br><br>

                <button type="submit">Update Enrollment</button>
                <a href="VEnrollment.php"><button type="button">Cancel</button></a>
            </form>
        <?php endif; ?>
    </center>
</div>

<div class="footer">
    <!-- Footer -->
</div>

</body>
</html>