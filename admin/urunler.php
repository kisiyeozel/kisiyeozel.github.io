<?php
require_once '../config.php';
adminGirisKontrol();

// Ürün ekleme
if(isset($_POST['urun_ekle'])) {
    $ad = $_POST['ad'];
    $slug = strtolower(str_replace(' ', '-', $ad));
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];
    $kategori_id = $_POST['kategori_id'];
    $stok = $_POST['stok'] ?? 100;
    
    $stmt = $db->prepare("INSERT INTO urunler (ad, slug, aciklama, fiyat, kategori_id, stok) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$ad, $slug, $aciklama, $fiyat, $kategori_id, $stok]);
    header("Location: urunler.php");
    exit;
}

// Ürün silme
if(isset($_GET['sil'])) {
    $stmt = $db->prepare("DELETE FROM urunler WHERE id = ?");
    $stmt->execute([$_GET['sil']]);
    header("Location: urunler.php");
    exit;
}

$urunler = $db->query("SELECT u.*, k.ad as kategori_ad FROM urunler u LEFT JOIN kategoriler k ON u.kategori_id = k.id ORDER BY u.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY ad")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürünler - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout { display: flex; }
        .admin-sidebar { width: 260px; background: var(--primary); color: var(--white); position: fixed; height: 100vh; overflow-y: auto; }
        .admin-sidebar-header { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .admin-sidebar-header a { color: var(--white); text-decoration: none; }
        .admin-menu { padding: 20px 0; }
        .admin-menu-item { display: block; padding: 14px 20px; color: rgba(255,255,255,0.8); font-size: 14px; transition: var(--transition); border-left: 3px solid transparent; }
        .admin-menu-item:hover, .admin-menu-item.active { background: rgba(255,255,255,0.1); color: var(--white); border-left-color: var(--accent); }
        .admin-menu-item i { margin-right: 10px; width: 20px; }
        .admin-content { flex: 1; margin-left: 260px; padding: 30px; background: var(--light); min-height: 100vh; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h2><a href="index.php">Admin Panel</a></h2>
            </div>
            <nav class="admin-menu">
                <a href="index.php" class="admin-menu-item"><i class="fas fa-home"></i> Dashboard</a>
                <a href="urunler.php" class="admin-menu-item active"><i class="fas fa-box"></i> Ürünler</a>
                <a href="kategoriler.php" class="admin-menu-item"><i class="fas fa-tags"></i> Kategoriler</a>
                <a href="siparisler.php" class="admin-menu-item"><i class="fas fa-shopping-cart"></i> Siparişler</a>
                <a href="../cikis.php" class="admin-menu-item"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Ürünler</h1>
                <button class="btn btn-primary" onclick="document.getElementById('urunForm').style.display='block'">+ Yeni Ürün</button>
            </div>
            
            <div id="urunForm" class="admin-card" style="display:none;margin-bottom:30px;">
                <h3>Yeni Ürün Ekle</h3>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Ürün Adı</label>
                            <input type="text" name="ad" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="kategori_id" required>
                                <option value="">Seçin</option>
                                <?php foreach($kategoriler as $kat): ?>
                                <option value="<?= $kat['id'] ?>"><?= $kat['ad'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fiyat (₺)</label>
                            <input type="number" name="fiyat" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" value="100">
                        </div>
                        <div class="form-group full">
                            <label>Açıklama</label>
                            <textarea name="aciklama" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="submit" name="urun_ekle" class="btn btn-primary">Kaydet</button>
                </form>
            </div>
            
            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ürün</th>
                            <th>Kategori</th>
                            <th>Fiyat</th>
                            <th>Stok</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($urunler as $urun): ?>
                        <tr>
                            <td><?= $urun['id'] ?></td>
                            <td><?= $urun['ad'] ?></td>
                            <td><?= $urun['kategori_ad'] ?? '-' ?></td>
                            <td>₺<?= number_format($urun['fiyat'], 0) ?></td>
                            <td><?= $urun['stok'] ?></td>
                            <td><span class="status-badge status-<?= $urun['durum'] ?>"><?= $urun['durum'] ?></span></td>
                            <td class="actions">
                                <a href="?sil=<?= $urun['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Silinsin mi?')">Sil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>