<?php
session_start(); // Start the session

// Check if the student is logged in
// if (!isset($_SESSION['reg_no'])) {
//     // Redirect to login page if not logged in
//     header("Location: login.php");
//     exit();
// }

// Connect to your database
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

// Fetch student details from the database
$reg_no = $_SESSION['reg_no'];
$sql = "SELECT reg_no, stud_name, stud_branch FROM stud_details WHERE reg_no = '$reg_no'";
$result = $conn->query($sql);

$response = array();

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        $response['reg_no'] = $row["reg_no"];
        $response['stud_name'] = $row["stud_name"];
        $response['stud_branch'] = $row["stud_branch"];
    }
} else {
    // If no results found, handle it here
    $response['reg_no'] = "N/A";
    $response['stud_name'] = "N/A";
    $response['stud_branch'] = "N/A";
}

$conn->close(); // Close the database connection

// Output student details
header('Content-Type: application/json');
echo json_encode($response);
?>
