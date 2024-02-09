<?php 
// Bismillahirrahmanirrahim
/*
* A "Reversible" password encryption routine by Sinan Eldem
* www.sinaneldem.com.tr
* V. 1.3
* 24.02.2013 21:02
*/
class Hash {

    protected $key;     
    protected $method;   
    protected $options; 
    protected $iv;   

    public function __construct()
    {
        $this->method  = "rc4-hmac-md5";
        $this->key     = '3h2cwpYJscKTGrbFiUveeWV3iMAdrzGf';
        $this->options = 0;
        $this->iv      = "";

        /*
        if(!function_exists('mcrypt_create_iv'))
        {
            exit('<strong>HASH Error:</strong> Sınıf çalışması için Mcrypt kütüphanesine ihtiyaç duyar.');
        }       

        if(version_compare(PHP_VERSION, '5.3.0') === -1)
        {
            exit('<strong>HASH Error:</strong> Sınıf en azından PHP 5.3.0\'a ihtiyaç duyuyor.');
        }
        */
    }

    public function make($password, $key = FALSE)
    {
        return trim(openssl_encrypt($password, $this->method, $this->key($key), $this->options, $this->iv));
    }

    public function take($protected, $key = FALSE)
    {
        return trim(openssl_decrypt($protected, $this->method, $this->key($key), $this->options, $this->iv));
    }

    public function key($key)
    {
        return strlen($key) == 32 ? $key : $this->key;
    }
}
?>