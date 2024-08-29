<?php
// Include your database connection code here

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "oea";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming you have stored the registration number in a session variable
// Retrieve the registration number from the session
session_start();
$reg_no = $_SESSION['reg_no'];

// Perform SQL query to fetch branch name based on registration number
$query_branch = "SELECT stud_branch FROM stud_details WHERE reg_no = '$reg_no'";
$result_branch = $conn->query($query_branch);

// Check if there is a result for branch
if ($result_branch->num_rows > 0) {
    // Fetch the branch name
    $row_branch = $result_branch->fetch_assoc();
    $branch_name = $row_branch['stud_branch'];

    // Perform SQL query to fetch courses based on branch name
    $query_courses = "SELECT * FROM course_details WHERE FIND_IN_SET('$branch_name', open_to) > 0";
    $result_courses = $conn->query($query_courses);

    // Check if there are courses available
    if ($result_courses->num_rows > 0) {
        echo '<label for="course">Select Course:</label>';
        echo '<select name="course" id="course">';
        
        // Output options for each course
        while ($row_course = $result_courses->fetch_assoc()) {
            echo '<option value="' . $row_course['course_name'] . '">' . $row_course['course_name'] . '</option>';
        }
        
        echo '</select>';
    } else {
        // No courses available for the branch
        echo 'No courses available for this branch.';
    }
} else {
    // No result found for the registration number
    echo 'Branch not found.';
}

// Close the database connection
$conn->close();
?>
