<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../config/conn_db.php';

// Handle CRUD Products
if (isset($_POST['add_product'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $sku = mysqli_real_escape_string($conn, $_POST['sku']);
    $stok = intval($_POST['stok']);
    $harga = floatval($_POST['harga']);
    $created_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO products (nama_produk, sku, stok, harga, created_by) 
            VALUES ('$nama', '$sku', $stok, $harga, $created_by)";
    mysqli_query($conn, $sql);
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: index.php");
    exit();
}

// Get products
$products = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Gudang</title>
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
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            padding: 30px 40px;
        }
        .nav-tabs {
            margin-bottom: 30px;
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .nav-tabs a {
            padding: 12px 24px;
            border-radius: 6px 6px 0 0;
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
        h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
            border-left: 4px solid #2196F3;
            padding-left: 12px;
        }
        form {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
            font-size: 14px;
        }
        .form-group input {
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            transition: all 0.3s;
            font-size: 14px;
        }
        .form-group input:focus {
            border-color: #2196F3;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
        }
        .btn {
            padding: 12px 30px;
            background: #4CAF50;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn:hover {
            background: #43a047;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table thead {
            background: #1976D2;
            color: white;
        }
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        table td {
            padding: 15px;
            text-align: left;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        table th:nth-child(1),
        table td:nth-child(1) {
            width: 60px;
            text-align: center;
        }
        table th:nth-child(2),
        table td:nth-child(2) {
            width: 30%;
        }
        table th:nth-child(3),
        table td:nth-child(3) {
            width: 15%;
        }
        table th:nth-child(4),
        table td:nth-child(4) {
            width: 12%;
            text-align: center;
        }
        table th:nth-child(5),
        table td:nth-child(5) {
            width: 18%;
            text-align: right;
        }
        table th:nth-child(6),
        table td:nth-child(6) {
            width: 15%;
            text-align: center;
        }
        table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        table tbody tr:hover {
            background: #e3f2fd;
            transition: 0.3s;
        }
        .action-btns {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        .action-btns a {
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
        }
        .edit-btn {
            background: #2196F3;
            color: white;
        }
        .delete-btn {
            background: #f44336;
            color: white;
        }
        .edit-btn:hover { 
            background: #1976D2;
            transform: translateY(-1px);
        }
        .delete-btn:hover { 
            background: #d32f2f;
            transform: translateY(-1px);
        }
        .stok-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }
        .stok-low {
            background: #ffebee;
            color: #c62828;
        }
        .stok-medium {
            background: #fff3e0;
            color: #ef6c00;
        }
        .stok-high {
            background: #e8f5e9;
            color: #2e7d32;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .container {
                padding: 20px;
                margin: 20px;
            }
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>🏭 Dashboard Admin Gudang</h1>
            <div class="user-info">
                <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= htmlspecialchars($_SESSION['user_role']) ?>) - <?= htmlspecialchars($_SESSION['user_email']) ?>
            </div>
        </div>
        <a href="../auth/logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <div class="nav-tabs">
            <a href="index.php" class="active">📦 Data Produk</a>
            <a href="profile.php">👤 Profil & Password</a>
        </div>

        <h2>Tambah Produk</h2>
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_produk" required placeholder="Masukkan nama produk">
                </div>
                <div class="form-group">
                    <label>SKU (Kode Produk)</label>
                    <input type="text" name="sku" required placeholder="Contoh: BRG-001">
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" required min="0" placeholder="Jumlah stok">
                </div>
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" required min="0" step="0.01" placeholder="Harga produk">
                </div>
            </div>
            <button type="submit" name="add_product" class="btn">+ Tambah Produk</button>
        </form>

        <h2>Daftar Produk</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>SKU</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($products)): 
                        $stok = $row['stok'];
                        $stok_class = $stok < 10 ? 'stok-low' : ($stok < 50 ? 'stok-medium' : 'stok-high');
                    ?>
                    <tr>
                        <td style="text-align: center; font-weight: 600;"><?= $no++ ?></td>
                        <td><strong><?= htmlspecialchars($row['nama_produk']) ?></strong></td>
                        <td><code style="background: #f0f0f0; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?= htmlspecialchars($row['sku']) ?></code></td>
                        <td style="text-align: center;">
                            <span class="stok-badge <?= $stok_class ?>"><?= $stok ?></span>
                        </td>
                        <td style="text-align: right; font-weight: 600; color: #2e7d32;">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="?delete=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Yakin mau hapus produk ini?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>