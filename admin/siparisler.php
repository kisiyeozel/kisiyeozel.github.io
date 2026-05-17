<?php
require_once '../config.php';
adminGirisKontrol();

// Sipariş durumu güncelle
if(isset($_POST['durum_guncelle'])) {
    $id = $_POST['siparis_id'];
    $durum = $_POST['durum'];
    $stmt = $db->prepare("UPDATE siparisler SET durum = ? WHERE id = ?");
    $stmt->execute([$durum, $id]);
    header("Location: siparisler.php");
    exit;
}

$siparisler = $db->query("SELECT * FROM siparisler ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Siparişler - Admin Panel</title>
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
                <a href="urunler.php" class="admin-menu-item"><i class="fas fa-box"></i> Ürünler</a>
                <a href="siparisler.php" class="admin-menu-item active"><i class="fas fa-shopping-cart"></i> Siparişler</a>
                <a href="yorumlar.php" class="admin-menu-item"><i class="fas fa-comments"></i> Yorumlar</a>
                <a href="../cikis.php" class="admin-menu-item"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Siparişler</h1>
            </div>
            
            <div class="admin-card">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Müşteri</th>
                            <th>Telefon</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($siparisler as $siparis): ?>
                        <tr>
                            <td>#<?= $siparis['id'] ?></td>
                            <td><?= $siparis['ad_soyad'] ?><br><small><?= $siparis['email'] ?></small></td>
                            <td><?= $siparis['telefon'] ?></td>
                            <td>₺<?= number_format($siparis['toplam_tutar'], 0) ?></td>
                            <td>
                                <span class="status-badge status-<?= str_replace('ı','i',$siparis['durum']) ?>">
                                    <?= str_replace(['ı','ş','ç','ğ'], ['i','s','c','g'], $siparis['durum']) ?>
                                </span>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($siparis['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="siparis_id" value="<?= $siparis['id'] ?>">
                                    <select name="durum" style="padding:5px;margin-right:5px;border-radius:5px;border:1px solid #ddd;">
                                        <option value="beklemede" <?= $siparis['durum']=='beklemede'?'selected':'' ?>>Beklemede</option>
                                        <option value="onaylandi" <?= $siparis['durum']=='onaylandi'?'selected':'' ?>>Onaylandı</option>
                                        <option value="hazirlaniyor" <?= $siparis['durum']=='hazirlaniyor'?'selected':'' ?>>Hazırlanıyor</option>
                                        <option value="kargoda" <?= $siparis['durum']=='kargoda'?'selected':'' ?>>Kargoda</option>
                                        <option value="teslim_edildi" <?= $siparis['durum']=='teslim_edildi'?'selected':'' ?>>Teslim Edildi</option>
                                        <option value="iptal" <?= $siparis['durum']=='iptal'?'selected':'' ?>>İptal</option>
                                    </select>
                                    <button type="submit" name="durum_guncelle" class="btn btn-primary btn-sm">Güncelle</button>
                                </form>
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