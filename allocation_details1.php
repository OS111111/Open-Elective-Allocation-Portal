<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "oea";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to select all records from sub_allocation table
$sql = "SELECT * FROM sub_allocation order by course_alloted";

// Execute query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $courses = array();

    // Fetch each row as an associative array
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
  
    // Output data as JSON
    echo json_encode($courses);
} 


// Close connection
$conn->close();
?>
