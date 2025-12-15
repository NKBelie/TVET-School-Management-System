<?php
session_start();
if (!isset($_SESSION['user'])) {
	header("location: login.php");
	exit;
}

require 'db.php';

$result = null;
if (isset($_POST['search'])) {
	$id = intval($_POST['student']);
	$result = $conn->query("
		SELECT student.Full_Name, course.Course_Id, course.Title, attendance.Status AS AttendStatus, grade.Score, grade.Grade_Letter
		FROM enrollment
		JOIN student ON enrollment.Student_Id = student.Student_Id
		JOIN course ON enrollment.Course_Id = course.Course_Id
		LEFT JOIN attendance ON attendance.Student_Id = student.Student_Id AND attendance.Course_Id = course.Course_Id
		LEFT JOIN grade ON grade.Student_Id = student.Student_Id AND grade.Course_Id = course.Course_Id
		WHERE student.Student_Id = $id");
}

$students = $conn->query("SELECT * FROM student");
?>
<!DOCTYPE html>
<html>
<head>
<title>Single Student Report</title>
<link rel="stylesheet" href="style.css">
<style>
table { width: 85%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid black; padding:6px; text-align:center; }
th { background:#4CAF50; color:white; }
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
<h2>Student Performance Report</h2>

<form method="POST">
<select name="student" required>
	<option value="">-- Select Student --</option>
	<?php while($s = $students->fetch_assoc()): ?>
	<option value="<?= $s['Student_Id']; ?>"><?= $s['Full_Name']; ?></option>
	<?php endwhile; ?>
</select>
<button type="submit" name="search">Generate</button>
</form>

<?php if ($result && $result->num_rows > 0): ?>
<table>
	<tr>
		<th>Course ID</th>
		<th>Title</th>
		<th>Attendance</th>
		<th>Score</th>
		<th>Grade</th>
	</tr>
	<?php while($row = $result->fetch_assoc()): ?>
	<tr>
		<td><?= $row['Course_Id'] ?></td>
		<td><?= $row['Title'] ?></td>
		<td><?= $row['AttendStatus'] ?: "No Record" ?></td>
		<td><?= $row['Score'] ?: "-" ?></td>
		<td><b><?= $row['Grade_Letter'] ?: "-" ?></b></td>
	</tr>
	<?php endwhile; ?>
</table>
<?php elseif($result): ?>
<p style="color:red;">No data found.</p>
<?php endif; ?>
</center>
</div>
<br>
<div class="footer"><hr><center>&copy; TVET School Management System</center><hr></div>

</body>
</html>
