<?php
session_start();
include '../config/conn_db.php';

$message = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        if ($user['status'] != 'ACTIVE') {
            $message = "⚠️ Akun belum diaktivasi. Silakan cek email kamu.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            header("Location: ../dashboard/index.php");
            exit();
        } else {
            $message = "❌ Password salah.";
        }
    } else {
        $message = "❌ Email tidak terdaftar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | Gudang Management</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card {
            width: 380px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.2);
            padding: 40px 35px;
            color: white;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 22px;
        }

        .input-group {
            margin-bottom: 18px;
            position: relative;
        }

        .input-group label {
            font-size: 14px;
            display: block;
            margin-bottom: 6px;
            color: #f1f1f1;
        }

        .input-group input {
            width: 100%;
            padding: 12px 38px 12px 14px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }

        .input-group input:focus {
            background: rgba(255,255,255,0.35);
        }

        .input-group i {
            position: absolute;
            right: 12px;
            top: 36px;
            color: #eee;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #6c63ff;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 5px;
        }

        button:hover {
            background: #574bdb;
            transform: scale(1.03);
        }

        .msg {
            margin-top: 15px;
            text-align: center;
            color: #ffbaba;
            font-size: 14px;
            min-height: 18px;
        }

        .links {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
        }

        .links a {
            color: #fff;
            text-decoration: none;
            opacity: 0.85;
        }

        .links a:hover {
            opacity: 1;
            text-decoration: underline;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
            color: rgba(255,255,255,0.6);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>🏭 Gudang Management</h2>
        <form method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Masukkan email kamu" required>
                <i>📧</i>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
                <i>🔒</i>
            </div>

            <button type="submit" name="login">Masuk</button>
        </form>

        <div class="msg"><?= $message ?></div>

        <div class="links">
            <a href="forgot_password.php">Lupa Password?</a> • 
            <a href="register.php">Buat Akun</a>
        </div>

        <div class="footer-text">© <?= date('Y') ?> Gudang Management System</div>
    </div>
</body>
</html>
