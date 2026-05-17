<?php
require_once 'config.php';

if(empty($_SESSION['sepet'])) {
    header("Location: index.php");
    exit;
}

$mesaj = '';

if(isset($_POST['siparis_ver'])) {
    $ad_soyad = $_POST['ad_soyad'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'];
    $adres = $_POST['adres'];
    $sehir = $_POST['sehir'];
    $not = $_POST['not'];
    $toplam = sepetToplam();
    
    $kullanici_id = $_SESSION['kullanici_id'] ?? null;
    
    // Sipariş kaydet
    $stmt = $db->prepare("INSERT INTO siparisler (kullanici_id, ad_soyad, telefon, email, adres, sehir, siparis_notu, toplam_tutar, durum) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'beklemede')");
    $stmt->execute([$kullanici_id, $ad_soyad, $telefon, $email, $adres, $sehir, $not, $toplam]);
    
    $siparis_id = $db->lastInsertId();
    
    // Sipariş ürünlerini kaydet
    foreach($_SESSION['sepet'] as $urun_id => $adet) {
        $urun = $db->query("SELECT * FROM urunler WHERE id = $urun_id")->fetch(PDO::FETCH_ASSOC);
        if($urun) {
            $stmt = $db->prepare("INSERT INTO siparis_urunleri (siparis_id, urun_id, urun_adi, fiyat, adet) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$siparis_id, $urun_id, $urun['ad'], $urun['fiyat'], $adet]);
        }
    }
    
    sepetiBosalt();
    
    $mesaj = '<div class="alert alert-success">Siparişiniz başarıyla alındı! Teşekkürler.</div>';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <section class="cart-page">
        <div class="container">
            <?= $mesaj ?>
            
            <h1 style="margin-bottom:30px;">Siparişi Tamamla</h1>
            
            <form method="POST" class="checkout-form">
                <h3 class="form-title">Kişisel Bilgiler</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Ad Soyad *</label>
                        <input type="text" name="ad_soyad" required value="<?= $_SESSION['kullanici_ad'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Telefon *</label>
                        <input type="tel" name="telefon" required>
                    </div>
                    <div class="form-group">
                        <label>E-posta *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Şehir *</label>
                        <input type="text" name="sehir" required>
                    </div>
                    <div class="form-group full">
                        <label>Adres *</label>
                        <textarea name="adres" rows="2" required></textarea>
                    </div>
                    <div class="form-group full">
                        <label>Sipariş Notu</label>
                        <textarea name="not" rows="2" placeholder="Varsa özel notlarınız..."></textarea>
                    </div>
                </div>
                
                <h3 class="form-title" style="margin-top:30px;">Sipariş Özeti</h3>
                <div class="cart-summary" style="position:static;box-shadow:none;padding:20px;background:var(--light);margin-bottom:20px;">
                    <div class="summary-row">
                        <span>Toplam</span>
                        <span style="font-size:1.5rem;font-weight:700;">₺<?= number_format(sepetToplam(), 0) ?></span>
                    </div>
                </div>
                
                <button type="submit" name="siparis_ver" class="btn btn-primary btn-lg" style="width:100%;">Siparişi Tamamla</button>
            </form>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>