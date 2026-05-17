<?php
require_once 'config.php';

if(isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}

$mesaj = '';

if(isset($_POST['kayit'])) {
    $ad_soyad = $_POST['ad_soyad'];
    $email = $_POST['email'];
    $telefon = $_POST['telefon'];
    $sifre = password_hash($_POST['sifre'], PASSWORD_DEFAULT);
    
    // Email kontrolü
    $stmt = $db->prepare("SELECT id FROM kullanicilar WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->fetch()) {
        $mesaj = '<div class="alert alert-danger">Bu e-posta zaten kayıtlı!</div>';
    } else {
        $stmt = $db->prepare("INSERT INTO kullanicilar (ad_soyad, email, telefon, sifre) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ad_soyad, $email, $telefon, $sifre]);
        
        // Otomatik giriş
        $kullanici_id = $db->lastInsertId();
        $_SESSION['kullanici_id'] = $kullanici_id;
        $_SESSION['kullanici_ad'] = $ad_soyad;
        
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-logo">
                <a href="index.php" class="logo">
                    <i class="fas fa-gift"></i>
                    Kişiye<span>Özel</span>
                </a>
            </div>
            
            <?= $mesaj ?>
            
            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label>Ad Soyad</label>
                    <input type="text" name="ad_soyad" required placeholder="Adınız ve soyadınız">
                </div>
                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" required placeholder="E-posta adresiniz">
                </div>
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="tel" name="telefon" placeholder="Telefon numaranız">
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="sifre" required placeholder="Şifreniz">
                </div>
                <button type="submit" name="kayit" class="btn btn-primary btn-lg">Kayıt Ol</button>
            </form>
            
            <div class="auth-footer">
                Hesabınız var mı? <a href="giris.php">Giriş Yap</a>
            </div>
        </div>
    </div>
</body>
</html>