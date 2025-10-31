<?php
include '../config/conn_db.php';

$message = "";

// Pastikan token ada
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
    $query = "SELECT * FROM users WHERE reset_token='$token'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 0) {
        die("<div style='text-align:center; color:red; font-family:sans-serif; margin-top:50px;'>❌ Token tidak valid atau sudah digunakan.</div>");
    } else {
        $user = mysqli_fetch_assoc($result);
        // Cek token expired
        if (!empty($user['reset_token_expired']) && strtotime($user['reset_token_expired']) < time()) {
            die("<div style='text-align:center; color:red; font-family:sans-serif; margin-top:50px;'>⚠️ Token sudah kadaluarsa. Silakan minta link reset baru.</div>");
        }
    }
} else {
    die("<div style='text-align:center; color:red; font-family:sans-serif; margin-top:50px;'>Token tidak ditemukan.</div>");
}

// Handle perubahan password
if (isset($_POST['change'])) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $message = "❌ Konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $message = "⚠️ Password minimal 6 karakter!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = "UPDATE users 
                   SET password='$hashed', reset_token=NULL, reset_token_expired=NULL 
                   WHERE reset_token='$token'";
        if (mysqli_query($conn, $update)) {
            echo "<script>
                alert('Password berhasil diubah! Silakan login kembali.');
                window.location.href='login.php';
            </script>";
            exit();
        } else {
            $message = "Gagal memperbarui password. Coba lagi nanti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #74ebd5 0%, #ACB6E5 100%);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .form-container {
            width: 400px;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
        }
        h3 {
            color: #333;
            margin-bottom: 20px;
        }
        label {
            text-align: left;
            display: block;
            margin-top: 10px;
            color: #555;
            font-weight: 500;
        }
        input[type=password] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 8px;
            transition: 0.2s;
        }
        input[type=password]:focus {
            border-color: #74ebd5;
            outline: none;
            box-shadow: 0 0 5px rgba(116,235,213,0.5);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #74ebd5;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }
        button:hover {
            background: #5ac7c0;
        }
        .msg {
            margin-top: 15px;
            font-size: 14px;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h3>🔑 Reset Password</h3>
        <form method="POST">
            <label>Password Baru:</label>
            <input type="password" name="password" minlength="6" required>

            <label>Konfirmasi Password:</label>
            <input type="password" name="confirm_password" minlength="6" required>

            <button type="submit" name="change">Ubah Password</button>
        </form>
        <div class="msg"><?= $message ?></div>
    </div>
</body>
</html>
