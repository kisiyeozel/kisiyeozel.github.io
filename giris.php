<?php
require_once 'config.php';

if(isset($_SESSION['kullanici_id'])) {
    header("Location: index.php");
    exit;
}

$hata = '';

if(isset($_POST['giris'])) {
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];
    
    $stmt = $db->prepare("SELECT * FROM kullanicilar WHERE email = ? AND durum = 'aktif'");
    $stmt->execute([$email]);
    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($kullanici && password_verify($sifre, $kullanici['sifre'])) {
        $_SESSION['kullanici_id'] = $kullanici['id'];
        $_SESSION['kullanici_ad'] = $kullanici['ad_soyad'];
        header("Location: index.php");
        exit;
    } else {
        $hata = "E-posta veya şifre hatalı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - <?= SITE_NAME ?></title>
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
            
            <?php if($hata): ?>
            <div class="alert alert-danger"><?= $hata ?></div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" required placeholder="E-posta adresiniz">
                </div>
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="password" name="sifre" required placeholder="Şifreniz">
                </div>
                <button type="submit" name="giris" class="btn btn-primary btn-lg">Giriş Yap</button>
            </form>
            
            <div class="auth-footer">
                Hesabınız yok mu? <a href="kayit.php">Kayıt Ol</a>
            </div>
        </div>
    </div>
</body>
</html>