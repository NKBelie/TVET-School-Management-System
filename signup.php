<?php
session_start();
require 'db.php'; // DB connection file - ensure this returns a valid $conn

$message = "";

// Quick connection check
if (!isset($conn) || !$conn || $conn->connect_error) {
    $message = "<span style='color:red;'>Database connection failed: " . htmlspecialchars($conn->connect_error ?? 'Unknown error') . "</span>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message)) {
    // Input sanitization
    $FName = htmlspecialchars(trim($_POST['Fname'] ?? ''));
    $Gender = htmlspecialchars($_POST['Gender'] ?? '');
    $User = htmlspecialchars(trim($_POST['User'] ?? ''));
    $Email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $Password = trim($_POST['Password'] ?? ''); // Plain text - no hashing
    $ConfirmPassword = trim($_POST['ConfirmPassword'] ?? ''); // For matching check
    $Create = date('Y-m-d H:i:s'); // Current timestamp: 2025-12-09 HH:MM:SS

    // Validate inputs (password checks first)
    $errors = [];
    if (empty($FName)) $errors[] = "Full Name is required.";
    if (!in_array($Gender, ['Male', 'Female'])) $errors[] = "Valid Gender required.";
    if (empty($User)) $errors[] = "Username is required.";
    if (empty($Email) || !filter_var($Email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid Email Format.";
    if (empty($Password) || strlen($Password) < 6) $errors[] = "Password is required (min 6 characters).";
    if ($Password !== $ConfirmPassword) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // No hashing - use plain $Password (WARNING: Insecure for production!)
        $PlainPassword = $Password;

        // Check for existing user
        $checkStmt = $conn->prepare("SELECT COUNT(*) as exists_count FROM user WHERE Username = ? OR Email = ?");
        if (!$checkStmt) {
            $message = "<span style='color:red;'>Check query prepare failed: " . htmlspecialchars($conn->error) . ". Verify table 'user' exists and has 'Username'/'Email' columns.</span>";
        } else {
            $checkStmt->bind_param("ss", $User, $Email);
            if (!$checkStmt->execute()) {
                $message = "<span style='color:red;'>Check query execute failed: " . htmlspecialchars($checkStmt->error) . "</span>";
            } else {
                $checkResult = $checkStmt->get_result();
                $rowCount = $checkResult->fetch_assoc()['exists_count'];
                if ($rowCount > 0) {
                    $message = "<span style='color:red;'>Error: User Already Exists (Username or Email taken).</span>";
                } else {
                    // Secure prepared INSERT (plain password - testing only!)
                    $stmt = $conn->prepare("INSERT INTO user (Full_Name, Gender, Username, Email, Created_At, Password) VALUES (?, ?, ?, ?, ?, ? )");
                    if (!$stmt) {
                        $message = "<span style='color:red;'>INSERT prepare failed: " . htmlspecialchars($conn->error) . ". Check if columns 'Full_Name',Gender, 'Username', 'Email', 'Created_At','Password' exist in 'user' table.</span>";
                    } else {
                        $stmt->bind_param("ssssss", $FName, $Gender, $User, $Email, $Create, $PlainPassword);
                        if ($stmt->execute()) {
                            $message = "<span style='color:green;'>SignUp Successful!</span>";
                            $_POST = []; // Fully reset form
                        } else {
                            $message = "<span style='color:red;'>INSERT execute failed: " . htmlspecialchars($stmt->error) . ". Possible duplicate, constraint, or missing column.</span>";
                        }
                        $stmt->close();
                    }
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TVET School Management System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="header">
        <!-- Header content if needed -->
    </div>
    <div class="content">
        <center>
            <h2>TVET Schools SignUp</h2>

            <!-- Display message -->
            <?php if ($message != "") { echo "<p>$message</p>"; } ?>

            <form method="POST" action="signup.php">
                <label>Full Name:</label><br>
                <input type="text" name="Fname" value="<?php echo htmlspecialchars($_POST['Fname'] ?? ''); ?>" required><br><br>

                <label>Gender:</label><br>
                <select name="Gender" required>
                    <option value="">Choose</option>
                    <option value="Male" <?php echo ($_POST['Gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($_POST['Gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                </select><br><br>

                <label>Username:</label><br>
                <input type="text" name="User" value="<?php echo htmlspecialchars($_POST['User'] ?? ''); ?>" required><br><br>

                <label>Email:</label><br>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required><br><br>

                <label>Password:</label><br>
                <input type="password" name="Password" required><br><br>

                <label>Confirm Password:</label><br>
                <input type="password" name="ConfirmPassword" required><br><br>

                <button type="submit" name="submit">SignUp</button>
            </form>

            <p><a href="login.php">Already have an account? Login</a></p>
        </center>
    </div>
    <div class="footer">
        <hr>
        <center>&copy TVET School Management System</center>
        <hr>
    </div>
</body>
</html>