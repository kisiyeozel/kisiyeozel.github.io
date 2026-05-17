<?php
require_once '../config.php';
adminGirisKontrol();

// Durum güncelle
if(isset($_POST['durum_guncelle'])) {
    $id = $_POST['id'];
    $durum = $_POST['durum'];
    $stmt = $db->prepare("UPDATE yorumlar SET durum = ? WHERE id = ?");
    $stmt->execute([$durum, $id]);
    header("Location: yorumlar.php");
    exit;
}

// Yorum sil
if(isset($_GET['sil'])) {
    $stmt = $db->prepare("DELETE FROM yorumlar WHERE id = ?");
    $stmt->execute([$_GET['sil']]);
    header("Location: yorumlar.php");
    exit;
}

$yorumlar = $db->query("SELECT y.*, u.ad as urun_ad FROM yorumlar y LEFT JOIN urunler u ON y.urun_id = u.id ORDER BY y.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yorumlar - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-layout { display: flex; }
        .admin-sidebar { width: 270px; background: var(--primary); color: var(--white); position: fixed; height: 100vh; overflow-y: auto; }
        .admin-sidebar-header { padding: 28px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .admin-sidebar-header a { color: var(--white); text-decoration: none; }
        .admin-menu { padding: 20px 0; }
        .admin-menu-item { display: block; padding: 16px 24px; color: rgba(255,255,255,0.75); font-size: 14px; border-left: 3px solid transparent; }
        .admin-menu-item:hover, .admin-menu-item.active { background: rgba(255,255,255,0.08); color: var(--white); border-left-color: var(--accent); }
        .admin-menu-item i { margin-right: 12px; width: 20px; }
        .admin-content { flex: 1; margin-left: 270px; padding: 32px; background: var(--light); min-height: 100vh; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .admin-header h1 { font-size: 2rem; }
        .admin-card { background: var(--white); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-sm); }
        .status-badge { padding: 6px 14px; border-radius: 50px; font-size: 12px; font-weight: 600; }
        .status-beklemede { background: #fef3c7; color: #92400e; }
        .status-onaylandi { background: #d1fae5; color: #065f46; }
        .status-reddedildi { background: #fee2e2; color: #991b1b; }
        .review-card { background: var(--light); padding: 20px; border-radius: var(--radius-md); margin-bottom: 15px; }
        .review-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .review-card-product { font-weight: 700; color: var(--primary); }
        .review-card-stars { color: var(--gold); font-size: 14px; margin-bottom: 8px; }
        .review-card-text { color: var(--gray); font-size: 14px; line-height: 1.6; margin-bottom: 12px; }
        .review-card-author { font-size: 13px; color: var(--gray); }
        .review-actions { display: flex; gap: 10px; }
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
                <a href="siparisler.php" class="admin-menu-item"><i class="fas fa-shopping-cart"></i> Siparişler</a>
                <a href="yorumlar.php" class="admin-menu-item active"><i class="fas fa-comments"></i> Yorumlar</a>
                <a href="../cikis.php" class="admin-menu-item"><i class="fas fa-sign-out-alt"></i> Çıkış</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Yorum Yönetimi</h1>
            </div>
            
            <div class="admin-card">
                <?php if(empty($yorumlar)): ?>
                <p style="text-align:center;color:var(--gray);padding:40px;">Henüz yorum yok.</p>
                <?php else: ?>
                <?php foreach($yorumlar as $yorum): ?>
                <div class="review-card">
                    <div class="review-card-header">
                        <span class="review-card-product"><?= $yorum['urun_ad'] ?></span>
                        <span class="status-badge status-<?= $yorum['durum'] ?>"><?= $yorum['durum'] ?></span>
                    </div>
                    <div class="review-card-stars">
                        <?php for($i=1;$i<=5;$i++): ?>
                        <i class="fas fa-star<?= $i <= $yorum['puan'] ? '' : '-empty' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="review-card-text"><?= $yorum['yorum'] ?></p>
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span class="review-card-author"><?= $yorum['ad_soyad'] ?> • <?= date('d.m.Y H:i', strtotime($yorum['created_at'])) ?></span>
                        <div class="review-actions">
                            <?php if($yorum['durum'] != 'onaylandi'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $yorum['id'] ?>">
                                <input type="hidden" name="durum" value="onaylandi">
                                <button type="submit" name="durum_guncelle" class="btn btn-sm" style="background:#d1fae5;color:#065f46;">Onayla</button>
                            </form>
                            <?php endif; ?>
                            <?php if($yorum['durum'] != 'reddedildi'): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $yorum['id'] ?>">
                                <input type="hidden" name="durum" value="reddedildi">
                                <button type="submit" name="durum_guncelle" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;">Reddet</button>
                            </form>
                            <?php endif; ?>
                            <a href="?sil=<?= $yorum['id'] ?>" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;" onclick="return confirm('Silinsin mi?')">Sil</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>