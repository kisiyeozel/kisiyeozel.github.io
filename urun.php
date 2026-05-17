<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';
$stmt = $db->prepare("SELECT * FROM urunler WHERE slug = ? AND durum = 'aktif'");
$stmt->execute([$slug]);
$urun = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$urun) {
    header("Location: index.php");
    exit;
}

$kat = $db->query("SELECT * FROM kategoriler WHERE id = ".$urun['kategori_id'])->fetch(PDO::FETCH_ASSOC);

// Sepete ekle
if(isset($_POST['sepete_ekle'])) {
    sepeteEkle($urun['id'], $_POST['adet'] ?? 1);
    header("Location: sepet.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $urun['ad'] ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="cart-page">
        <div class="container">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:start;">
                <div class="product-image" style="height:400px;border-radius:20px;">
                    <?php if($urun['resim'] && file_exists('img/'.$urun['resim'])): ?>
                    <img src="img/<?= $urun['resim'] ?>" alt="<?= $urun['ad'] ?>">
                    <?php else: ?>
                    <span style="font-size:120px;">🎁</span>
                    <?php endif; ?>
                </div>
                
                <div>
                    <span class="product-category"><?= $kat['ad'] ?? '' ?></span>
                    <h1 style="font-size:2rem;margin:15px 0;color:var(--primary);"><?= $urun['ad'] ?></h1>
                    <div class="product-price" style="font-size:2rem;margin-bottom:20px;">
                        ₺<?= number_format($urun['fiyat'], 0) ?>
                        <?php if($urun['eski_fiyat']): ?>
                        <span style="font-size:1.2rem;">₺<?= number_format($urun['eski_fiyat'], 0) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <p style="color:var(--gray);margin-bottom:30px;line-height:1.8;"><?= $urun['aciklama'] ?></p>
                    
                    <div style="background:var(--light);padding:20px;border-radius:15px;margin-bottom:30px;">
                        <h4 style="margin-bottom:15px;">Özelleştirme Seçenekleri</h4>
                        <p style="font-size:14px;color:var(--gray);">
                            • İsim ekleme<br>
                            • Fotoğraf ekleme<br>
                            • Özel mesaj ekleme<br>
                            • Renk seçimi
                        </p>
                    </div>
                    
                    <form method="POST" style="display:flex;gap:15px;align-items:center;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <label>Adet:</label>
                            <input type="number" name="adet" value="1" min="1" max="<?= $urun['stok'] ?>" style="width:60px;padding:10px;border:2px solid #ddd;border-radius:8px;">
                        </div>
                        <button type="submit" name="sepete_ekle" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart"></i> Sepete Ekle
                        </button>
                    </form>
                    
                    <div style="margin-top:30px;display:flex;gap:20px;color:var(--gray);font-size:14px;">
                        <span><i class="fas fa-truck"></i> Ücretsiz Kargo</span>
                        <span><i class="fas fa-shield-alt"></i> 2 Yıl Garanti</span>
                        <span><i class="fas fa-undo"></i> Kolay İade</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>