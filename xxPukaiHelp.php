<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = ""; // Your database password
$dbname = "oea"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if(isset($_POST['submit'])) {
    // Retrieve form data
    $stud_name = $_POST['stud_name'];
    $reg_no = $_POST['reg_no'];
    $stud_branch = $_POST['stud_branch'];

    // Prepare SQL statement to insert data into stud_details table
    $stmt = $conn->prepare("INSERT INTO stud_details (stud_name, reg_no, stud_branch) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $stud_name, $reg_no, $stud_branch);

    // Execute statement
    if ($stmt->execute()) {
        echo "Data inserted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Check if the "CHANGE Power to Student" button is clicked
if(isset($_POST['change'])) {
    // SQL to update all rows in the column called 'change' to set value = 1
    $update_sql = "UPDATE signup_details SET `change` = 1"; // Wrapped change with backticks

    if ($conn->query($update_sql) === TRUE) {
        echo "Records updated successfully";
    } else {
        echo "Error updating records: " . $conn->error;
    }
}

// Close connection
$conn->close();
?>
