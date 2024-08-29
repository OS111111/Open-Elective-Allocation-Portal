<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload file
require __DIR__ . '/vendor/autoload.php';

session_start();

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

// Function to sanitize user inputs
function sanitizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Function to generate a random verification code
function generateVerificationCode() {
    return rand(100000, 999999); // You can customize the range or method as needed
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize user inputs
    $reg_no = $_SESSION['reg_no'];
    $email = sanitizeInput($_POST["email"]);
    $password = $_POST["password"]; // Hash the password

    // Check if email or reg_no already exists
    $checkQuery = "SELECT * FROM signup_details WHERE email = '$email' OR reg_no = '$reg_no'";
    $checkResult = $conn->query($checkQuery);

    if ($checkResult->num_rows > 0) {
        echo "Email or Registration Number already exists. Please choose different credentials.";
    } else {
        // Generate verification code
        $verificationCode = generateVerificationCode();

        // Insert user data into the database with verification code
        $sql = "INSERT INTO signup_details(reg_no, email, password, `allow`, `change`) VALUES ('$reg_no', '$email', '$password', '0', '1')";

        if ($conn->query($sql) === TRUE) {
            echo "User registered successfully";
            echo "<br> <a href='login.html'>go to login page</a>  ";

            // Send verification email using Gmail SMTP
            $subject = "Verify Your Email";
            $message = "Thank you for registering with us! for STUDENT OPEN ELECTIVE PORTAL";

            // Gmail SMTP settings
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            $mail->Username = 'pratminifelix@gmail.com'; // Your Gmail address
            $mail->Password = 'jlvq ahkx yizx biiy'; // Your Gmail password

            $mail->setFrom('pratminifelix@gmail.com', 'OE MINOR PROJECT');
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Enable verbose debug output
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;

            // Send email and check for errors
            try {
                $mail->send();
                echo "<script>alert('successfully! registered as user ');window.location.href = 'login.html';</script>";
            } catch (Exception $e) {
                echo "Error sending verification email: " . $mail->ErrorInfo;
            }
        } else {
            echo "Error registering user: " . $conn->error;
        }
    }
}

// Close connection
$conn->close();
?>
