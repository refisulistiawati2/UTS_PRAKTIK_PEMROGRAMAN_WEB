<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/conn_db.php';

$message = "";
$message_type = "";

if (isset($_POST['update'])) {
    $user_id = $_SESSION['user_id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    
    $sql = "UPDATE users SET nama='$nama' WHERE id=$user_id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['user_name'] = $nama;
        $message = "Profil berhasil diupdate!";
        $message_type = "success";
    } else {
        $message = "Gagal update profil!";
        $message_type = "error";
    }
}

if (isset($_POST['change_password'])) {
    $user_id = $_SESSION['user_id'];
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
    
    if (!password_verify($current, $user['password'])) {
        $message = "Password lama salah!";
        $message_type = "error";
    } elseif ($new !== $confirm) {
        $message = "Password baru tidak cocok!";
        $message_type = "error";
    } elseif (strlen($new) < 6) {
        $message = "Password minimal 6 karakter!";
        $message_type = "error";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id=$user_id");
        $message = "Password berhasil diubah!";
        $message_type = "success";
    }
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=" . $_SESSION['user_id']));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - Dashboard Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }
        body {
            background: #f3f6fa;
            color: #333;
        }
        .header {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .header h1 {
            font-size: 22px;
            letter-spacing: 1px;
        }
        .user-info {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        .logout-btn {
            background: #f44336;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
            font-weight: 500;
        }
        .logout-btn:hover {
            background: #d32f2f;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .nav-tabs {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .nav-tabs a {
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            color: #555;
            background: #f2f2f2;
            transition: all 0.3s;
            font-weight: 500;
        }
        .nav-tabs a.active {
            background: #2196F3;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .nav-tabs a:hover {
            background: #1976D2;
            color: white;
        }
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .profile-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .profile-name {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .profile-role {
            display: inline-block;
            padding: 6px 16px;
            background: #e3f2fd;
            color: #1976D2;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .profile-info {
            text-align: left;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }
        .profile-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .profile-info-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        .profile-info-text {
            flex: 1;
        }
        .profile-info-label {
            font-size: 12px;
            color: #777;
            margin-bottom: 2px;
        }
        .profile-info-value {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .forms-container {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .form-card h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #2196F3;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #2196F3, #1976D2);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .message::before {
            content: "✓";
            font-size: 20px;
            font-weight: bold;
        }
        .message.error::before {
            content: "✕";
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 600;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }
        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
        }
        .btn:active {
            transform: translateY(0);
        }
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
        }
        .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }
        .strength-bar-fill {
            height: 100%;
            transition: all 0.3s;
            border-radius: 2px;
        }
        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>🏭 Dashboard Admin Gudang</h1>
            <div class="user-info">
                <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= htmlspecialchars($_SESSION['user_role']) ?>)
            </div>
        </div>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <div class="nav-tabs">
            <a href="index.php">📦 Data Produk</a>
            <a href="profile.php" class="active">👤 Profil & Password</a>
        </div>

        <?php if ($message): ?>
            <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="content-grid">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <div class="profile-name"><?= htmlspecialchars($user['nama']) ?></div>
                <div class="profile-role"><?= htmlspecialchars($user['role']) ?></div>

                <div class="profile-info">
                    <div class="profile-info-item">
                        <div class="profile-info-icon">📧</div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Email</div>
                            <div class="profile-info-value"><?= htmlspecialchars($user['email']) ?></div>
                        </div>
                    </div>
                    
                    <div class="profile-info-item">
                        <div class="profile-info-icon">👤</div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Role</div>
                            <div class="profile-info-value"><?= htmlspecialchars($user['role']) ?></div>
                        </div>
                    </div>
                    
                    <div class="profile-info-item">
                        <div class="profile-info-icon">✓</div>
                        <div class="profile-info-text">
                            <div class="profile-info-label">Status</div>
                            <div class="profile-info-value">
                                <span class="status-badge"><?= htmlspecialchars($user['status']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forms Container -->
            <div class="forms-container">
                <!-- Update Profile Form -->
                <div class="form-card">
                    <h3>
                        <div class="form-icon">✏️</div>
                        Update Profil
                    </h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required placeholder="Masukkan nama lengkap">
                        </div>
                        <button type="submit" name="update" class="btn">💾 Simpan Perubahan</button>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="form-card">
                    <h3>
                        <div class="form-icon">🔒</div>
                        Ubah Password
                    </h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Password Lama</label>
                            <input type="password" name="current_password" required placeholder="Masukkan password lama">
                        </div>
                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="new_password" id="new_password" required minlength="6" placeholder="Minimal 6 karakter">
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-bar-fill" id="strength-fill"></div>
                                </div>
                                <span id="strength-text"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" required minlength="6" placeholder="Ketik ulang password baru">
                        </div>
                        <button type="submit" name="change_password" class="btn">🔐 Ubah Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('new_password');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');

        passwordInput?.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;

            const colors = ['#f44336', '#ff9800', '#ffc107', '#4caf50', '#2196f3'];
            const texts = ['Sangat Lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
            const widths = ['20%', '40%', '60%', '80%', '100%'];

            strengthFill.style.width = widths[strength];
            strengthFill.style.background = colors[strength];
            strengthText.textContent = texts[strength];
            strengthText.style.color = colors[strength];
        });
    </script>
</body>
</html>