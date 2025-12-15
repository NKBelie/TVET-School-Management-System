<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
	exit;
}

require 'db.php'; // DB connection

$message = "";

$instructor = $conn->query("SELECT Instructor_Id, Full_Name FROM instructor");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	// Sanitize and validate input values
	$id        = htmlspecialchars($_POST['Id']);
	$instid    = htmlspecialchars($_POST['InstId'] ?? '');
	$title     = htmlspecialchars($_POST['Title']);
	$desc      = htmlspecialchars($_POST['Description']);
	$duration  = intval($_POST['Duration']);
	$startDate = $_POST['date'];

	// Secure prepared query
	$stmt = $conn->prepare("INSERT INTO course (Course_Id, Instructor_Id, Title, Description, Duration, Start_Date) VALUES (?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssssis", $id, $instid, $title, $desc, $duration, $startDate);

	if ($stmt->execute()) {
		$message = "<span style='color:green;'>Course Registered Successfully</span>";
	} else {
		$message = "<span style='color:red;'>Error: Course Already Exists or Query Failed</span>";
	}

	$stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Register Course</title>
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
		<h2>Register Course</h2>

		<!-- Display message -->
		<?php if ($message != "") { echo "<p>$message</p>"; } ?>

		<form method="POST" action="">

			<label>Course ID:</label><br>
			<input type="text" name="Id" required><br><br>

			<label>Select Instructor:</label><br>
	            <select name="InstId" required>
		          <option value="">-- Select Instructor --</option>
		            <?php while($row = $instructor->fetch_assoc()): ?>
			      <option value="<?= $row['Instructor_Id']; ?>"><?= $row['Instructor_Id']; ?>||<?= $row['Full_Name']; ?></option>
		             <?php endwhile; ?>
	            </select><br><br>

			<label>Course Title:</label><br>
			<input type="text" name="Title" required><br><br>

			<label>Description:</label><br>
			<textarea name="Description" placeholder="Short description about the course" rows="3" required></textarea><br><br>

			<label>Duration (Weeks):</label><br>
			<input type="number" name="Duration" required><br><br>

			<label>Start Date:</label><br>
			<input type="date" name="date" required><br><br>

			<button type="submit" name="submit">Register</button>
		</form>
	</center>
</div>

<div class="footer">
	<hr>
	<center>&copy; TVET School Management System</center>
	<hr>
</div>

</body>
</html>
