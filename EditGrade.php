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
$grade = null;

// Quick connection check
if (!isset($conn) || !$conn || $conn->connect_error) {
    $message = "Database connection failed: " . htmlspecialchars($conn->connect_error ?? 'Unknown error');
    error_log("DB Connect Error in EditGrade: " . ($conn->connect_error ?? 'Unknown'));
} else {
    // Fetch students and courses for dropdowns
    $studentList = [];
    $courseList = [];
    $studentsQuery = $conn->query("SELECT Student_Id, Full_Name FROM `student` WHERE Status='Active' ORDER BY Full_Name");
    if ($studentsQuery) {
        while ($row = $studentsQuery->fetch_assoc()) {
            $studentList[] = $row;
        }
        $studentsQuery->free();
    } else {
        $errors[] = "Failed to fetch students: " . htmlspecialchars($conn->error);
        error_log("Students Query Error: " . $conn->error);
    }

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

    // Helper to calculate grade
    function calculateGrade($score) {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    if ($id <= 0) {
        $errors[] = "Invalid ID.";
    } else {
        // Fetch existing grade
        $stmt = $conn->prepare("SELECT * FROM `grade` WHERE Grade_Id = ?");
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
                $grade = $result->fetch_assoc();
                if (!$grade) {
                    $errors[] = "Grade record not found.";
                }
            }
            $stmt->close();
        }

        if (empty($errors) && $_SERVER["REQUEST_METHOD"] == "POST" && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            // Sanitize inputs
            $studentId = trim(htmlspecialchars($_POST['Student_Id'] ?? ''));
            $courseId = trim(htmlspecialchars($_POST['Course_Id'] ?? ''));
            $scoreInput = trim($_POST['Score'] ?? '');
            $gDate = trim($_POST['Gdate'] ?? '');

            // Validation
            $errors = [];
            if (empty($studentId)) $errors[] = "Student is required.";
            if (empty($courseId)) $errors[] = "Course is required.";
            $score = floatval($scoreInput);
            if ($score < 0 || $score > 100) $errors[] = "Score must be 0-100.";
            $dateObj = DateTime::createFromFormat('Y-m-d', $gDate);
            if (!$dateObj || $dateObj > new DateTime()) $errors[] = "Valid past/today Grade Date required.";

            if (empty($errors)) {
                $gDateFormatted = $dateObj->format('Y-m-d');
                $newGrade = calculateGrade($score); // Re-calc
                // UPDATE query with prepare check
                $updateStmt = $conn->prepare("UPDATE `grade` SET Student_Id=?, Course_Id=?, Score=?, Grade=?, Gdate=? WHERE Grade_Id=?");
                if (!$updateStmt) {
                    $errors[] = "Prepare failed for update: " . htmlspecialchars($conn->error);
                    error_log("Update Prepare Error: " . $conn->error);
                } else {
                    $updateStmt->bind_param("ssdssi", $studentId, $courseId, $score, $newGrade, $gDateFormatted, $id);
                    if (!$updateStmt->execute()) {
                        $errors[] = "Execute failed for update: " . htmlspecialchars($updateStmt->error);
                        error_log("Update Execute Error: " . $updateStmt->error);
                    } else {
                        $message = "Grade updated successfully (re-calculated: $newGrade).";
                        $success = true;
                        header("Location: VGrade.php?success=updated");
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
    <title>Edit Grade</title>
    <link rel="stylesheet" href="style.css">
    <style> .error { color: red; } .success { color: green; } </style>
</head>
<body>

<div class="header">
    <!-- Navbar as before -->
</div>

<div class="content">
    <center><br><br>
        <h2>Edit Grade ID: <?php echo $id; ?></h2>
        <?php if ($message): ?>
            <p class="<?php echo $success ? 'success' : 'error'; ?>"><?php echo $message; ?></p>
            <a href="VGrade.php"><button>Back to List</button></a>
        <?php endif; ?>

        <?php if ($grade && empty($errors)): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label>Student ID:</label><br>
                <select name="Student_Id" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($studentList as $student): ?>
                        <option value="<?php echo htmlspecialchars($student['Student_Id']); ?>" <?php echo ($grade['Student_Id'] === $student['Student_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($student['Full_Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Course ID:</label><br>
                <select name="Course_Id" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courseList as $course): ?>
                        <option value="<?php echo htmlspecialchars($course['Course_Id']); ?>" <?php echo ($grade['Course_Id'] === $course['Course_Id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['Title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br><br>

                <label>Score (0-100):</label><br>
                <input type="number" name="Score" min="0" max="100" step="0.1" value="<?php echo htmlspecialchars($grade['Score']); ?>" required><br><br>

                <label>Grade Date:</label><br>
                <input type="date" name="Gdate" value="<?php echo htmlspecialchars($grade['Gdate']); ?>" max="<?php echo date('Y-m-d'); ?>" required><br><br>

                <p><small>Grade will be auto-recalculated from Score (e.g., 85 = B).</small></p>

                <button type="submit">Update Grade</button>
                <a href="VGrade.php"><button type="button">Cancel</button></a>
            </form>
        <?php endif; ?>
    </center>
</div>

<div class="footer">
    <!-- Footer -->
</div>

</body>
</html>