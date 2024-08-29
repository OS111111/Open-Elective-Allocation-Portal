<?php
// Start session to retrieve logged-in user's registration number
session_start();

// Check if user is logged in
if (!isset($_SESSION['reg_no'])) {
    // Redirect user to login page if not logged in
    header("Location: login.php");
    exit;
}

// Retrieve logged-in user's registration number
$reg_no = $_SESSION['reg_no'];

// Fetch student's name from database based on registration number
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

// Prepare SQL statement to fetch student's name
$stmt = $conn->prepare("SELECT stud_name FROM stud_details WHERE reg_no = ?");
$stmt->bind_param("s", $reg_no);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($stud_name);
$stmt->fetch();
$stmt->close();

// Check if student name is fetched successfully
if (!$stud_name) {
    echo "Student name not found!";
    exit;
}

// Check if "Change" button is clicked
if (isset($_POST['change'])) {
    // Check if there are any rows in stud_details where change = 1 for the given reg_no
    $stmt_check_change = $conn->prepare("SELECT COUNT(*) FROM signup_details  WHERE reg_no = ? AND `change` = 1");
    if ($stmt_check_change) {
        $stmt_check_change->bind_param("s", $reg_no);
        $stmt_check_change->execute();
        $stmt_check_change->store_result();
        $stmt_check_change->bind_result($change_count);
        $stmt_check_change->fetch();
        $stmt_check_change->close();

        // If there are rows with change = 1, delete corresponding rows from sub_allocation
        if ($change_count > 0) {
            $stmt_delete = $conn->prepare("DELETE FROM sub_allocation WHERE reg_no = ?");
            if ($stmt_delete) {
                $stmt_delete->bind_param("s", $reg_no);
                if ($stmt_delete->execute()) {
                    echo "<script>alert('Changes applied successfully!');window.location.href = 'student_sub_alloc.php';</script>";
                } else {
                    echo "Error deleting records: " . $conn->error;
                }
                $stmt_delete->close();
            } else {
                echo "Failed to prepare statement for deletion: " . $conn->error;
            }
        } else {
            echo "<script>alert('No changes to apply!');window.location.href = 'student_sub_alloc.php';</script>";
        }
    } else {
        echo "Failed to prepare statement for checking change: " . $conn->error;
    }
}


// Check if form is submitted
if (isset($_POST['submit'])) {
    // Retrieve selected course from form
    if (isset($_POST['course_alloted'])) {
        $course_alloted = $_POST['course_alloted'];

        // Check if student is already registered for the course
        $stmt_check_reg = $conn->prepare("SELECT reg_no FROM sub_allocation WHERE reg_no = ?");
        if ($stmt_check_reg) {
            $stmt_check_reg->bind_param("s", $reg_no);
            $stmt_check_reg->execute();
            $stmt_check_reg->store_result();
            $num_rows = $stmt_check_reg->num_rows;
            $stmt_check_reg->close();

            if ($num_rows == 0) {
                // Check seat availability
                $stmt_check_cap = $conn->prepare("SELECT internal_cap FROM course_details WHERE course_name = ?");
                if ($stmt_check_cap) {
                    $stmt_check_cap->bind_param("s", $course_alloted);
                    $stmt_check_cap->execute();
                    $stmt_check_cap->store_result();
                    $stmt_check_cap->bind_result($internal_cap);
                    $stmt_check_cap->fetch();
                    $stmt_check_cap->close();

                    if ($internal_cap !== null && $internal_cap !== 0) {
                        // Insert data into sub_allocation table
                        $stmt = $conn->prepare("INSERT INTO sub_allocation (reg_no, stud_name, course_alloted) VALUES (?, ?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("sss", $reg_no, $stud_name, $course_alloted);
                            if ($stmt->execute()) {
                                echo "<script>alert('Preferences submitted successfully!');window.location.href = 'student_homepage.html';</script>";

                                // Subtract 1 from internal_cap in course_details table
                                $stmt_update = $conn->prepare("UPDATE course_details SET internal_cap = internal_cap - 1 WHERE course_name = ?");
                                if ($stmt_update) {
                                    $stmt_update->bind_param("s", $course_alloted);
                                    $stmt_update->execute();
                                    $stmt_update->close(); // Close statement after executing update query
                                } else {
                                    echo "Error updating internal_cap: " . $conn->error;
                                }
                            } else {
                                echo "Error inserting record: " . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            echo "Failed to prepare statement for insertion: " . $conn->error;
                        }
                    } else {
                        // Seats not available, display alert and exit
                        echo "<script>alert('Seats for this course are not available!');window.location.href = 'student_homepage.html';</script>";
                        exit();
                    }
                } else {
                    echo "Failed to prepare statement for seat availability check: " . $conn->error;
                }
            } else {
                // Student already registered for the course, display alert and redirect
                echo "<script>alert('You have already registered for a course!');window.location.href = 'student_homepage.html';</script>";
                exit();
            }
        } else {
            echo "Failed to prepare statement for registration check: " . $conn->error;
        }
    } else {
        echo "No course selected!";
    }
}

// Close connection
$conn->close();
?>
