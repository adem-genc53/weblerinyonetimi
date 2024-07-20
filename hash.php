<?php 
// Bismillahirrahmanirrahim

class Hash {

    protected $key;       // Şifreleme anahtarı
    protected $method;    // Şifreleme yöntemi
    protected $options;   // Şifreleme seçenekleri
    protected $iv;        // Başlangıç vektörü (Initialization Vector)

    public function __construct()
    {
        // Şifreleme yöntemi olarak AES-256-CBC kullanılıyor
        $this->method  = "aes-256-cbc";
        
        // Şifreleme anahtarı
        $this->key     = 'C2jNZ2--#xZ3bFa!LQ8E&OGzM&m*%z-1';
        
        // Şifreleme seçenekleri (bu örnekte 0 olarak ayarlanmış)
        $this->options = 0;
        
        // Başlangıç vektörü, anahtarın SHA-256 hash'inin ilk 16 karakteri kullanılarak oluşturuluyor
        $this->iv      = substr(hash('sha256', $this->key), 0, 16);

        // OpenSSL şifreleme fonksiyonlarının mevcut olup olmadığını kontrol et
        if (!function_exists('openssl_encrypt')) {
            exit('<strong>HASH Hatası:</strong> Sınıf çalışması için OpenSSL kütüphanesine ihtiyaç duyar.');
        }
    }

    // Şifreleme fonksiyonu
    public function make($password, $key = FALSE)
    {
        // Veriyi şifreleyip döndürüyor
        return trim(openssl_encrypt($password, $this->method, $this->key($key), $this->options, $this->iv));
    }

    // Şifre çözme fonksiyonu
    public function take($protected, $key = FALSE)
    {
        // Şifreli veriyi çözerek döndürüyor
        return trim(openssl_decrypt($protected, $this->method, $this->key($key), $this->options, $this->iv));
    }

    // Anahtar fonksiyonu (Eğer anahtar uzunluğu 32 karakter değilse, varsayılan anahtarı kullan)
    public function key($key)
    {
        return strlen($key) == 32 ? $key : $this->key;
    }
}
?>