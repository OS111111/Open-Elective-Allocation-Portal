<?php
// Your database connection code
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the course number to delete
    $courseNoToDelete = $_POST['course_no'];

    // Check if the course exists
    $checkCourseQuery = "SELECT * FROM course_details WHERE course_no = '$courseNoToDelete'";
    $checkCourseResult = $conn->query($checkCourseQuery);

    if ($checkCourseResult->num_rows > 0) {
        // The course exists, proceed with deletion
        $deleteQuery = "DELETE FROM course_details WHERE course_no = '$courseNoToDelete'"; // Adjust table name if needed
        $result = $conn->query($deleteQuery);

        if ($result) {
            // Deletion successful
            echo "suuccessfully removed $courseNoToDelete ";
        } else {
            // Deletion failed
            echo " FAILED to  remove $courseNoToDelete ";
        }
    } else {
        // Course doesn't exist
        echo "$courseNoToDelete does not exist in database";
    }
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Close database connection
$conn->close();
?>
