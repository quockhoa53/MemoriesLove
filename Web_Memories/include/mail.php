<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

// Khởi tạo đối tượng PHPMailer
$mail = new PHPMailer(true);

// try {
    // Cấu hình máy chủ SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Máy chủ SMTP của Gmail
    $mail->SMTPAuth = true;
    $mail->Username = 'nguyenquockhoa5549@gmail.com'; // Địa chỉ email Gmail của bạn
    $mail->Password = 'rums kdhs bvku eqqg';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->setFrom('nguyenquockhoa5549@gmail.com', 'Nguyen Quoc Khoa');
?>
