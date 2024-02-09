<?php 
// Sunucu zaman dilimi ne olduğunu gösterir
	//echo date_default_timezone_get()."<br>";
// Genel ayarlarda zaman dilimini ayarlamak içiin sunucu zamanı göstermek içindir
	$sunucu_tarihi = date('Y-m-d, H:i:s');
	$varsayilan_zaman_dilimi = date_default_timezone_get();

######## Date&Time #############################################################
// Yerel tarih ayarı
	setlocale(LC_ALL, 'tr_TR.UTF8'); /* Local time setting. See: http://www.php.net/manual/en/function.setlocale.php */
	date_default_timezone_set( $genel_ayarlar['secili_zaman_dilimi'] ?? 'Europe/Istanbul' ); /* Zone time setting. See: http://www.php.net/manual/en/function.date-default-timezone-set.php */

// Genel ayarlarda zaman dilimini ayarlamak içiin yerel zamanı göstermek içindir
	$yerel_tarihi = date('Y-m-d, H:i:s');
######## Date&Time #############################################################

function date_tr($f, $zt = 'now'){
	$z = date("$f", $zt);
	$donustur = array(
		'Monday'	=> 'Pazartesi',
		'Tuesday'	=> 'Salı',
		'Wednesday'	=> 'Çarşamba',
		'Thursday'	=> 'Perşembe',
		'Friday'	=> 'Cuma',
		'Saturday'	=> 'Cumartesi',
		'Sunday'	=> 'Pazar',
		'January'	=> 'Ocak',
		'February'	=> 'Şubat',
		'March'		=> 'Mart',
		'April'		=> 'Nisan',
		'May'		=> 'Mayıs',
		'June'		=> 'Haziran',
		'July'		=> 'Temmuz',
		'August'	=> 'Ağustos',
		'September'	=> 'Eylül',
		'October'	=> 'Ekim',
		'November'	=> 'Kasım',
		'December'	=> 'Aralık',
		'Mon'		=> 'Pts',
		'Tue'		=> 'Sal',
		'Wed'		=> 'Çar',
		'Thu'		=> 'Per',
		'Fri'		=> 'Cum',
		'Sat'		=> 'Cts',
		'Sun'		=> 'Paz',
		'Jan'		=> 'Oca',
		'Feb'		=> 'Şub',
		'Mar'		=> 'Mar',
		'Apr'		=> 'Nis',
		'Jun'		=> 'Haz',
		'Jul'		=> 'Tem',
		'Aug'		=> 'Ağu',
		'Sep'		=> 'Eyl',
		'Oct'		=> 'Eki',
		'Nov'		=> 'Kas',
		'Dec'		=> 'Ara',
	);
	foreach($donustur as $en => $tr){
		$z = str_replace($en, $tr, $z);
	}
	if(strpos($z, 'Mayıs') !== false && strpos($f, 'F') === false) $z = str_replace('Mayıs', 'May', $z);
	return $z;
}

function near_date($data)
{
$date = date_tr('Y-m-d H:i:s', $data);
	// 'yakın' tarihleri hesaplayalım
	$y_d = date('Y-m-d', strtotime('-1 day'));
	$c_d = date('Y-m-d');
	$t_d = date('Y-m-d', strtotime('+1 day'));
	
	// 'yakın' tarihler için bir harita dizisi oluşturalım
	$map[$y_d] = 'Dün';
	$map[$c_d] = 'Bugün';
	$map[$t_d] = 'Yarın';
	
	// giriş tarihini tarih ve saat bölümleri ayıralım
	list($d,$t) = explode(' ',$date);
	
	if(!isset($map[$d]))
	{
		// 'yakın' tarihlerden biri değil, orijinal değeri döndürelim
		return date_tr('d M Y, l, H:i', $data); //$date;
	}
	
	// 'yakın' tarihlerden biri, 'yakın' tarih değerini döndürelim
	return "$map[$d] $t";
}
?>