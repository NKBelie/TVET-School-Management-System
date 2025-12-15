<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
	exit;
}

require 'db.php';

$result = $conn->query("SELECT * FROM student ORDER BY Student_Id ASC");
?>
<!DOCTYPE html>
<html>
<head>
	<title>View Students</title>
	<link rel="stylesheet" href="style.css">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		table {
			width: 90%;
			border-collapse: collapse;
		}
		th, td {
			border: 1px solid black;
			padding: 8px;
			text-align: center;
		}
		th {
			background: #4CAF50;
			color: white;
		}
		tr:nth-child(even) {
			background: #f2f2f2;
		}
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
<center><br><br><br>
	<h2>Registered Students</h2>

	<?php if ($result->num_rows > 0): ?>

	<table>
		<tr>
			<th>Student ID</th>
			<th>Full Name</th>
			<th>Gender</th>
			<th>Email</th>
			<th>Phone</th>
			<th>Enrollment Date</th>
			<th>Status</th>
			<th>Actions</th>
		</tr>

		<?php while($row = $result->fetch_assoc()): ?>
		<tr>
			<td><?= $row['Student_Id']; ?></td>
			<td><?= $row['Full_Name']; ?></td>
			<td><?= $row['Gender']; ?></td>
			<td><?= $row['Email']; ?></td>
			<td><?= $row['Phone']; ?></td>
			<td><?= $row['Enrollment_Date']; ?></td>
			<td><?= $row['Status']; ?></td>
			<td>
				<button disabled>Edit</button>
				<button disabled>Delete</button>
			</td>
		</tr>
		<?php endwhile; ?>

	</table>

	<?php else: ?>
		<p style="color:red;">No Student Records Found</p>
	<?php endif; ?>

</center>
<br><br>
</div>

<div class="footer">
	<hr>
	<center>&copy; TVET School Management System</center>
	<hr>
</div>

</body>
</html>
