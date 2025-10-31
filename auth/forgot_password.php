<?php
include '../config/conn_db.php';
include '../config/email_config.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if (isset($_POST['reset'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $sql = "SELECT * FROM users WHERE email='$email' AND status='ACTIVE'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $token = bin2hex(random_bytes(16));
        $update = "UPDATE users SET reset_token='$token' WHERE email='$email'";
        
        if (mysqli_query($conn, $update)) {
            $link = "http://localhost/USERMGMT/auth/reset_password.php?token=$token";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = 'tls';
                $mail->Port = SMTP_PORT;

                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Reset Password - Sistem Gudang';
                $mail->Body = "
                    <h3>Hai!</h3>
                    <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
                    <p>Klik tautan berikut untuk membuat password baru:</p>
                    <a href='$link'>$link</a>
                    <br><br>
                    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>
                    <p>Salam,<br><b>Sistem Gudang</b></p>
                ";

                $mail->send();
                $message = "<span style='color:green;'>Link reset password telah dikirim ke email Anda.</span>";
            } catch (Exception $e) {
                $message = "Gagal mengirim email. Error: {$mail->ErrorInfo}";
            }
        }
    } else {
        $message = "Email tidak terdaftar atau akun belum aktif.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-container {
            width: 380px;
            background: #ffffffcc;
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        h3 {
            text-align: center;
            color: #4a4a4a;
            margin-bottom: 10px;
        }
        p {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }
        label {
            font-weight: 500;
            color: #444;
            font-size: 14px;
        }
        input[type=email] {
            width: 100%;
            padding: 12px;
            margin: 8px 0 18px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            outline: none;
            transition: 0.3s;
        }
        input[type=email]:focus {
            border-color: #a18cd1;
            box-shadow: 0 0 6px rgba(161, 140, 209, 0.5);
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #a18cd1, #fbc2eb);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            opacity: 0.85;
            transform: translateY(-1px);
        }
        .msg {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
            color: #333;
        }
        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }
        .links a {
            color: #a18cd1;
            text-decoration: none;
            transition: 0.3s;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>🔑 Reset Password</h3>
        <p>Masukkan email kamu untuk menerima link reset password</p>
        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="contoh@email.com" required>
            <button type="submit" name="reset">Kirim Link Reset</button>
        </form>
        <div class="msg"><?= $message ?></div>
        <div class="links">
            <a href="login.php">← Kembali ke Login</a>
        </div>
    </div>
</body>
</html>
