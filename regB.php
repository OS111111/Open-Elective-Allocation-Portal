<?php
  session_start();
  $servername = 'localhost';
  $user = 'root';
  $password = 'Omkar@123';
  $database = 'oea';
  $conn = mysqli_connect($servername, $user, $password, $database);
  if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
  }
  $reg_no = $_POST['reg_no'];
  

  $sql = "SELECT * FROM stud_details WHERE reg_no='$reg_no'";

  $result = mysqli_query($conn, $sql);
  if (mysqli_num_rows($result) > 0) {
    $_SESSION['reg_no'] = $reg_no;
    header('Location: signup.html');
  } else {
    echo 'Invalid username or password';
    echo "<br> <a href = 'login.html'>Try Again!</a>  ";
  }
  mysqli_close($conn);
?>
