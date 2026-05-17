<?php
// Veritabanı Bağlantı Ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'kisiyeozel_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("Veritabanı bağlantısı başarısız: " . $e->getMessage());
}

// Site Ayarları
define('SITE_URL', 'http://localhost/kisiyeozel');
define('SITE_NAME', 'Kişiye Özel');
define('SITE_DESC', 'Kişiye özel monopoly, kitap takvim ve daha fazlası');

// Sepet Fonksiyonları
session_start();

function sepeteEkle($urun_id, $adet = 1) {
    if(!isset($_SESSION['sepet'])) {
        $_SESSION['sepet'] = [];
    }
    
    if(isset($_SESSION['sepet'][$urun_id])) {
        $_SESSION['sepet'][$urun_id] += $adet;
    } else {
        $_SESSION['sepet'][$urun_id] = $adet;
    }
}

function sepettenSil($urun_id) {
    if(isset($_SESSION['sepet'][$urun_id])) {
        unset($_SESSION['sepet'][$urun_id]);
    }
}

function sepetiBosalt() {
    unset($_SESSION['sepet']);
}

function sepetToplam() {
    $toplam = 0;
    if(isset($_SESSION['sepet'])) {
        global $db;
        foreach($_SESSION['sepet'] as $id => $adet) {
            $stmt = $db->prepare("SELECT fiyat FROM urunler WHERE id = ?");
            $stmt->execute([$id]);
            $urun = $stmt->fetch(PDO::FETCH_ASSOC);
            if($urun) {
                $toplam += $urun['fiyat'] * $adet;
            }
        }
    }
    return $toplam;
}

function sepetUrunSayisi() {
    $sayi = 0;
    if(isset($_SESSION['sepet'])) {
        foreach($_SESSION['sepet'] as $adet) {
            $sayi += $adet;
        }
    }
    return $sayi;
}

// Admin Kontrolü
function adminGirisKontrol() {
    if(!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Kullanıcı Kontrolü
function kullaniciGirisKontrol() {
    if(!isset($_SESSION['kullanici_id'])) {
        header("Location: giris.php");
        exit;
    }
}
?>