<?php
// Start the session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if registration number is stored in the session
if (!isset($_SESSION['reg_no'])) {
    echo json_encode(['course_alloted' => 'No Course']);
    exit;
}

// Retrieve the registration number from the session
$reg_no = $_SESSION['reg_no'];

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "oea";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Perform SQL query to fetch course_alloted based on registration number
$query = "SELECT course_alloted FROM sub_allocation WHERE reg_no = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $reg_no);
$stmt->execute();
$result = $stmt->get_result();

// Check if there is a result
if ($result->num_rows > 0) {
    // Fetch the course allocated
    $row = $result->fetch_assoc();
    $course_alloted = $row['course_alloted'];

    echo json_encode(['course_alloted' => $course_alloted ?: 'No Course']);
} else {
    echo json_encode(['course_alloted' => 'No Course']);
}

// Close the database connection
$conn->close();
?>
