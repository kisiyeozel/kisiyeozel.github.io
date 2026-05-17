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

// Yorum ekle
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

// Yorumları çek (onaylı)
$yorumlar = $db->query("SELECT * FROM yorumlar WHERE urun_id = ".$urun['id']." AND durum = 'onaylandi' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$toplamYorum = count($yorumlar);
$ortalamaPuan = $toplamYorum > 0 ? array_sum(array_column($yorumlar, 'puan')) / $toplamYorum : 0;

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
    <style>
        .product-detail { padding: 150px 0 80px; }
        .product-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start; }
        .product-detail-image { 
            background: var(--light); 
            border-radius: var(--radius-lg); 
            height: 450px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .product-detail-image img { max-width: 80%; max-height: 80%; object-fit: contain; }
        .product-detail-info { padding: 20px 0; }
        .product-detail-title { font-size: 2.5rem; margin-bottom: 15px; color: var(--primary); }
        .product-rating { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .product-rating-stars { color: var(--gold); font-size: 18px; }
        .product-rating-count { color: var(--gray); font-size: 14px; }
        .product-detail-price { font-size: 2.2rem; font-weight: 800; color: var(--primary); margin-bottom: 25px; }
        .product-detail-price span { font-size: 1.2rem; color: var(--gray); text-decoration: line-through; margin-left: 10px; }
        .product-detail-desc { color: var(--gray); font-size: 16px; line-height: 1.8; margin-bottom: 30px; }
        .product-options { background: var(--light); padding: 25px; border-radius: var(--radius-md); margin-bottom: 30px; }
        .product-options h4 { margin-bottom: 15px; font-size: 1rem; }
        .product-options ul { list-style: none; }
        .product-options li { padding: 8px 0; color: var(--gray); font-size: 14px; }
        .product-options li i { color: var(--accent); margin-right: 10px; width: 20px; }
        .add-to-cart-form { display: flex; gap: 15px; align-items: center; margin-bottom: 30px; }
        .qty-input { width: 70px; padding: 14px; border: 2px solid var(--gray-light); border-radius: var(--radius-md); text-align: center; font-size: 16px; }
        .product-badges { display: flex; gap: 15px; align-items: center; margin-bottom: 20px; }
        .product-badge-detail { padding: 10px 20px; border-radius: 50px; font-size: 13px; font-weight: 600; }
        .product-badge-detail.free { background: #d1fae5; color: #065f46; }
        
        /* Yorumlar */
        .reviews-section { margin-top: 80px; padding-top: 60px; border-top: 1px solid var(--gray-light); }
        .reviews-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .reviews-header h2 { font-size: 1.8rem; }
        .reviews-summary { text-align: center; padding: 30px; background: var(--light); border-radius: var(--radius-lg); }
        .reviews-summary-score { font-size: 3.5rem; font-weight: 800; color: var(--primary); }
        .reviews-summary-stars { color: var(--gold); font-size: 1.5rem; margin: 10px 0; }
        .reviews-summary-count { color: var(--gray); font-size: 14px; }
        .review-form { background: var(--light); padding: 30px; border-radius: var(--radius-lg); margin-bottom: 40px; }
        .review-form h3 { margin-bottom: 25px; }
        .rating-select { display: flex; gap: 8px; margin-bottom: 20px; }
        .rating-select input { display: none; }
        .rating-select label { font-size: 28px; cursor: pointer; color: var(--gray-light); transition: 0.2s; }
        .rating-select input:checked ~ label,
        .rating-select label:hover,
        .rating-select label:hover ~ label { color: var(--gold); }
        .review-list { margin-top: 40px; }
        .review-item { background: var(--white); padding: 25px; border-radius: var(--radius-md); margin-bottom: 20px; border: 1px solid var(--gray-light); }
        .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .review-author { font-weight: 700; color: var(--primary); }
        .review-date { color: var(--gray); font-size: 13px; }
        .review-stars { color: var(--gold); font-size: 14px; margin-bottom: 10px; }
        .review-text { color: var(--gray); line-height: 1.7; }
        .no-reviews { text-align: center; padding: 40px; color: var(--gray); }
        .no-reviews i { font-size: 48px; margin-bottom: 15px; opacity: 0.5; }
        
        @media (max-width: 768px) {
            .product-detail-grid { grid-template-columns: 1fr; gap: 30px; }
            .product-detail-image { height: 300px; }
            .add-to-cart-form { flex-direction: column; }
            .add-to-cart-form .btn { width: 100%; }
        }
    </style>
</head>
<body>
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
                    
                    <div class="product-badges">
                        <span class="product-badge-detail free"><i class="fas fa-truck"></i> Ücretsiz Kargo</span>
                        <span class="product-badge-detail" style="background:#e0e7ff;color:#3730a3;"><i class="fas fa-shield-alt"></i> 2 Yıl Garanti</span>
                    </div>
                    
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
            
            <!-- Yorumlar Bölümü -->
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
                                        <input type="email" name="email" placeholder="E-posta (görünmeyecek)">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Yorumunuz</label>
                                    <textarea name="yorum" rows="4" required placeholder="Ürün hakkında düşüncelerinizi paylaşın..."></textarea>
                                </div>
                                <button type="submit" name="yorum_ekle" class="btn btn-primary">Yorumu Gönder</button>
                            </form>
                        </div>
                        
                        <div class="review-list">
                            <?php if(empty($yorumlar)): ?>
                            <div class="no-reviews">
                                <i class="far fa-comment-dots"></i>
                                <p>Henüz yorum yapılmamış. İlk yorumu siz yapın!</p>
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