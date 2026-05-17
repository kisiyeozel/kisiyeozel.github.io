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

if(isset($_POST['yorum_ekle'])) {
    $ad_soyad = $_POST['ad_soyad'];
    $email = $_POST['email'];
    $puan = $_POST['puan'];
    $yorum = $_POST['yorum'];
    $kullanici_id = $_SESSION['kullanici_id'] ?? null;
    
    $stmt = $db->prepare("INSERT INTO yorumlar (urun_id, kullanici_id, ad_soyad, email, puan, yorum, durum) VALUES (?, ?, ?, ?, ?, ?, 'beklemede')");
    $stmt->execute([$urun['id'], $kullanici_id, $ad_soyad, $email, $puan, $yorum]);
    $mesaj = '<div class="alert alert-success">Yorumunuz gönderildi! Onaylandıktan sonra yayınlanacaktır.</div>';
}

try {
    $yorumlar = $db->query("SELECT * FROM yorumlar WHERE urun_id = ".$urun['id']." AND durum = 'onaylandi' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $toplamYorum = count($yorumlar);
    $ortalamaPuan = $toplamYorum > 0 ? array_sum(array_column($yorumlar, 'puan')) / $toplamYorum : 0;
} catch(Exception $e) {
    $yorumlar = [];
    $toplamYorum = 0;
    $ortalamaPuan = 0;
}

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
    <style>
        .product-detail { padding: 150px 0 80px; }
        .product-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start; }
        .product-detail-image { 
            background: var(--surface); 
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg); 
            height: 450px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
        }
        .product-detail-image img { max-width: 80%; max-height: 80%; object-fit: contain; }
        .product-detail-info { padding: 20px 0; }
        .product-detail-title { font-size: 2.5rem; margin-bottom: 15px; }
        .product-rating { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .product-rating-stars { color: #fbbf24; font-size: 18px; }
        .product-rating-count { color: var(--text-muted); font-size: 14px; }
        .product-detail-price { font-size: 2.2rem; font-weight: 800; margin-bottom: 25px; }
        .product-detail-price span { font-size: 1.2rem; color: var(--text-muted); text-decoration: line-through; margin-left: 10px; }
        .product-detail-desc { color: var(--text-muted); font-size: 16px; line-height: 1.8; margin-bottom: 30px; }
        .product-options { background: var(--surface); padding: 25px; border-radius: var(--radius-md); margin-bottom: 30px; border: 1px solid var(--glass-border); }
        .product-options h4 { margin-bottom: 15px; font-size: 1rem; }
        .product-options ul { list-style: none; }
        .product-options li { padding: 10px 0; color: var(--text-muted); font-size: 14px; border-bottom: 1px solid var(--glass-border); }
        .product-options li:last-child { border-bottom: none; }
        .product-options li i { color: var(--accent-light); margin-right: 10px; width: 20px; }
        .add-to-cart-form { display: flex; gap: 15px; align-items: center; margin-bottom: 30px; }
        .qty-input { width: 70px; padding: 14px; background: var(--surface); border: 1px solid var(--glass-border); border-radius: var(--radius-md); text-align: center; font-size: 16px; color: var(--text); }
        @media (max-width: 768px) {
            .product-detail-grid { grid-template-columns: 1fr; gap: 30px; }
            .product-detail-image { height: 300px; }
            .add-to-cart-form { flex-direction: column; }
            .add-to-cart-form .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="page-bg"></div>
    <div class="grid-pattern"></div>
    
    <?php include 'header.php'; ?>

    <section class="product-detail">
        <div class="container">
            <?= $mesaj ?? '' ?>
            
            <div class="product-detail-grid">
                <div class="product-detail-image">
                    <?php if($urun['resim'] && file_exists('img/'.$urun['resim'])): ?>
                    <img src="img/<?= $urun['resim'] ?>" alt="<?= $urun['ad'] ?>">
                    <?php else: ?>
                    <span style="font-size:120px;">🎁</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-detail-info">
                    <span class="product-category"><?= $kat['ad'] ?? '' ?></span>
                    
                    <h1 class="product-detail-title"><?= $urun['ad'] ?></h1>
                    
                    <div class="product-rating">
                        <div class="product-rating-stars">
                            <?php for($i=1;$i<=5;$i++): ?>
                            <i class="fas fa-star<?= $i <= round($ortalamaPuan) ? '' : '-empty' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="product-rating-count"><?= $toplamYorum ?> yorum</span>
                    </div>
                    
                    <div class="product-detail-price">
                        ₺<?= number_format($urun['fiyat'], 0) ?>
                        <?php if($urun['eski_fiyat']): ?>
                        <span>₺<?= number_format($urun['eski_fiyat'], 0) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="product-detail-desc"><?= $urun['aciklama'] ?></p>
                    
                    <div class="product-options">
                        <h4>Özelleştirme Seçenekleri</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> İsim ekleme</li>
                            <li><i class="fas fa-check"></i> Fotoğraf ekleme</li>
                            <li><i class="fas fa-check"></i> Özel mesaj ekleme</li>
                            <li><i class="fas fa-check"></i> Renk seçimi</li>
                        </ul>
                    </div>
                    
                    <form method="POST" class="add-to-cart-form">
                        <input type="number" name="adet" value="1" min="1" max="<?= $urun['stok'] ?>" class="qty-input">
                        <button type="submit" name="sepete_ekle" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag"></i> Sepete Ekle
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Yorumlar -->
            <div class="reviews-section">
                <div class="reviews-header">
                    <h2>Yorumlar (<?= $toplamYorum ?>)</h2>
                </div>
                
                <div style="display:grid;grid-template-columns:300px 1fr;gap:40px;">
                    <div class="reviews-summary">
                        <div class="reviews-summary-score"><?= number_format($ortalamaPuan, 1) ?></div>
                        <div class="reviews-summary-stars">
                            <?php for($i=1;$i<=5;$i++): ?>
                            <i class="fas fa-star<?= $i <= round($ortalamaPuan) ? '' : '-empty' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="reviews-summary-count"><?= $toplamYorum ?> değerlendirme</div>
                    </div>
                    
                    <div>
                        <div class="review-form">
                            <h3>Yorum Yaz</h3>
                            <form method="POST">
                                <div class="form-group">
                                    <label>Puanınız</label>
                                    <div class="rating-select">
                                        <?php for($i=5;$i>=1;$i--): ?>
                                        <input type="radio" name="puan" id="puan<?= $i ?>" value="<?= $i ?>" <?= $i==5?'checked':'' ?> required>
                                        <label for="puan<?= $i ?>"><i class="fas fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label>Adınız</label>
                                        <input type="text" name="ad_soyad" required placeholder="Adınız">
                                    </div>
                                    <div class="form-group">
                                        <label>E-posta</label>
                                        <input type="email" name="email" placeholder="E-posta">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Yorumunuz</label>
                                    <textarea name="yorum" rows="4" required placeholder="Düşüncelerinizi paylaşın..."></textarea>
                                </div>
                                <button type="submit" name="yorum_ekle" class="btn btn-primary">Gönder</button>
                            </form>
                        </div>
                        
                        <div class="review-list">
                            <?php if(empty($yorumlar)): ?>
                            <div class="no-reviews">
                                <i class="far fa-comment-dots"></i>
                                <p>Henüz yorum yapılmamış.</p>
                            </div>
                            <?php else: ?>
                            <?php foreach($yorumlar as $yorum): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-author"><?= $yorum['ad_soyad'] ?></span>
                                    <span class="review-date"><?= date('d.m.Y', strtotime($yorum['created_at'])) ?></span>
                                </div>
                                <div class="review-stars">
                                    <?php for($i=1;$i<=5;$i++): ?>
                                    <i class="fas fa-star<?= $i <= $yorum['puan'] ? '' : '-empty' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="review-text"><?= $yorum['yorum'] ?></p>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>
</body>
</html>