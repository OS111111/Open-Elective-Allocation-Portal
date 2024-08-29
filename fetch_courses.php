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

// Fetch data from the database
$sql = "SELECT * FROM course_details order by   student_cap"; // Change 'courses' to your actual table name
$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    $courses = array();

    // Fetch each row as an associative array
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

    // Output data as JSON
    echo json_encode($courses);
} else {
    echo "No courses found";
}

// Close connection
$conn->close();
?>
