<?php
require_once '../config.php';

$hata = '';

if(isset($_POST['giris'])) {
    $kullanici_adi = $_POST['kullanici_adi'];
    $sifre = $_POST['sifre'];
    
    $stmt = $db->prepare("SELECT * FROM admin WHERE kullanici_adi = ? AND durum = 'aktif'");
    $stmt->execute([$kullanici_adi]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($admin && password_verify($sifre, $admin['sifre'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_ad'] = $admin['ad'];
        header("Location: index.php");
        exit;
    } else {
        $hata = "Kullanıcı adı veya şifre hatalı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Giriş</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a2e, #16213e); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-box { background: white; padding: 40px; border-radius: 15px; width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        h2 { color: #1a1a2e; text-align: center; margin-bottom: 30px; }
        input { width: 100%; padding: 15px; margin-bottom: 15px; border: 2px solid #ddd; border-radius: 10px; font-size: 14px; }
        input:focus { outline: none; border-color: #e94560; }
        button { width: 100%; padding: 15px; background: #e94560; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #c73e54; }
        .hata { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Giriş</h2>
        <?php if($hata): ?><div class="hata"><?= $hata ?></div><?php endif; ?>
        <form method="POST">
            <input type="text" name="kullanici_adi" placeholder="Kullanıcı Adı" required>
            <input type="password" name="sifre" placeholder="Şifre" required>
            <button type="submit" name="giris">Giriş Yap</button>
        </form>
    </div>
</body>
</html>