<?php
// Database connection details
$servername = "localhost"; // Change this to your MySQL server name if different
$username = "root"; // Change this to your MySQL username
$password = ""; // Change this to your MySQL password
$dbname = "oea"; // Change this to your MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
$reg_no = $_SESSION['reg_no'];


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

}
        

// Check if there are any results
if ($result_courses->num_rows > 0) {
    $courses = array();

    // Fetch each row as an associative array
    while ($row = $result_courses->fetch_assoc()) {
        $courses[] = $row;
    }
  
    // Output data as JSON
    echo json_encode($courses);
} 

else {
    echo "No courses found";
}

// Close connection
$conn->close();
?>
