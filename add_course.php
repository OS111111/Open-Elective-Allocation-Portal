<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "oea";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form has been submitted
if (isset($_POST['add'])) {

    // Get the form data
    $course_name = $_POST['course_name'];
    $course_no = $_POST['course_no'];
    $student_cap = $_POST['student_cap'];
    $course_sch = $_POST['course_sch'];
    $open_to = isset($_POST['open_to']) ? implode(",", $_POST['open_to']) : '';

    // Insert the data into the database
    $sql = "INSERT INTO course_details (course_name, course_no, student_cap, course_sch, open_to) VALUES ('$course_name', '$course_no', '$student_cap', '$course_sch', '$open_to')";
    if ($conn->query($sql) === TRUE) {
        echo "Course added successful";
        echo "<br> <a href = 'courses.html'>Go back </a>  ";
        // echo "<br> <a href = 'index.html'>go home</a>  "
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
