<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit; }

require 'db.php';
$result = $conn->query("SELECT * FROM course ORDER BY Course_Id ASC");
?>
<!DOCTYPE html>
<html>
<head>
<title>View Courses</title>
<link rel="stylesheet" href="style.css">
<style>
table{width:90%;border-collapse:collapse;}th,td{border:1px solid black;padding:8px;text-align:center;}th{background:#4CAF50;color:white;}tr:nth-child(even){background:#f2f2f2;}
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
<h2>Registered Courses</h2>
<?php $successMsg = $_GET['success'] ?? '';
$errorMsg = $_GET['error'] ?? '';
if ($successMsg) echo "<p class='success'>$successMsg!</p>";
if ($errorMsg) echo "<p class='error'>$errorMsg</p>"; ?>
<?php if ($result->num_rows > 0): ?>
<table>
<tr><th>ID</th><th>Title</th><th>Description</th><th>Duration</th><th>Start Date</th><th>Actions</th></tr>

<?php while($row=$result->fetch_assoc()): ?>
<tr>
<td><?= $row['Course_Id']; ?></td>
<td><?= $row['Title']; ?></td>
<td><?= $row['Description']; ?></td>
<td><?= $row['Duration']; ?> weeks</td>
<td><?= $row['Start_Date']; ?></td>
<td>
    <a href="EditCourse.php?id=<?php echo urlencode($row['Course_Id']); ?>"><button>Edit</button></a>
    <a href="DeleteCourse.php?id=<?php echo urlencode($row['Course_Id']); ?>" onclick="return confirm('Are you sure?');"><button>Delete</button></a>
</td>
</tr>
<?php endwhile; ?>
</table>

<?php else: ?><p style="color:red;">No Courses Available</p><?php endif; ?>

</center></div>

<div class="footer"><hr><center>&copy; TVET School Management System</center><hr></div>
</body>
</html>
