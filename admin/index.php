<?php
require_once '../config.php';
adminGirisKontrol();

// İstatistikler
$toplamUrun = $db->query("SELECT COUNT(*) FROM urunler")->fetchColumn();
$toplamSiparis = $db->query("SELECT COUNT(*) FROM siparisler")->fetchColumn();
$toplamKullanici = $db->query("SELECT COUNT(*) FROM kullanicilar")->fetchColumn();
$toplamGelir = $db->query("SELECT SUM(toplam_tutar) FROM siparisler WHERE durum != 'iptal'")->fetchColumn();

// Son siparişler
$sonSiparisler = $db->query("SELECT * FROM siparisler ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - <?= SITE_NAME ?></title>
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
                <a href="index.php" class="admin-menu-item active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="urunler.php" class="admin-menu-item"><i class="fas fa-box"></i> Ürünler</a>
                <a href="kategoriler.php" class="admin-menu-item"><i class="fas fa-tags"></i> Kategoriler</a>
                <a href="siparisler.php" class="admin-menu-item"><i class="fas fa-shopping-cart"></i> Siparişler</a>
                <a href="yorumlar.php" class="admin-menu-item"><i class="fas fa-comments"></i> Yorumlar</a>
                <a href="ayarlar.php" class="admin-menu-item"><i class="fas fa-cog"></i> Ayarlar</a>
                <a href="../cikis.php" class="admin-menu-item"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <span>Hoş geldin, Yönetici</span>
            </div>
            
            <div class="admin-stats">
                <div class="stat-card">
                    <h4>Toplam Ürün</h4>
                    <div class="value"><?= $toplamUrun ?></div>
                </div>
                <div class="stat-card">
                    <h4>Toplam Sipariş</h4>
                    <div class="value"><?= $toplamSiparis ?></div>
                </div>
                <div class="stat-card">
                    <h4>Toplam Kullanıcı</h4>
                    <div class="value"><?= $toplamKullanici ?></div>
                </div>
                <div class="stat-card">
                    <h4>Toplam Gelir</h4>
                    <div class="value">₺<?= number_format($toplamGelir ?? 0, 0) ?></div>
                </div>
            </div>
            
            <div class="admin-card">
                <h3>Son Siparişler</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Müşteri</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($sonSiparisler as $siparis): ?>
                        <tr>
                            <td>#<?= $siparis['id'] ?></td>
                            <td><?= $siparis['ad_soyad'] ?></td>
                            <td>₺<?= number_format($siparis['toplam_tutar'], 0) ?></td>
                            <td><span class="status-badge status-<?= $siparis['durum'] ?>"><?= $siparis['durum'] ?></span></td>
                            <td><?= date('d.m.Y', strtotime($siparis['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>