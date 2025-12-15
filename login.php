<?php
session_start();
require 'db.php'; // DB connection file

$message = "";

// If already logged in, redirect to index
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Input sanitization
    $LoginField = trim($_POST['LoginField'] ?? ''); // Username or Email
    $Password = trim($_POST['Password'] ?? ''); // Plain text for comparison

    // Validate inputs
    $errors = [];
    if (empty($LoginField)) $errors[] = "Username or Email is required.";
    if (empty($Password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        // Fetch user by Username OR Email (no 'id' needed)
        $stmt = $conn->prepare("SELECT Username, Password FROM user WHERE Username = ? OR Email = ?");
        if (!$stmt) {
            $message = "<span style='color:red;'>Login query prepare failed: " . htmlspecialchars($conn->error) . "</span>";
        } else {
            $stmt->bind_param("ss", $LoginField, $LoginField);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($user = $result->fetch_assoc()) {
                // Verify password (plain text match - testing only!)
                if ($Password === $user['Password']) {
                    // Set session (use Username as key)
                    $_SESSION['user'] = [
                        'username' => $user['Username'],
                    ];
                    // Redirect to index
                    header("Location: index.php");
                    exit;
                } else {
                    $message = "<span style='color:red;'>Invalid Username/Email or Password.</span>";
                }
            } else {
                $message = "<span style='color:red;'>Invalid Username/Email or Password.</span>";
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TVET School Management System - Login</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="header">
        <!-- Header content if needed -->
    </div>
    <div class="content">
        <center>
            <h2>TVET Schools Login</h2>

            <!-- Display message -->
            <?php if ($message != "") { echo "<p>$message</p>"; } ?>

            <form method="POST" action="login.php">
                <label>Username or Email:</label><br>
                <input type="text" name="LoginField" value="<?php echo htmlspecialchars($_POST['LoginField'] ?? ''); ?>" required><br><br>

                <label>Password:</label><br>
                <input type="password" name="Password" required><br><br>

                <button type="submit" name="submit">Login</button>
            </form>

            <p><a href="signup.php">Don't have an account? Sign Up</a></p>
        </center>
    </div>
    <div class="footer">
        <hr>
        <center>&copy TVET School Management System</center>
        <hr>
    </div>
</body>
</html>