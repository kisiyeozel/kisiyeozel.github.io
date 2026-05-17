<?php
require_once 'config.php';

// Kategorileri çek
$stmt = $db->query("SELECT * FROM kategoriler WHERE durum = 'aktif' ORDER BY siralama");
$kategoriler = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Öne çıkan ürünler
$stmt = $db->query("SELECT * FROM urunler WHERE durum = 'aktif' AND one_cikan = 'evet' ORDER BY id DESC LIMIT 6");
$oneCikan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tüm ürünler
$stmt = $db->query("SELECT * FROM urunler WHERE durum = 'aktif' ORDER BY id DESC");
$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Kişiye Özel Ürünler</title>
    <meta name="description" content="<?= SITE_DESC ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <i class="fas fa-truck"></i> Ücretsiz Kargo | Siparişleriniz 3-5 İş Günü İçinde Teslim
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
                        <a href="giris.php" class="header-action">
                            <i class="fas fa-user"></i>
                            <span>Giriş</span>
                        </a>
                        <a href="kayit.php" class="header-action">
                            <i class="fas fa-user-plus"></i>
                            <span>Kayıt</span>
                        </a>
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
                    <li><a href="#urunler">Ürünler</a></li>
                    <li><a href="#hakkimizda">Hakkımızda</a></li>
                    <li><a href="#iletisim">İletişim</a></li>
                </ul>
                <div class="nav-categories">
                    <a><i class="fas fa-bars"></i> Kategoriler</a>
                    <div class="category-dropdown">
                        <?php foreach($kategoriler as $kat): ?>
                        <a href="kategori.php?slug=<?= $kat['slug'] ?>"><?= $kat['ad'] ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <span class="hero-badge">✨ Yeni Sezon</span>
                <h1>Kendine Özel<br>Hediyeler Yarat</h1>
                <p>Monopoly, kitap takvim, kişiselleştirilmiş defter ve daha fazlası. Sevdikleriniz için benzersiz, anlamlı hediyeler tasarlayın.</p>
                <div class="hero-buttons">
                    <a href="#urunler" class="btn btn-primary"><i class="fas fa-compass"></i> Ürünleri Keşfet</a>
                    <a href="sepet.php" class="btn btn-outline" style="border-color:white;color:white;background:transparent;">Sepete Git</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <h3>5000+</h3>
                        <p>Mutlu Müşteri</p>
                    </div>
                    <div class="stat-item">
                        <h3>150+</h3>
                        <p>Özel Tasarım</p>
                    </div>
                    <div class="stat-item">
                        <h3>%98</h3>
                        <p>Memnuniyet</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-paint-brush"></i></div>
                    <h4>Özel Tasarım</h4>
                    <p>Her ürün sizin için özel tasarlanır.</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-shipping-fast"></i></div>
                    <h4>Hızlı Teslimat</h4>
                    <p>3-5 iş günü içinde kapınızda.</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-gem"></i></div>
                    <h4>Kaliteli Malzeme</h4>
                    <p>Premium kalite ürünler.</p>
                </div>
                <div class="feature-box">
                    <div class="feature-icon"><i class="fas fa-heart"></i></div>
                    <h4>Mükemmel Hediye</h4>
                    <p>Unutulmaz anılar için.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Products -->
    <section class="products" id="urunler">
        <div class="container">
            <div class="section-header">
                <h2>Ürünlerimiz</h2>
                <p>İsteğinize göre tamamen kişiselleştirilebilen özel ürünler</p>
            </div>
            
            <div class="products-filter">
                <button class="filter-btn active" data-filter="all">Tümü</button>
                <?php foreach($kategoriler as $kat): ?>
                <button class="filter-btn" data-filter="<?= $kat['id'] ?>"><?= $kat['ad'] ?></button>
                <?php endforeach; ?>
            </div>
            
            <div class="product-grid">
                <?php foreach($urunler as $urun): 
                    $kat = $db->query("SELECT ad FROM kategoriler WHERE id = ".$urun['kategori_id'])->fetch(PDO::FETCH_ASSOC);
                ?>
                <div class="product-card" data-category="<?= $urun['kategori_id'] ?>">
                    <?php if($urun['one_cikan'] == 'evet'): ?>
                    <span class="product-badge popular">Öne Çıkan</span>
                    <?php endif; ?>
                    <div class="product-image">
                        <?php if($urun['resim'] && file_exists('img/'.$urun['resim'])): ?>
                        <img src="img/<?= $urun['resim'] ?>" alt="<?= $urun['ad'] ?>">
                        <?php else: ?>
                        <span style="font-size:60px;">🎁</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?= $kat['ad'] ?? '' ?></span>
                        <h3 class="product-title"><?= $urun['ad'] ?></h3>
                        <p class="product-desc"><?= substr($urun['aciklama'], 0, 100) ?>...</p>
                        <div class="product-footer">
                            <div class="product-price">
                                ₺<?= number_format($urun['fiyat'], 0) ?>
                                <?php if($urun['eski_fiyat']): ?>
                                <span>₺<?= number_format($urun['eski_fiyat'], 0) ?></span>
                                <?php endif; ?>
                            </div>
                            <a href="urun.php?slug=<?= $urun['slug'] ?>" class="product-btn">İncele</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Banner -->
    <section class="banner">
        <div class="container">
            <h2>Özel Tasarım Hediyeler</h2>
            <p>Sevdikleriniz için benzersiz, kişiselleştirilmiş hediyeler yaratın</p>
            <a href="#iletisim" class="btn btn-primary btn-lg">İletişime Geç</a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="iletisim">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="logo">
                        <i class="fas fa-gift"></i>
                        Kişiye<span>Özel</span>
                    </a>
                    <p>Kişiye özel ürünler ile unutulmaz hediyeler yaratıyoruz. Monopoly, takvim, defter ve daha fazlası.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4 class="footer-title">Hızlı Linkler</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Ana Sayfa</a></li>
                        <li><a href="#urunler">Ürünler</a></li>
                        <li><a href="#hakkimizda">Hakkımızda</a></li>
                        <li><a href="#iletisim">İletişim</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 class="footer-title">Ürünler</h4>
                    <ul class="footer-links">
                        <li><a href="#">Monopoly</a></li>
                        <li><a href="#">Takvim</a></li>
                        <li><a href="#">Defter</a></li>
                        <li><a href="#">Planner</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 class="footer-title">İletişim</h4>
                    <ul class="footer-links">
                        <li><a href="tel:+905551234567">0555 123 4567</a></li>
                        <li><a href="mailto:info@kisiyeozel.org">info@kisiyeozel.org</a></li>
                        <li><a href="#">İstanbul, Türkiye</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 <?= SITE_NAME ?>. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </footer>

    <script>
        // Filter
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;
                
                document.querySelectorAll('.product-card').forEach(card => {
                    if(filter === 'all' || card.dataset.category === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Header scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('.header');
            if(window.scrollY > 100) {
                header.style.boxShadow = '0 5px 30px rgba(0,0,0,0.1)';
            }
        });
    </script>
</body>
</html>