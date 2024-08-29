<?php
session_start();
$servername = 'localhost';
$user = 'root';
$password = '';
$database = 'oea';
$conn = mysqli_connect($servername, $user, $password, $database);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM signup_details WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    // If login is successful, retrieve reg_no and store it in session variable
    $row = mysqli_fetch_assoc($result);
    $reg_no = $row['reg_no'];
    $_SESSION['reg_no'] = $reg_no;

    // Redirect to student homepage
    header('Location: student_homepage.html');
    exit(); // Ensure script execution stops after redirection
} else {
    echo 'Invalid username or password';
    echo "<br> <a href='login.html'>Try Again!</a>";
}

mysqli_close($conn);
?>
