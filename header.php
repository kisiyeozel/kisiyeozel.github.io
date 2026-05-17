<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="header-top">
            <i class="fas fa-truck"></i> Ücretsiz Kargo | 3-5 İş Günü Teslim
        </div>
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <a href="index.php" class="logo">
                        <i class="fas fa-gift"></i>
                        Kişiye<span>Özel</span>
                    </a>
                    
                    <form class="search-box" action="ara.php" method="GET">
                        <input type="text" name="q" placeholder="Ürün ara...">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                    
                    <div class="header-actions">
                        <?php if(isset($_SESSION['kullanici_id'])): ?>
                        <a href="hesabim.php" class="header-action">
                            <i class="fas fa-user"></i>
                            <span>Hesabım</span>
                        </a>
                        <a href="cikis.php" class="header-action">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Çıkış</span>
                        </a>
                        <?php else: ?>
                        <a href="giris.php" class="header-action">
                            <i class="fas fa-user"></i>
                            <span>Giriş</span>
                        </a>
                        <a href="kayit.php" class="header-action">
                            <i class="fas fa-user-plus"></i>
                            <span>Kayıt</span>
                        </a>
                        <?php endif; ?>
                        <a href="sepet.php" class="header-action cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count"><?= sepetUrunSayisi() ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <nav class="nav-menu">
            <div class="container">
                <ul class="nav-links">
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li><a href="index.php#urunler">Ürünler</a></li>
                    <li><a href="#iletisim">İletişim</a></li>
                </ul>
            </div>
        </nav>
    </header>