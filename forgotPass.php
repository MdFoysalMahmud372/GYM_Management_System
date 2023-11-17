<?php
session_start();

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('include/config.php');

function sendVerificationCode($email, $verificationCode) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'exampilot.nstu@gmail.com'; // Gmail email address
        $mail->Password = 'flgkqmehikxknweq'; // Gmail password or app-specific password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('exampilot.nstu@gmail.com', 'FlexFit');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Verification Code';
        $mail->Body = "Your verification code is: $verificationCode";

        $mail->send();
        return true; // Email sent successfully
    } catch (Exception $e) {
        return false; // Email sending failed
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit;
    }

       // Check if the email exists in the tbladmin table
    $queryAdmin = "SELECT id FROM tbladmin WHERE email = :email";
    $stmtAdmin = $dbh->prepare($queryAdmin);
    $stmtAdmin->bindParam(':email', $email, PDO::PARAM_STR);
    $stmtAdmin->execute();
    $rowAdmin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

    // Check if the email exists in the tbluser table
    $queryUser = "SELECT id FROM tbluser WHERE email = :email";
    $stmtUser = $dbh->prepare($queryUser);
    $stmtUser->bindParam(':email', $email, PDO::PARAM_STR);
    $stmtUser->execute();
    $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($rowAdmin || $rowUser) {
        // Email exists in either tbladmin or tbluser
        // echo 'exists';
    $verificationCode = generateVerificationCode();
    $_SESSION["verification_code"] = $verificationCode;
    $_SESSION["user_email"] = $email;

    if (sendVerificationCode($email, $verificationCode)) {
       // Store a success flag in the session to indicate successful code send
            $_SESSION["verification_code_success"] = true;
            // Email sent successfully, redirect to submitCode.php
            header("Location: submitCode.php");
            exit; // Ensure that no further code execution occurs
    } else {
        echo "failure"; // Failed to send the verification code
    }
    } else {
        // Email doesn't exist in either table
        echo 'Email not found in our records.';
    }

 
}

// $conn->close();

function generateVerificationCode() {
    return rand(1000, 9999);
}
?>





<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Main CSS-->
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <!-- Font-icon css-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>FlexFit</title>
  </head>
  <body>
    <section class="material-half-bg">
      <div class="cover"></div>
    </section>
    <section class="login-content">
      <div class="logo">
        <h1>FlexFit</h1>
      </div>
      <div class="login-box">
        <form class="login-form" method="post" action="#">
            <!-- Add the form elements for the "Forgot Password" form here -->
            <h3 class="login-head"><i class="fa fa-lg fa-fw fa-lock"></i>Forgot Password ?</h3>
            <div class="form-group">
                <label class="control-label">EMAIL</label>
                <input class="form-control" type="text" name="email" id="email1" placeholder="Email" required>
            </div>
            <div class="form-group btn-container">
                <button class="btn btn-primary btn-block" id="getCodeBtn" type="submit"><i class="fa fa-unlock fa-lg fa-fw"></i>GET CODE</button>
            </div>
            <div class="form-group mt-3">
                <p class="semibold-text mb-0"><a href="login.php" data-toggle="flip"><i class="fa fa-angle-left fa-fw"></i> Back to Login</a></p>
            </div>
        </form>
      </div>
    </section>
    <!-- Essential javascripts for application to work-->
    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
    <!-- The javascript plugin to display page loading on top-->
    <script src="js/plugins/pace.min.js"></script>
    <!-- <script type="text/javascript">
      // Login Page Flipbox control
      $('.login-content [data-toggle="flip"]').click(function() {
      	$('.login-box').toggleClass('flipped');
      	return false;
      });
    </script> -->
  </body>
</html>
