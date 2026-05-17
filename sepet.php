<?php
require_once 'config.php';

if(isset($_GET['sil'])) {
    sepettenSil($_GET['sil']);
    header("Location: sepet.php");
    exit;
}

if(isset($_GET['bosalt'])) {
    sepetiBosalt();
    header("Location: sepet.php");
    exit;
}

$sepetUrunler = [];
if(isset($_SESSION['sepet'])) {
    foreach($_SESSION['sepet'] as $id => $adet) {
        $stmt = $db->prepare("SELECT * FROM urunler WHERE id = ?");
        $stmt->execute([$id]);
        $urun = $stmt->fetch(PDO::FETCH_ASSOC);
        if($urun) {
            $urun['sepet_adet'] = $adet;
            $sepetUrunler[] = $urun;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="cart-page">
        <div class="container">
            <h1 style="margin-bottom:30px;">Sepetim</h1>
            
            <?php if(empty($sepetUrunler)): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>Sepetiniz Boş</h3>
                <p>Sepetinizde henüz ürün bulunmuyor.</p>
                <a href="index.php" class="btn btn-primary">Alışverişe Başla</a>
            </div>
            <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <div class="cart-header">
                        <span>Ürün</span>
                        <span>Fiyat</span>
                        <span>Adet</span>
                        <span>Toplam</span>
                        <span></span>
                    </div>
                    
                    <?php foreach($sepetUrunler as $urun): ?>
                    <div class="cart-item">
                        <div class="cart-product">
                            <div class="cart-product-img">
                                <?php if($urun['resim'] && file_exists('img/'.$urun['resim'])): ?>
                                <img src="img/<?= $urun['resim'] ?>" style="max-width:100%;max-height:100%;">
                                <?php else: ?>
                                🎁
                                <?php endif; ?>
                            </div>
                            <div class="cart-product-info">
                                <h4><?= $urun['ad'] ?></h4>
                                <p><?= SITE_NAME ?></p>
                            </div>
                        </div>
                        <div class="cart-price">₺<?= number_format($urun['fiyat'], 0) ?></div>
                        <div class="cart-qty">
                            <span><?= $urun['sepet_adet'] ?></span>
                        </div>
                        <div class="cart-price">₺<?= number_format($urun['fiyat'] * $urun['sepet_adet'], 0) ?></div>
                        <a href="?sil=<?= $urun['id'] ?>" class="cart-remove"><i class="fas fa-trash"></i></a>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <h3>Sipariş Özeti</h3>
                    <div class="summary-row">
                        <span>Ara Toplam</span>
                        <span>₺<?= number_format(sepetToplam(), 0) ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Kargo</span>
                        <span>Ücretsiz</span>
                    </div>
                    <div class="summary-row total">
                        <span>Toplam</span>
                        <span>₺<?= number_format(sepetToplam(), 0) ?></span>
                    </div>
                    <a href="siparis.php" class="btn btn-primary btn-lg">Siparişi Tamamla</a>
                    <a href="?bosalt=1" class="btn btn-danger" style="margin-top:10px;width:100%;">Sepeti Boşalt</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>