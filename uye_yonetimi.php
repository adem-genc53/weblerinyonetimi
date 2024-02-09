<?php 
// Bismillahirrahmanirrahim
session_start();
require_once('includes/connect.php');
require_once('check-login.php');
require_once("includes/turkcegunler.php");
##########################################################################################################
    // Kayıtlı veri tabanı listeleme için
    $stmt = $PDOdb->prepare("SELECT * FROM uyeler");
    $stmt->execute();

    $ekle_hata = false;
##########################################################################################################
    // Veritabanı bilgileri silmek
    if(isset($_POST['veri_del']) && is_numeric($_POST['veri_del'])){
        $sil = $PDOdb->prepare("DELETE FROM uyeler WHERE user_id = ?");
        $sil->execute([$_POST['veri_del']]);
        if($sil->rowCount()){
            $messages[] = "Üye Bilgileri Başarıyla Silindi.";
            header("Refresh:2");
        }else{
            $errors[] = "Bir Hatadan Dolayı Üye Bilgileri Silinemedi. Tekrar Deneyin.";
        }
    }

##########################################################################################################
    // Yeni veritabanı bilgileri ekleme
    if(isset($_POST['yeni_ekle']) && $_POST['yeni_ekle'] == 1){

        if(empty($_POST['user_name'])){
            $errors[] = "Üye adı soyadı girmelisiniz";
        }else{
            $user_name       = $_POST['user_name'];
        }
        if(empty($_POST['user_email'])){
            $errors[] = "E-Posta adresi girmelisiniz";
        }elseif(!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)){
            $errors[] = "E-Posta adresi geçersiz";
        }else{
            $user_email      = $_POST['user_email'];
        }
        if(empty($_POST['user_group'])){
            $errors[] = "Grup seçmelisiniz";
        }else{
            $user_group      = $_POST['user_group'];
        }
        if(empty($_POST['user_password'])){
            $errors[] = "Şifre alanına şifre girmelisiniz";
        }else{
            $user_password_hash     = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
        }

    if(empty($errors)){
        try {
        $ftvtk = $PDOdb->prepare("INSERT INTO uyeler (
                user_name,
                user_email,
                user_group,
                user_password_hash) 
                VALUES (
                :user_name,
                :user_email,
                :user_group,
                :user_password_hash
                )");
                $ftvtk->bindParam(':user_name', $user_name, PDO::PARAM_STR);
                $ftvtk->bindParam(':user_email', $user_email, PDO::PARAM_STR);
                $ftvtk->bindParam(':user_group', $user_group, PDO::PARAM_STR);
                $ftvtk->bindParam(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
                $ftvtk->execute();

		if($PDOdb->lastInsertId()){
		    $messages[] = "Üye Bilgileri Başarıyla Eklendi.";
            header("Refresh:2");
		}else{
            $errors[] = "Bir Hatadan Dolayı Üye Bilgileri Eklenemedi. Tekrar Deneyin.";
        }

        } catch (PDOException $e) {
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Eklemeye çalıştığınız Üye Bilgileri veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
            }
        }
    }else{
        $ekle_hata = true;
    }

    }
##########################################################################################################
    // Veritabanı bilgileri güncelleme
    if(isset($_POST['guncelle']) && $_POST['guncelle'] == 1){

        if(empty($_POST['user_name'])){
            $errors[] = "Üye adı soyadı girmelisiniz";
        }else{
            $user_name       = $_POST['user_name'];
        }
        if(empty($_POST['user_email'])){
            $errors[] = "E-Posta adresi girmelisiniz";
        }elseif(!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)){
            $errors[] = "E-Posta adresi geçersiz";
        }else{
            $user_email      = $_POST['user_email'];
        }
        if(empty($_POST['user_group'])){
            $errors[] = "Grup seçmelisiniz";
        }else{
            $user_group      = $_POST['user_group'];
        }
        if(isset($_POST['user_password']) && !empty($_POST['user_password'])){
            $user_password_hash     = password_hash($_POST['user_password'], PASSWORD_DEFAULT);
            $user_password          = 'user_password_hash=:user_password_hash,';
        }else{
            $user_password          = "";
        }

        $edit_id            = $_POST['edit_id'];

    if(empty($errors)){
        try {
        $ftvtk = $PDOdb->prepare("UPDATE uyeler SET
                $user_password
                user_name=:user_name,
                user_email=:user_email,
                user_group=:user_group
                WHERE user_id=:user_id");

                if(isset($_POST['user_password']) && !empty($_POST['user_password'])){
                $ftvtk->bindParam(':user_password_hash', $user_password_hash, PDO::PARAM_STR);
                }
                $ftvtk->bindParam(':user_name', $user_name, PDO::PARAM_STR);
                $ftvtk->bindParam(':user_email', $user_email, PDO::PARAM_STR);
                $ftvtk->bindParam(':user_group', $user_group, PDO::PARAM_STR);
                $ftvtk->bindParam(':user_id', $edit_id, PDO::PARAM_INT);
                $ftvtk->execute();

		if($ftvtk->rowCount()){
		    $messages[] = "Üye Bilgileri Başarıyla Guncellendi.";
            header("Refresh: 2; url=".htmlspecialchars($_SERVER["PHP_SELF"])."?");
		}else{
            $errors[] = "Bir Hatadan Dolayı Üye Bilgileri Güncellenemedi. Tekrar Deneyin.<br />Veya hiçbir değişiklik yapmadan güncelliyorsunuz";
        }

        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Güncellemeye çalıştığınız Üye Bilgileri veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
                $errors[] = $e->getMessage();
            }
        }

    }

    }
##########################################################################################################
include('includes/header.php');
include('includes/navigation.php');
include('includes/sub_navbar.php');
?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Web Siteler Yönetimi</h1>
                        </div><!-- / <div class="col-sm-6"> -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="index.php">Anasayfa</a></li>
                                <li class="breadcrumb-item active">Üye Yönetimi</li>
                            </ol>
                        </div><!-- / <div class="col-sm-6"> -->
                    </div><!-- / <div class="row mb-2"> -->
                </div><!-- / <div class="container-fluid"> -->
            </div><!-- / <div class="content-header"> -->

<?php 
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                //echo '<pre>' . print_r($_POST, true) . '</pre>';
            }
            if (isset($errors)) {
                echo "<div class='uyari'>";
                echo "<span title='Kapat' class='closebtn'>&times;</span>";
                echo "<span class='baslik'><span class='glyphicon glyphicon-ban-circle'></span></span><br />";
                foreach ($errors AS $error) {
                    echo $error."<br />";
                }
                echo "</div>";
            }
            if (isset($messages)) {
                echo "<div class='uyari success'>";
                echo "<span title='Kapat' class='closebtn'>&times;</span>";
                echo "<span class='baslik'><span class='glyphicon glyphicon-ok'></span></span><br />";                
                foreach ($messages AS $message) {
                    echo $message."</strong>";
                }
                    echo "</div>";
            }
?>

    <!-- Bilgilendirme Satırı Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
                    <!-- Bilgilendirme bölümü -->
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                            <h5 class="m-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                Üye Ekle / Düzenle / Sil Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Buradan yeni üye eklebilir, düzenleyebilir veya silebilirsiniz</p>
                                <p>Bu siteye giriş izni vereceğiniz üyeleri buradan ekleyebilirsiniz</p>
                            </div>
                            </div>
                        </div><!-- / <div class="card"> -->
                    </div><!-- / <div id="accordion"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Bilgilendirme Satırı Sonu -->


<?php 
    if(isset($_GET['edit']) && is_numeric($_GET['edit'])){
    $edit = $PDOdb->prepare(" SELECT * FROM uyeler WHERE user_id=? ");
    $edit->execute([$_GET['edit']]);
    $row = $edit->fetch();
?>

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">
                        <a name="ed" id="ed" style="scroll-margin-top: 50px;"></a>
                            <form name="edit" id="edit" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                            <input type="hidden" name="edit_id" value="<?php echo $row['user_id']; ?>">
                                            <table class="table" style="min-width: 1000px;">
                                            <colgroup span="3">
                                                <col style="width:20%"></col>
                                                <col style="width:30%"></col>
                                                <col style="width:50%"></col>
                                            </colgroup>
                                                <thead>
                                                    <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                                        <th colspan="3" style="text-align: center;">Üye Düzelt</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Üye Adı Soyadı: </td>
                                                        <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;"><input type="text" class="form-control" name="user_name" id="user_name" value="<?php if(isset($_POST['user_name'])){ echo $_POST['user_name']; }else{ echo $row['user_name']; } ?>" placeholder="Üye adı soyadı" /></td>
                                                        <td>Üyeyi tanımlamak için adı soyadı</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Üyeni E-Posta Adresi: </td>
                                                        <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="user_email" id="user_email" value="<?php if(isset($_POST['user_email'])){ echo $_POST['user_email']; }else{ echo $row['user_email']; } ?>" placeholder="Üye e-posta adresi" /></td>
                                                        <td>Giriş yaparken kullanıcı adı olarak kullanacağı e-posta adresi</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Üyenin Grubu: </td>
                                                        <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                            <select size="1" name="user_group" class="form-control" />
                                                                <option value="1">Admin</option>
                                                            </select>
                                                        </td>
                                                        <td>Admin, Moderatör gibi üye grubu</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Giriş Şifresi: </td>
                                                        <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="user_password" id="user_passwordedit" placeholder="Değiştirmiyorsanız boş bırakın" /></td>
                                                        <td>Girişte kullanılacak şifre</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3" style="text-align:center;">
                                                        <button type="submit" value="1" name="guncelle" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-repeat"></span> Değişiklikleri Kaydet </button> 
                                                        <button type="reset" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </form>

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->

<?php }else{ ?>

    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

                                            <table class="table table-sm table-striped table-hover yukleniyor" id="gvUsers" style="min-width: 1000px;">
                                            <colgroup span="6">
                                                <col style="width:5%"></col>
                                                <col style="width:45%"></col>
                                                <col style="width:20%"></col>
                                                <col style="width:20%"></col>
                                                <col style="width:5%"></col>
                                                <col style="width:5%"></col>
                                            </colgroup>
                                                 <thead>
                                                    <tr class="bg-primary" style="line-height: 1.2;font-size: 1rem;">
                                                        <th>ID</th>
                                                        <th>Adı Soyadı</th>
                                                        <th>E-Posta Adresi</th>
                                                        <th>Grup</th>
                                                        <th>Düzelt</th>
                                                        <th>Sil</th>
                                                    </tr>
                                                </thead>
                                            <form method="POST">
                                                <tbody id="satirlar">
                                                    <tr>
                                                        <td class="ilk-yukleniyor" colspan="6">&nbsp;</td>
                                                    </tr>
                                                </tbody>
                                            </form>
                                            <tfoot>
                                                <tr>
                                                <td colspan="6"><div style="width:50%; display:inline-block;"><div id="linkler"></div></div><div style="width:50%; display:inline-block;">
                                            <div style="float:right;">
                                                <input type="text" autocomplete="off" id="search" list="aranacaklar" placeholder="İçerik Ara / Çift Tıkla"> Sayfada
                                                
                                                <select name="sayfada" id="sayfada">
                                                    <option value="5">5</option>
                                                    <option value="15" selected>15</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                    <option value="250">250</option>
                                                    <option value="500">500</option>
                                                    <option value="999">999</option>
                                                    <option value="-1">Hepsi</option>
                                                </select> Satır Göster
                                                </div></div></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="6" style="text-align:right;">
                                                        <button id="eklebuton" class="btn btn-success btn-sm"> <span class="glyphicon glyphicon-plus"></span> Üye Ekle </button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                            </table>
                                        

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->

<?php } ?>




                            
    <!-- Gövde İçerik Başlangıcı -->
    <section class="content" id="ekle" style="display:none;">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">
                    <a name="tbliste" id="tbliste" style="scroll-margin-top: 50px;"></a>
                                        <form name="ekle" id="ekle" method="POST" action="uye_yonetimi.php">
                                            <table class="table" style="min-width: 1000px;">
                                            <colgroup span="3">
                                                <col style="width:20%"></col>
                                                <col style="width:30%"></col>
                                                <col style="width:40%"></col>
                                            </colgroup>
                                                <thead>
                                                    <tr class="bg-primary" style="line-height: .40;font-size: 1rem;">
                                                        <th colspan="3" style="text-align: center;">Yeni Üye Ekle</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Üye Adı Soyadı: </td>
                                                        <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;"><input type="text" class="form-control" name="user_name" id="user_name" value="<?php if(isset($_POST['user_name'])){ echo $_POST['user_name']; } ?>" placeholder="Üye adı soyadı" /></td>
                                                        <td>Üyeyi tanımlamak için adı soyadı</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Üyeni E-Posta Adresi: </td>
                                                        <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="user_email" id="user_email" value="<?php if(isset($_POST['user_email'])){ echo $_POST['user_email']; } ?>" placeholder="Üye e-posta adresi" /></td>
                                                        <td>Giriş yaparken kullanıcı adı olarak kullanacağı e-posta adresi</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Üyenin Grubu: </td>
                                                        <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;">
                                                            <select size="1" name="user_group" class="form-control" />
                                                                <option value="1">Admin</option>
                                                            </select>
                                                        <td>Admin, Moderatör gibi üye grubu</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Giriş Şifresi: </td>
                                                        <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="user_password" value="<?php if(isset($_POST['user_password'])){ echo $_POST['user_password']; } ?>" id="user_passwordedit" placeholder="Şifre" /></td>
                                                        <td>Girişte kullanılacak şifre</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="3" style="text-align:center;">
                                                            <button type="submit" name="yeni_ekle" value="1" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Ekle </button> 
                                                            <button type="reset" value="1" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </form>

                </div><!-- / <div class="card-body p-0"> -->
            </div><!-- / <div class="card"> -->
        </div><!-- / <div class="col-sm-12"> -->
        </div><!-- / <div class="row mb-2"> -->
    </div><!-- / <div class="container-fluid"> -->
    </section><!-- / <section class="content"> -->
    <!-- Gövde İçerik Sonu -->
                            
<form id="gonder" method="POST">
    <input type="hidden" name="veri_del" id="veri_del">
</form>
<script>
<?php 
    if($ekle_hata){
        echo '$("#ekle").show();';
    }
?>

    $(document).on('click', "[id^=veri_sil_]", function(){
            var id = $(this).attr('id');
            id = id.replace("veri_sil_",'');
            $("#veri_del").val(id);
            var veri_adi = $(this).attr('data-name');

        $(function()
            {
            jw('b secim',sil_dur).baslik("Üye Silmeyi Onayla").icerik("<b>" + veri_adi + "</b> üyeyi silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
            })
        return false;

        function sil_dur(x){
            if(x==1){
                $("#gonder").submit();
            }
        }

    });
</script>

<br />
        </div><!-- / <div class="content-wrapper"> -->
        
<script type='text/javascript'>
    var satir = "users";
    var query = '';
    var tarih = '';
    var firma = '';
</script>
<?php 
include('includes/footer.php');
?>
