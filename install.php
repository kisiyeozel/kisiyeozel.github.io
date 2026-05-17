<?php
// Veritabanı Kurulum Dosyası - Bu dosyayı bir kez çalıştırın

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kisiyeozel_db";

try {
    // Veritabanı oluştur
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8 COLLATE utf8_general_ci");
    echo "Veritabanı oluşturuldu.<br>";
    
    // Veritabanını seç
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
    
    // Tabloları oluştur
    $conn->exec("CREATE TABLE IF NOT EXISTS kategoriler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ad VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        durum ENUM('aktif','pasif') DEFAULT 'aktif',
        siralama INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "kategoriler tablosu oluşturuldu.<br>";
    
    $conn->exec("CREATE TABLE IF NOT EXISTS urunler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kategori_id INT,
        ad VARCHAR(200) NOT NULL,
        slug VARCHAR(200) NOT NULL,
        aciklama TEXT,
        fiyat DECIMAL(10,2) NOT NULL,
        eski_fiyat DECIMAL(10,2),
        resim VARCHAR(255),
        durum ENUM('aktif','pasif') DEFAULT 'aktif',
        stok INT DEFAULT 100,
        one_cikan ENUM('evet','hayir') DEFAULT 'hayir',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (kategori_id) REFERENCES kategoriler(id)
    )");
    echo "urunler tablosu oluşturuldu.<br>";
    
    $conn->exec("CREATE TABLE IF NOT EXISTS kullanicilar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ad_soyad VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        telefon VARCHAR(20),
        sifre VARCHAR(255) NOT NULL,
        durum ENUM('aktif','pasif') DEFAULT 'aktif',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "kullanicilar tablosu oluşturuldu.<br>";
    
    $conn->exec("CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
        sifre VARCHAR(255) NOT NULL,
        ad VARCHAR(100),
        durum ENUM('aktif','pasif') DEFAULT 'aktif',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "admin tablosu oluşturuldu.<br>";
    
    $conn->exec("CREATE TABLE IF NOT EXISTS siparisler (
        id INT AUTO_INCREMENT PRIMARY KEY,
        kullanici_id INT,
        ad_soyad VARCHAR(100) NOT NULL,
        telefon VARCHAR(20),
        email VARCHAR(100),
        adres TEXT,
        sehir VARCHAR(50),
        siparis_notu TEXT,
        toplam_tutar DECIMAL(10,2) NOT NULL,
        durum ENUM('beklemede','onaylandi','hazirlaniyor','kargoda','teslim_edildi','iptal') DEFAULT 'beklemede',
        odeme_turu VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id)
    )");
    echo "siparisler tablosu oluşturuldu.<br>";
    
    $conn->exec("CREATE TABLE IF NOT EXISTS siparis_urunleri (
        id INT AUTO_INCREMENT PRIMARY KEY,
        siparis_id INT NOT NULL,
        urun_id INT NOT NULL,
        urun_adi VARCHAR(200),
        fiyat DECIMAL(10,2) NOT NULL,
        adet INT NOT NULL,
        FOREIGN KEY (siparis_id) REFERENCES siparisler(id),
        FOREIGN KEY (urun_id) REFERENCES urunler(id)
    )");
    echo "siparis_urunleri tablosu oluşturuldu.<br>";
    
    // Yorumlar tablosu
    $conn->exec("CREATE TABLE IF NOT EXISTS yorumlar (
        id INT AUTO_INCREMENT PRIMARY KEY,
        urun_id INT NOT NULL,
        kullanici_id INT,
        ad_soyad VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        puan INT DEFAULT 5,
        yorum TEXT NOT NULL,
        durum ENUM('beklemede','onaylandi','reddedildi') DEFAULT 'beklemede',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (urun_id) REFERENCES urunler(id),
        FOREIGN KEY (kullanici_id) REFERENCES kullanicilar(id)
    )");
    echo "yorumlar tablosu oluşturuldu.<br>";
    
    // Örnek verileri ekle
    // Kategoriler
    $kategoriCheck = $conn->query("SELECT COUNT(*) FROM kategoriler")->fetchColumn();
    if($kategoriCheck == 0) {
        $conn->exec("INSERT INTO kategoriler (ad, slug, siralama) VALUES 
            ('Monopoly', 'monopoly', 1),
            ('Takvim', 'takvim', 2),
            ('Defter', 'defter', 3),
            ('Planner', 'planner', 4),
            ('Hediye', 'hediye', 5),
            ('Çerçeve', 'cerceve', 6)
        ");
        echo "Örnek kategoriler eklendi.<br>";
    }
    
    // Admin
    $adminCheck = $conn->query("SELECT COUNT(*) FROM admin")->fetchColumn();
    if($adminCheck == 0) {
        $sifre = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->exec("INSERT INTO admin (kullanici_adi, sifre, ad) VALUES ('admin', '$sifre', 'Yönetici')");
        echo "Admin hesabı oluşturuldu. Kullanıcı: admin, Şifre: admin123<br>";
    }
    
    // Örnek ürünler
    $urunCheck = $conn->query("SELECT COUNT(*) FROM urunler")->fetchColumn();
    if($urunCheck == 0) {
        $conn->exec("INSERT INTO urunler (kategori_id, ad, slug, aciklama, fiyat, eski_fiyat, resim, durum, one_cikan, stok) VALUES
            (1, 'Kişiye Özel Monopoly', 'kisiye-ozel-monopoly', 'Ailenize veya arkadaşlarınıza özel monopoly seti. Oyun tahtası, kartlar ve para üzerine isimleriniz, fotoğraflarınız ve özel mekanlarınız eklenir.', 349.00, 399.00, 'monopoly.jpg', 'aktif', 'evet', 50),
            (2, 'Kitap Takvim', 'kitap-takvim', '365 günlük anılarınızı ve özel günlerinizi anlatan benzersiz takvim. Her ay için özel fotoğraf ve not eklenebilir.', 199.00, 249.00, 'takvim.jpg', 'aktif', 'evet', 100),
            (3, 'Kişiselleştirilmiş Defter', 'kisisellestirilmis-defter', 'İsminiz ve özel tasarımınız ile hazırlanmış benzersiz defter. Günlük, hatıra defteri veya özel not defteri olarak kullanabilirsiniz.', 129.00, 159.00, 'defter.jpg', 'aktif', 'evet', 200),
            (4, 'Özel Yıl Planner', 'ozel-yil-planner', 'Yıl hedeflerinizi, aylık ve günlük planlarınızı takip edeceğiniz kişisel planlayıcı.', 179.00, 199.00, 'planner.jpg', 'aktif', 'hayir', 80),
            (5, 'Özel Hediye Kutusu', 'ozel-hediye-kutusu', 'Doğum günü, yıl dönümü veya özel günler için hazırlanmış hediye kutusu.', 249.00, 299.00, 'hediye.jpg', 'aktif', 'evet', 50),
            (6, 'Özel Fotoğraf Çerçevesi', 'ozel-fotograf-cercevesi', 'En güzel anılarınız için özel tasarım çerçeveler.', 89.00, 119.00, 'cerceve.jpg', 'aktif', 'hayir', 150)
        ");
        echo "Örnek ürünler eklendi.<br>";
    }
    
    echo "<h2>Kurulum Tamamlandı!</h2>";
    echo "<p><a href='index.php'>Siteye Git</a> | <a href='admin/'>Admin Panele Git</a></p>";
    
} catch(PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>