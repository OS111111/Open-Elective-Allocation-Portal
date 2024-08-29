<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload file
require __DIR__ . '/vendor/autoload.php';

// Start session to retrieve logged-in user's registration number and name
session_start();

// Check if user is logged in
if (!isset($_SESSION['reg_no'])) {
    // Redirect user to login page if not logged in
    header("Location: login.php");
    exit;
}

// Retrieve logged-in user's registration number
$reg_no = $_SESSION['reg_no'];

// Database connection details
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

// Fetch student's name from signup_details based on reg_no
$stmt_fetch_name = $conn->prepare("SELECT stud_name FROM stud_details WHERE reg_no = ?");
$stmt_fetch_name->bind_param("s", $reg_no);
$stmt_fetch_name->execute();
$stmt_fetch_name->store_result();
$stmt_fetch_name->bind_result($stud_name);
$stmt_fetch_name->fetch();
$stmt_fetch_name->close();

// Check if "Change" button is clicked
if (isset($_POST['change'])) {
    // Update internal_cap and reset course allotment for the given registration number
    // Check if the value of 'change' for the given reg_no is 1
    $stmt_check_change = $conn->prepare("SELECT `change` FROM signup_details WHERE reg_no = ?");
    $stmt_check_change->bind_param("s", $reg_no);
    $stmt_check_change->execute();
    $stmt_check_change->store_result();
    $stmt_check_change->bind_result($change_value);
    $stmt_check_change->fetch();
    $stmt_check_change->close();

    if ($change_value != 1) {
        echo "<script>alert('You have already changed your course!');window.location.href = 'student_homepage.html';</script>";
        exit;
    }

    // First, update internal_cap
    $stmt_update_cap = $conn->prepare("UPDATE course_details SET internal_cap = internal_cap + 1 WHERE course_name IN (SELECT course_alloted FROM sub_allocation WHERE reg_no = ?)");
    $stmt_update_cap->bind_param("s", $reg_no);

    if ($stmt_update_cap->execute()) {
        // Now delete corresponding rows from sub_allocation
        $stmt_delete = $conn->prepare("DELETE FROM sub_allocation WHERE reg_no = ?");
        $stmt_delete->bind_param("s", $reg_no);

        if ($stmt_delete->execute()) {
            // Update change value to 0 in signup_details
            $stmt_update_change = $conn->prepare("UPDATE signup_details SET `change` = 0 WHERE reg_no = ?");
            $stmt_update_change->bind_param("s", $reg_no);

            if ($stmt_update_change->execute()) {
                // Send email notification for change application
                sendChangeEmailNotification($reg_no, $stud_name);

                echo "<script>alert('Changes applied successfully!');window.location.href = 'student_homepage.html';</script>";
            } else {
                echo "Error updating change value: " . $conn->error;
            }

            $stmt_update_change->close();
        } else {
            echo "Error deleting records: " . $conn->error;
        }

        $stmt_delete->close();
    } else {
        echo "Error updating internal_cap: " . $conn->error;
    }

    $stmt_update_cap->close();
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Retrieve selected course from form
    if (isset($_POST['course_alloted'])) {
        $course_alloted = $_POST['course_alloted'];

        // Check if student is already registered for the course
        $stmt_check_reg = $conn->prepare("SELECT reg_no FROM sub_allocation WHERE reg_no = ?");
        $stmt_check_reg->bind_param("s", $reg_no);
        $stmt_check_reg->execute();
        $stmt_check_reg->store_result();
        $num_rows = $stmt_check_reg->num_rows;
        $stmt_check_reg->close();

        if ($num_rows == 0) {
            // Check seat availability
            $stmt_check_cap = $conn->prepare("SELECT internal_cap FROM course_details WHERE course_name = ?");
            $stmt_check_cap->bind_param("s", $course_alloted);
            $stmt_check_cap->execute();
            $stmt_check_cap->store_result();
            $stmt_check_cap->bind_result($internal_cap);
            $stmt_check_cap->fetch();
            $stmt_check_cap->close();

            if ($internal_cap !== null && $internal_cap !== 0) {
                // Insert data into sub_allocation table
                $stmt = $conn->prepare("INSERT INTO sub_allocation (reg_no, stud_name, course_alloted) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $reg_no, $stud_name, $course_alloted);

                // Execute statement
                if ($stmt->execute()) {
                    // Send email notification for successful course registration
                    sendRegistrationEmailNotification($reg_no, $stud_name, $course_alloted);

                    echo "<script>alert('successfully! alloted you the subject');window.location.href = 'student_homepage.html';</script>";
                    
                    // Subtract 1 from internal_cap in course_details table
                    $stmt_update = $conn->prepare("UPDATE course_details SET internal_cap = internal_cap - 1 WHERE course_name = ?");
                    $stmt_update->bind_param("s", $course_alloted);
                    $stmt_update->execute();
                    $stmt_update->close(); // Close statement after executing update query
                } else {
                    echo "Error: " . $stmt->error;
                }

                // Close statement
                $stmt->close();
            } else {
                // Seats not available, display alert and exit
                echo "<script>alert('Seats for this course are not available!');window.location.href = 'student_homepage.html';</script>";
                exit();
            }
        } else {
            // Student already registered for the course, display alert and redirect
            echo "<script>alert('You have already registered for a course!');window.location.href = 'student_homepage.html';</script>";
            exit();
        }
    } else {
        echo "No course selected!";
    }
}

// Close connection
$conn->close();

// Function to send email notification for change application
function sendChangeEmailNotification($reg_no, $stud_name) {
    // Fetch student's email from the database based on registration number
    global $conn;
    
    $stmt_email = $conn->prepare("SELECT email FROM signup_details WHERE reg_no = ?");
    $stmt_email->bind_param("s", $reg_no);
    $stmt_email->execute();
    $stmt_email->store_result();
    $stmt_email->bind_result($email);
    $stmt_email->fetch();
    $stmt_email->close();

    // Check if email is fetched successfully
    if (!$email) {
        echo "Email not found!";
        return;
    }

    // Send email using PHPMailer
    $subject = "Change Application Successful";
    $message = "Dear Student,\n\nYour application for course change has been successfully processed.\n\nBest regards,\nYour OE TEAM";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = 'pratminifelix@gmail.com'; // Your Gmail address
    $mail->Password = 'jlvq ahkx yizx biiy'; // Your Gmail password

    $mail->setFrom('pratminifelix@gmail.com', 'OE Minor Project');
    $mail->addAddress($email);
    $mail->Subject = $subject;
    $mail->Body = $message;

    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    // Send email and check for errors
    try {
        $mail->send();
        echo "Email notification sent successfully!";
    } catch (Exception $e) {
        echo "Error sending email notification: " . $mail->ErrorInfo;
    }
}

// Function to send email notification for successful course registration
function sendRegistrationEmailNotification($reg_no, $stud_name, $course_alloted) {
    // Fetch student's email from the database based on registration number
    global $conn;
    
    $stmt_email = $conn->prepare("SELECT email FROM signup_details WHERE reg_no = ?");
    $stmt_email->bind_param("s", $reg_no);
    $stmt_email->execute();
    $stmt_email->store_result();
    $stmt_email->bind_result($email);
    $stmt_email->fetch();
    $stmt_email->close();

    // Check if email is fetched successfully
    if (!$email) {
        echo "Email not found!";
        return;
    }

    // Send email using PHPMailer
    $subject = "Course Registration Successful";
    $message = "Dear $stud_name,\n\nCongratulations! You have successfully registered for the course: $course_alloted.\n\nBest regards,\nYour OE Team";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 587;
    $mail->SMTPSecure = 'tls';
    $mail->SMTPAuth = true;
    $mail->Username = 'pratminifelix@gmail.com'; // Your Gmail address
    $mail->Password = 'jlvq ahkx yizx biiy'; // Your Gmail password

    $mail->setFrom('pratminifelix@gmail.com', 'OE Minor Project');
    $mail->addAddress($email);
    $mail->Subject = $subject;
    $mail->Body = $message;

    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    // Send email and check for errors
    try {
        $mail->send();
        echo "Email notification sent successfully!";
    } catch (Exception $e) {
        echo "Error sending email notification: " . $mail->ErrorInfo;
    }
}
?>
