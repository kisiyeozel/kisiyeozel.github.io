<?php
require_once '../config.php';
adminGirisKontrol();

// Resim yükleme fonksiyonu
function uploadImage($file, $id) {
    if($file['error'] === 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if(in_array($ext, $allowed)) {
            $newName = 'urun_' . $id . '_' . time() . '.' . $ext;
            $uploadDir = '../img/';
            if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($file['tmp_name'], $uploadDir . $newName);
            return $newName;
        }
    }
    return null;
}

// Ürün ekleme
if(isset($_POST['urun_ekle'])) {
    $ad = $_POST['ad'];
    $slug = strtolower(str_replace(' ', '-', turkce($ad)));
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];
    $eski_fiyat = $_POST['eski_fiyat'] ?: null;
    $kategori_id = $_POST['kategori_id'];
    $stok = $_POST['stok'] ?? 100;
    $one_cikan = $_POST['one_cikan'] ?? 'hayir';
    
    $stmt = $db->prepare("INSERT INTO urunler (ad, slug, aciklama, fiyat, eski_fiyat, kategori_id, stok, one_cikan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$ad, $slug, $aciklama, $fiyat, $eski_fiyat, $kategori_id, $stok, $one_cikan]);
    
    $urun_id = $db->lastInsertId();
    
    if($_FILES['resim']['error'] === 0) {
        $resim = uploadImage($_FILES['resim'], $urun_id);
        if($resim) {
            $db->prepare("UPDATE urunler SET resim = ? WHERE id = ?")->execute([$resim, $urun_id]);
        }
    }
    
    header("Location: urunler.php");
    exit;
}

// Ürün güncelleme
if(isset($_POST['urun_guncelle'])) {
    $id = $_POST['id'];
    $ad = $_POST['ad'];
    $slug = strtolower(str_replace(' ', '-', turkce($ad)));
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];
    $eski_fiyat = $_POST['eski_fiyat'] ?: null;
    $kategori_id = $_POST['kategori_id'];
    $stok = $_POST['stok'];
    $one_cikan = $_POST['one_cikan'] ?? 'hayir';
    $durum = $_POST['durum'];
    
    $resimUpdate = "";
    if($_FILES['resim']['error'] === 0) {
        $resim = uploadImage($_FILES['resim'], $id);
        if($resim) {
            $eski = $db->query("SELECT resim FROM urunler WHERE id = $id")->fetch();
            if($eski['resim'] && file_exists('../img/'.$eski['resim'])) {
                unlink('../img/'.$eski['resim']);
            }
            $resimUpdate = ", resim = '$resim'";
        }
    }
    
    $sql = "UPDATE urunler SET ad=?, slug=?, aciklama=?, fiyat=?, eski_fiyat=?, kategori_id=?, stok=?, one_cikan=?, durum=? $resimUpdate WHERE id=?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$ad, $slug, $aciklama, $fiyat, $eski_fiyat, $kategori_id, $stok, $one_cikan, $durum, $id]);
    
    header("Location: urunler.php");
    exit;
}

// Ürün silme
if(isset($_GET['sil'])) {
    $id = $_GET['sil'];
    $urun = $db->query("SELECT resim FROM urunler WHERE id = $id")->fetch();
    if($urun['resim'] && file_exists('../img/'.$urun['resim'])) {
        unlink('../img/'.$urun['resim']);
    }
    $stmt = $db->prepare("DELETE FROM urunler WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: urunler.php");
    exit;
}

$duzenle = null;
if(isset($_GET['duzenle'])) {
    $stmt = $db->prepare("SELECT * FROM urunler WHERE id = ?");
    $stmt->execute([$_GET['duzenle']]);
    $duzenle = $stmt->fetch(PDO::FETCH_ASSOC);
}

$urunler = $db->query("SELECT u.*, k.ad as kategori_ad FROM urunler u LEFT JOIN kategoriler k ON u.kategori_id = k.id ORDER BY u.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$kategoriler = $db->query("SELECT * FROM kategoriler ORDER BY ad")->fetchAll(PDO::FETCH_ASSOC);

function turkce($str) {
    $str = str_replace(['ı','ş','ç','ğ','ü','ö'], ['i','s','c','g','u','o'], $str);
    return preg_replace('/[^a-zA-Z0-9-]/', '-', $str);
}
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
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group.full { grid-column: span 2; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; }
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .product-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; }
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
                <a href="siparisler.php" class="admin-menu-item"><i class="fas fa-shopping-cart"></i> Siparişler</a>
                <a href="../cikis.php" class="admin-menu-item"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Ürünler</h1>
                <button class="btn btn-primary" onclick="document.getElementById('urunForm').style.display='block'">+ Yeni Ürün</button>
            </div>
            
            <div id="urunForm" class="admin-card" style="display:<?= $duzenle ? 'none' : 'block' ?>;margin-bottom:30px;">
                <h3>Yeni Ürün Ekle</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Ürün Adı *</label>
                            <input type="text" name="ad" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori *</label>
                            <select name="kategori_id" required>
                                <option value="">Seçin</option>
                                <?php foreach($kategoriler as $kat): ?>
                                <option value="<?= $kat['id'] ?>"><?= $kat['ad'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fiyat (₺) *</label>
                            <input type="number" name="fiyat" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Eski Fiyat</label>
                            <input type="number" name="eski_fiyat" step="0.01" placeholder="İndirim için">
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" value="100">
                        </div>
                        <div class="form-group">
                            <label>Öne Çıkan</label>
                            <select name="one_cikan">
                                <option value="hayir">Hayır</option>
                                <option value="evet">Evet</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label>Ürün Resmi</label>
                            <input type="file" name="resim" accept="image/*">
                        </div>
                        <div class="form-group full">
                            <label>Açıklama</label>
                            <textarea name="aciklama" rows="3"></textarea>
                        </div>
                    </div>
                    <button type="submit" name="urun_ekle" class="btn btn-primary">Kaydet</button>
                </form>
            </div>
            
            <?php if($duzenle): ?>
            <div class="admin-card" style="margin-bottom:30px;border:2px solid var(--accent);">
                <h3>Ürün Düzenle - <?= $duzenle['ad'] ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $duzenle['id'] ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Ürün Adı *</label>
                            <input type="text" name="ad" value="<?= $duzenle['ad'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori *</label>
                            <select name="kategori_id" required>
                                <?php foreach($kategoriler as $kat): ?>
                                <option value="<?= $kat['id'] ?>" <?= $kat['id']==$duzenle['kategori_id']?'selected':'' ?>><?= $kat['ad'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fiyat (₺) *</label>
                            <input type="number" name="fiyat" step="0.01" value="<?= $duzenle['fiyat'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Eski Fiyat</label>
                            <input type="number" name="eski_fiyat" step="0.01" value="<?= $duzenle['eski_fiyat'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stok" value="<?= $duzenle['stok'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Durum</label>
                            <select name="durum">
                                <option value="aktif" <?= $duzenle['durum']=='aktif'?'selected':'' ?>>Aktif</option>
                                <option value="pasif" <?= $duzenle['durum']=='pasif'?'selected':'' ?>>Pasif</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Yeni Resim</label>
                            <input type="file" name="resim" accept="image/*">
                            <?php if($duzenle['resim']): ?>
                            <img src="../img/<?= $duzenle['resim'] ?>" style="width:80px;margin-top:5px;">
                            <?php endif; ?>
                        </div>
                        <div class="form-group full">
                            <label>Açıklama</label>
                            <textarea name="aciklama" rows="3"><?= $duzenle['aciklama'] ?></textarea>
                        </div>
                    </div>
                    <button type="submit" name="urun_guncelle" class="btn btn-success">Güncelle</button>
                    <a href="urunler.php" class="btn btn-secondary">İptal</a>
                </form>
            </div>
            <?php endif; ?>
            
            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Resim</th>
                            <th>Ürün</th>
                            <th>Fiyat</th>
                            <th>Stok</th>
                            <th>Durum</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($urunler as $urun): ?>
                        <tr>
                            <td><?= $urun['id'] ?></td>
                            <td>
                                <?php if($urun['resim'] && file_exists('../img/'.$urun['resim'])): ?>
                                <img src="../img/<?= $urun['resim'] ?>" class="product-thumb">
                                <?php else: ?>
                                <i class="fas fa-image" style="color:#ccc;"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= $urun['ad'] ?></td>
                            <td>₺<?= number_format($urun['fiyat'], 0) ?></td>
                            <td><?= $urun['stok'] ?></td>
                            <td><span class="status-badge status-<?= $urun['durum'] ?>"><?= $urun['durum'] ?></span></td>
                            <td>
                                <a href="?duzenle=<?= $urun['id'] ?>" class="btn btn-primary btn-sm">Düzenle</a>
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