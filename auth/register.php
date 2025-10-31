<?php
include '../config/conn_db.php';
include '../config/email_config.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Variabel untuk menampung pesan ke user
$message = "";
$message_type = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validasi password
    if ($password !== $confirm) {
        $message = "Konfirmasi password tidak cocok!";
        $message_type = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password minimal 6 karakter!";
        $message_type = "error";
    } else {
        // Cek apakah email sudah digunakan
        $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (!$cek) {
            $message = "Terjadi kesalahan: " . mysqli_error($conn);
            $message_type = "error";
        } elseif (mysqli_num_rows($cek) > 0) {
            $message = "Email sudah terdaftar!";
            $message_type = "error";
        } else {
            // Simpan ke database
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(16));

            $sql = "INSERT INTO users (nama, email, password, activation_token, role, status)
                    VALUES ('$nama', '$email', '$hashed', '$token', 'Admin Gudang', 'PENDING')";

            if (mysqli_query($conn, $sql)) {
                $mail = new PHPMailer(true);

                try {
                    // Pengaturan server SMTP
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'refisulistiawati29@gmail.com';
                    $mail->Password   = 'xbzg zkxw anoj eojg';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';
                    $mail->SMTPDebug  = 0;
                    $mail->Debugoutput = function($str, $level) {
                        error_log("PHPMailer: $str");
                    };
                    $mail->Timeout = 30;
                    $mail->SMTPKeepAlive = true;
                    
                    // Pengaturan email
                    $mail->setFrom('refisulistiawati29@gmail.com', 'Admin Gudang');
                    $mail->addAddress($email, $nama);
                    $mail->addReplyTo('refisulistiawati29@gmail.com', 'Admin Gudang');
                    
                    // Konten email
                    $mail->isHTML(true);
                    $mail->Subject = 'Aktivasi Akun Admin Gudang';
                    
                    // Dapatkan URL base secara dinamis
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'];
                    $baseUrl = $protocol . "://" . $host . "/usermgmt/auth";
                    
                    $mail->Body = "
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset='UTF-8'>
                        </head>
                        <body style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                            <div style='max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px;'>
                                <h2 style='color: #667eea; text-align: center;'>Aktivasi Akun Admin Gudang</h2>
                                <p>Halo <strong>$nama</strong>,</p>
                                <p>Terima kasih telah mendaftar. Silakan klik tombol di bawah ini untuk mengaktifkan akun Anda:</p>
                                <div style='text-align: center; margin: 30px 0;'>
                                    <a href='$baseUrl/activate.php?token=$token' 
                                       style='background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                                        Aktivasi Akun
                                    </a>
                                </div>
                                <p style='color: #666; font-size: 14px;'>Atau copy link berikut ke browser Anda:</p>
                                <p style='background: #f0f0f0; padding: 10px; word-break: break-all; font-size: 12px;'>
                                    $baseUrl/activate.php?token=$token
                                </p>
                                <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
                                <p style='color: #999; font-size: 12px; text-align: center;'>
                                    Jika Anda tidak merasa mendaftar, abaikan email ini.
                                </p>
                            </div>
                        </body>
                        </html>
                    ";
                    
                    $mail->AltBody = "Halo $nama,\n\n" .
                                    "Silakan klik link berikut untuk aktivasi akun:\n" .
                                    "$baseUrl/activate.php?token=$token\n\n" .
                                    "Terima kasih!";

                    $mail->send();
                    $message = "Registrasi berhasil! Silakan cek email Anda untuk aktivasi akun.";
                    $message_type = "success";
                    
                    // Clear form data setelah berhasil
                    unset($nama, $email);
                    
                } catch (Exception $e) {
                    // Hapus data dari database jika email gagal dikirim
                    mysqli_query($conn, "DELETE FROM users WHERE email='$email' AND activation_token='$token'");
                    
                    $message = "Email gagal dikirim. Error: {$mail->ErrorInfo}";
                    $message_type = "error";
                    
                    error_log("PHPMailer Error: " . $mail->ErrorInfo);
                }
            } else {
                $message = "Gagal menyimpan data. Error: " . mysqli_error($conn);
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Admin Gudang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated background circles */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        body::before {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            animation-delay: 0s;
        }

        body::after {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .form-container {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .header .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .header .icon i {
            color: white;
            font-size: 32px;
        }

        .header h3 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            color: #777;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            color: #999;
            font-size: 16px;
            z-index: 1;
        }

        input[type=text],
        input[type=email],
        input[type=password] {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        input[type=text]:focus,
        input[type=email]:focus,
        input[type=password]:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .password-hint i {
            font-size: 11px;
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }

        button:active {
            transform: translateY(0);
        }

        .alert {
            margin-top: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            font-size: 20px;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.success i {
            color: #28a745;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert.error i {
            color: #dc3545;
        }

        .links {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #666;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .form-container {
                padding: 35px 25px;
            }

            .header h3 {
                font-size: 24px;
            }

            .header .icon {
                width: 60px;
                height: 60px;
            }

            .header .icon i {
                font-size: 28px;
            }
        }

        /* Loading animation for button */
        button.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        button.loading::after {
            content: '';
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            display: inline-block;
            margin-left: 10px;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="form-container">
        <div class="header">
            <div class="icon">
                <i class="fas fa-warehouse"></i>
            </div>
            <h3>Registrasi Admin</h3>
            <p>Buat akun baru untuk mengelola gudang</p>
        </div>

        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Nama Lengkap</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input type="text" name="nama" value="<?= isset($nama) ? htmlspecialchars($nama) : '' ?>" 
                           placeholder="Masukkan nama lengkap" required>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" 
                           placeholder="contoh@email.com" required>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" minlength="6" 
                           placeholder="Minimal 6 karakter" required>
                </div>
                <div class="password-hint">
                    <i class="fas fa-info-circle"></i>
                    <span>Minimal 6 karakter untuk keamanan akun Anda</span>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Konfirmasi Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" minlength="6" 
                           placeholder="Ulangi password" required>
                </div>
            </div>

            <button type="submit" name="register">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>
        
        <?php if (!empty($message)): ?>
            <div class="alert <?= $message_type ?>">
                <i class="fas fa-<?= $message_type === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <span><?= $message ?></span>
            </div>
        <?php endif; ?>
        
        <div class="links">
            Sudah punya akun? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login di sini</a>
        </div>
    </div>

    <script>
        // Add loading animation on form submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.classList.add('loading');
            button.innerHTML = '<i class="fas fa-spinner"></i> Memproses...';
        });

        // Auto-hide alert after 5 seconds
        const alert = document.querySelector('.alert');
        if (alert) {
            setTimeout(() => {
                alert.style.animation = 'slideDown 0.3s ease-out reverse';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }
    </script>
</body>
</html>