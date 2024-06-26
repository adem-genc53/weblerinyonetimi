<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

############### GÖREV ZAMANLAMA YÖNETİMİ #######################################################################
if($_POST['urun'] == "zamanlanmisgorev"){

	if(isset($_POST['per'])){
	  if($_POST['per']=='-1'){
		$limit = '99999999999';
	  }else{
		$limit = $_POST['per'];
	  }
	}
  else
  {
	$limit = '15';
  }
  
	$search_keyword = " '%%' ";
	
	if(!empty($_POST['query'])) {
	  $search_keyword = $_POST['query'];
	
	// Çift tırnak " ile başlayan ve biten arama kelime
	$pattern = '/^".+"$/i';
	if(preg_match($pattern, $search_keyword))
	{
	  $searchkeyword = str_replace('"','',$search_keyword);
	  $search_keyword = " '$searchkeyword' ";
	// $search_keyword zaten gerekli değer
	}else{
	// sadece % joker karakterleri ekliyoruz
	$search_keyword = " '%$search_keyword%' ";
	}
	}
	if(!empty($_POST['bildirgoster'])){
	  $edit_id = " AND id = ".$_POST['bildirgoster']."";
	}else{
	  $edit_id = "";
	}
	
	$n = 9; // Kaç tane arama seçeneği var
	$search_params = array_fill($n, 0, $search_keyword);
  
  
	$sql = 'SELECT *
	  FROM zamanlanmisgorev    
	  WHERE
	  (id LIKE '.$search_keyword.' OR
	  sonraki_calisma LIKE '.$search_keyword.' OR 
	  haftanin_gunu LIKE '.$search_keyword.' OR 
	  gun LIKE '.$search_keyword.' OR
	  saat LIKE '.$search_keyword.' OR
	  dakika LIKE '.$search_keyword.' OR
	  aktif LIKE '.$search_keyword.' OR
	  gunluk_kayit LIKE '.$search_keyword.' OR
	  dosya_adi LIKE '.$search_keyword.' OR
	  gorev_adi LIKE '.$search_keyword.')
	  '.$edit_id.'
	  ORDER BY id ASC ';
	
	$page = 1;
	$start = 0;
	if(!empty($_POST["page"])) {
	  $page = $_POST["page"];
	  $start = ($page-1) * $limit;
	}
	$limit_code = " LIMIT " . $start . "," . $limit;
	$pagination_statement = $PDOdb->prepare($sql);
	//$pagination_statement->bindValue(':keyword', '%' . $search_keyword . '%', PDO::PARAM_STR);
	$pagination_statement->execute($search_params);
  
	$top_sayfa = $pagination_statement->rowCount();
  
	$query = $sql.$limit_code;
	$pdo_statement = $PDOdb->prepare($query);
	//$pdo_statement->bindValue(':keyword', '%' . $search_keyword . '%', PDO::PARAM_STR);
	$pdo_statement->execute($search_params);
	$result = $pdo_statement->fetchAll();

  $satirlar = '';

  if($top_sayfa > 0)
  {
	$haftadizi = array(1,2,3,4,5,6,7);
  foreach($result as $row)
  {

	$dizigunleri = explode(",", $row['haftanin_gunu']);

	// Hafta alanındaki Haftanın gününü gösterir
	if(is_array($dizigunleri) AND array_intersect($haftadizi, $dizigunleri)){             
	$haftaadi = array(1 => "Pazartesi","Salı","Çarşamba","Perşembe","Cuma","Cumartesi","Pazar");
	$haftaningunu = date('N', $row['sonraki_calisma']);;
	$hafta = $haftaadi[$haftaningunu];
	}else{
	$hafta = $row['haftanin_gunu'];
	}

	// Gün alanında günü gösterir
	if(is_array($dizigunleri) AND in_array('-1', $dizigunleri) AND $row['gun']>-1){
	  $gungun = $row['gun'];
	}
	if(is_array($dizigunleri) AND array_intersect($haftadizi, $dizigunleri)){
	  $gungun = "Haftalık";
	}                       
	if(is_array($dizigunleri) AND in_array('-1', $dizigunleri) AND $row['gun']=='-1'){
	  $gungun = "*";
	}

	$satirlar .= '
			<tr>
			  <td><div class="smallfont">'.near_date($row['sonraki_calisma']).'</div></td>
			  <td><div class="smallfont">'.$hafta.'</div></td>
			  <td><div class="smallfont">'.$row['gun'].'</div></td>
			  <td><div class="smallfont">'.$row['saat'].'</div></td>
			  <td><div class="smallfont">'.$row['dakika'].'</div></td>
			  <td><div class="smallfont">'.$row['dosya_adi'].'</div></td>
			  <td><div class="smallfont">'.$row['aktif'].'</div></td>
			  <td><div class="smallfont">'.$row['gunluk_kayit'].'</div></td>
			  <td><div class="smallfont">'.$row['gorev_adi'].'</div></td>
			  <td style="text-align: center;"><div class="smallfont"><a href="?edit='.$row['id'].'#edit"><span title="Görevi düzenlemek için tıkla" class="glyphicon glyphicon-edit"></span></a></div></td>
			  <td style="text-align: center;"><div class="smallfont"><a href="#" style="cursor: pointer;"><span data-name="'.strip_tags($row['gorev_adi']).'" id="veri_sil_'.$row['id'].'" title="Görevi silmek için tıkla" class="glyphicon glyphicon-remove"></span></a></div></td>
			  <td style="text-align: center;" onclick="simdiCalistir(\''.$row['gorev_adi'].'\',\''.$row['id'].'\');"><a href="#" title="Bu görevi şimdi çalıştır"><i class="fas fa-running"></i></a></td>
			</tr>
	';

  }
	}
  else
  {

  $satirlar .= '
	<tr>
	  <td colspan="12"><div align="center"><h5>ARANAN veya KAYITLI GÖREV MEVCUT DEĞİL</h5></div></td>
	</tr>
  ';

  }
  } // if($_POST['urun'] == "zamanlanmisgorev"){
############### GÖREV ZAMANLAMA YÖNETİMİ #######################################################################
//
//
############### GÖREV GÜNLÜK YÖNETİMİ ##########################################################################
if($_POST['urun'] == "zamanlanmisgorev_gunluk"){

	if(isset($_POST['per'])){
	  if($_POST['per']=='-1'){
		$limit = '99999999999';
	  }else{
		$limit = $_POST['per'];
	  }
	}
  else
  {
	$limit = '15';
  }
  
	$search_keyword = " '%%' ";

	// 2021-10-14
	// Bu bir tarih mi kontrol ediyoruz
	$tarih = isset($_POST['tarih']) ? $_POST['tarih'] : "";
	$dahil = '';
	function validateDate($date, $format = 'Y-m-d')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}
	if(validateDate($tarih)){
		$date = explode("-", $tarih);
		$gun_basla = mktime(00, 00, 00, $date[1], $date[2], $date[0]);
		$gun_bitir = mktime(23, 59, 59, $date[1], $date[2], $date[0]);
		$dahil = " AND calisma_zamani BETWEEN '".$gun_basla."' AND '".$gun_bitir."' ";
	}
	
	if(!empty($_POST['query'])) {
	  $search_keyword = $_POST['query'];
	
	// Çift tırnak " ile başlayan ve biten arama kelime
	$pattern = '/^".+"$/i';
	if(preg_match($pattern, $search_keyword))
	{
	  $searchkeyword = str_replace('"','',$search_keyword);
	  $search_keyword = " '$searchkeyword' ";
	// $search_keyword zaten gerekli değer
	}else{
	// sadece % joker karakterleri ekliyoruz
	$search_keyword = " '%$search_keyword%' ";
	}
	}


	$n = 4; // Kaç tane arama seçeneği var
	$search_params = array_fill($n, 0, $search_keyword);
  
  
	$sql = 'SELECT *
	  FROM zamanlanmisgorev_gunluk    
	  WHERE
	  (calistirma_ciktisi LIKE '.$search_keyword.' OR 
	  gorev_adi LIKE '.$search_keyword.' OR 
	  calistirilan_dosya LIKE '.$search_keyword.') '.$dahil.'
	  ORDER BY id DESC ';
	
	$page = 1;
	$start = 0;
	if(!empty($_POST["page"])) {
	  $page = $_POST["page"];
	  $start = ($page-1) * $limit;
	}
	$limit_code = " LIMIT " . $start . "," . $limit;
	$pagination_statement = $PDOdb->prepare($sql);
	//$pagination_statement->bindValue(':keyword', '%' . $search_keyword . '%', PDO::PARAM_STR);
	$pagination_statement->execute($search_params);
  
	$top_sayfa = $pagination_statement->rowCount();
  
	$query = $sql.$limit_code;
	$pdo_statement = $PDOdb->prepare($query);
	//$pdo_statement->bindValue(':keyword', '%' . $search_keyword . '%', PDO::PARAM_STR);
	$pdo_statement->execute($search_params);
	$result = $pdo_statement->fetchAll();
  
  
	$satirlar = '';
  
	if($top_sayfa > 0)
	{
	$css = 0;
	foreach($result as $row)
	{
  
		$class = ($css % 2) ? "alt1" : "alt2";
  
	  $satirlar .= '
				<tr>
				  <td class="'.$class.'"><div class="smallfont">'.near_date($row['calisma_zamani']).'</div></td>
				  <td class="'.$class.'"><div class="smallfont">'.$row['gorev_adi'].'</div></td>
				  <td class="'.$class.'"><div class="smallfont">'.$row['calistirilan_dosya'].'</div></td>
				  <td class="'.$class.'"><div class="smallfont">'.$row['calistirma_ciktisi'].'</div></td>
				  <td class="'.$class.'" style="text-align:right;padding-right:40px;"><div class="smallfont">'.$row['calisma_suresi'].'</div></td>
				  <td class="'.$class.'" style="text-align:right;padding-right:20px;"><div class="smallfont"><input type="checkbox" class="gunlukler" name="silid[]" value="'.$row['id'].'" title="Silmek için seçin" onclick="javascript:renk(this);" /></div></td>
				</tr>
	  ';
  
	$css++;
	}
	  }
	else
	{
  
	$satirlar .= '
	  <tr>
		<td colspan="6"><div align="center"><h5>ARANAN veya KAYITLI GÜNLÜK MEVCUT DEĞİL</h5></div></td>
	  </tr>
	';
  
	}
  } // if($_POST['urun'] == "zamanlanmisgorev_gunluk"){
############### GÖREV GÜNLÜK YÖNETİMİ ##########################################################################
//
//
//
############### ÜYE YÖNETİMİ ###################################################################################
if($_POST['urun'] == "users"){

	if(isset($_POST['per'])){
	  if($_POST['per']=='-1'){
		$limit = '99999999999';
	  }else{
		$limit = $_POST['per'];
	  }
	}
  else
  {
	$limit = '15';
  }
  
	$search_keyword = " '%%' ";
	
	if(!empty($_POST['query'])) {
	  $search_keyword = $_POST['query'];
	
	// Çift tırnak " ile başlayan ve biten arama kelime
	$pattern = '/^".+"$/i';
	if(preg_match($pattern, $search_keyword))
	{
	  $searchkeyword = str_replace('"','',$search_keyword);
	  $search_keyword = " '$searchkeyword' ";
	// $search_keyword zaten gerekli değer
	}else{
	// sadece % joker karakterleri ekliyoruz
	$search_keyword = " '%$search_keyword%' ";
	}
	}
	
	$n = 2; // Kaç tane arama seçeneği var
	$search_params = array_fill($n, 0, $search_keyword);
  
	$sql = 'SELECT * FROM uyeler WHERE 
	(user_name LIKE '.$search_keyword.' OR  
	user_email LIKE '.$search_keyword.') 
	ORDER BY user_id ASC ';
	
	$page = 1;
	$start = 0;
	if(!empty($_POST["page"])) {
	  $page = $_POST["page"];
	  $start = ($page-1) * $limit;
	}
	$limit_code = " LIMIT " . $start . "," . $limit;
	$pagination_statement = $PDOdb->prepare($sql);
	$pagination_statement->execute($search_params);
  
	$top_sayfa = $pagination_statement->rowCount();
  
	$query = $sql.$limit_code;
	$pdo_statement = $PDOdb->prepare($query);
	$pdo_statement->execute($search_params);
	$result = $pdo_statement->fetchAll();
  
  
  $satirlar = '';
  
  if($top_sayfa > 0) {

	foreach($result as $row) {

	// Son 10 giriş zamanını almak için SQL sorgusu
	$son_giris = '';
	$sql = "SELECT login_time, log_in_from FROM user_logins WHERE user_id = :user_id ORDER BY login_time DESC LIMIT 10";
	$stmt = $PDOdb->prepare($sql);
	$stmt->bindParam(':user_id', $row['user_id']);
	$stmt->execute();
	$login_times = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Giriş zamanlarını yazdırın veya istediğiniz gibi kullanın
	foreach ($login_times as $login) {
		if (!empty($login['login_time'])) {
			$login_time = (int)$login['login_time'];
			$formatted_date = near_date($login_time);
			$log_in_from = $login['log_in_from'];
			$son_giris .= '<option>' . $formatted_date . ' - ' . $log_in_from . '</option>';
		}
	}

	$satirlar .= "
		<tr>
			<td>{$row['user_id']}</td>
			<td>{$row['user_name']}</td>
			<td>{$row['user_email']}</td>
			<td>Admin</td>
			<td><select size='1' style='width:200px;'>".$son_giris."</select></td>
			<td style='text-align: center;'><a href='?edit=".$row['user_id']."#ed'><span title='Üyeyi düzenlemek için tıkla' class='glyphicon glyphicon-edit'></span></a></td>
			<td style='text-align: center;'><a href='#' style='cursor: pointer;'><span data-name='".$row['user_name']."' id='veri_sil_".$row['user_id']."' title='Üyeyi silmek için tıkla' class='glyphicon glyphicon-remove'></span></a></td>
		</tr>
	";
  
	  } // foreach($result as $row) {
   
	} // if($top_sayfa > 0) {
  else
  {
  $satirlar .= '
	<tr>
	  <td colspan="6"><div align="center"><h5>ARANAN veya KAYITLI ÜYE MEVCUT DEĞİL</h5></div></td>
	</tr>
	';
  }

  } // if($_POST['urun'] == "users"){
############### ÜYE YÖNETİMİ ###################################################################################
//
//
//

if(isset($top_sayfa)){
	require_once("pagination_linkler.php");
}
?>