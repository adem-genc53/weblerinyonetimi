<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';
require_once __DIR__ . '/hash.php';
$hash = new Hash;
##########################################################################################################
    // Kayıtlı veri tabanı listeleme için
    $stmt = $PDOdb->prepare("SELECT * FROM veritabanlari");
    $stmt->execute();
    //echo '<pre>' . print_r($_POST, true) . '</pre>';
##########################################################################################################
$errors = [];
    // Veritabanı bilgileri silmek
    if(isset($_POST['veri_del']) && is_numeric($_POST['veri_del'])){
        $sil = $PDOdb->prepare("DELETE FROM veritabanlari WHERE id = ?");
        $sil->execute([$_POST['veri_del']]);
        if($sil->rowCount()){
            $messages[] = "Veritabanı Bilgileri Başarıyla Silindi.";
            header("Refresh:2");
        }else{
            $errors[] = "Bir Hatadan Dolayı Veritabanı Bilgileri Silinemedi. Tekrar Deneyin.";
        }
    }
##########################################################################################################

##########################################################################################################
    if(isset($_POST['website_name']) && empty($_POST['website_name'])){
        $errors[] = "Web site adı boş olamaz";
    }
    if(isset($_POST['database_host']) && empty($_POST['database_host'])){
        $errors[] = "Host adı boş olamaz";
    }
    if(isset($_POST['db_name']) && empty($_POST['db_name'])){
        $errors[] = "Veri tabanı adı boş olamaz";
    }
    if(isset($_POST['database_user']) && empty($_POST['database_user']) && isset($_POST['yeni_ekle']) && $_POST['yeni_ekle'] == 1){
        $errors[] = "Veri tabanı kullanıcı adı boş olamaz";
    }
    if(isset($_POST['database_password']) && empty($_POST['database_password']) && isset($_POST['yeni_ekle']) && $_POST['yeni_ekle'] == 1){
        $errors[] = "Veri tabanı şifre boş olamaz";
    }
    
    // Yeni veritabanı bilgileri ekleme
    if(isset($_POST['yeni_ekle']) && $_POST['yeni_ekle'] == 1 && empty($errors)){

        $website_name       = $_POST['website_name'];
        $database_host      = str_replace(' ', '', $_POST['database_host']);
        $db_name            = str_replace(' ', '', $_POST['db_name']);
        $port               = str_replace(' ', '', $_POST['port']);
        $database_user      = $hash->make(str_replace(' ', '', $_POST['database_user'])); // Veri Tabanı kullanıcı adını şifrele
        if(isset($_POST['database_password']) && empty($_POST['database_password'])){
        $database_password  = "";
        }else{
        $database_password  = $hash->make(str_replace(' ', '', $_POST['database_password'])); // Veri Tabanı şifresini tekrar şifrele
        }
        
        try {
        $ftvtk = $PDOdb->prepare("INSERT INTO veritabanlari (
                website_name,
                database_host,
                db_name,
                database_user,
                database_password,
                port) 
                VALUES (
                :website_name,
                :database_host,
                :db_name,
                :database_user,
                :database_password,
                :port
                )");
                $ftvtk->bindParam(':website_name', $website_name, PDO::PARAM_STR);
                $ftvtk->bindParam(':database_host', $database_host, PDO::PARAM_STR);
                $ftvtk->bindParam(':db_name', $db_name, PDO::PARAM_STR);
                $ftvtk->bindParam(':database_user', $database_user, PDO::PARAM_STR);
                $ftvtk->bindParam(':database_password', $database_password, PDO::PARAM_STR);
                $ftvtk->bindParam(':port', $port, PDO::PARAM_INT);
                $ftvtk->execute();

		if($PDOdb->lastInsertId()){
		    $messages[] = "Veritabanı Bilgileri Başarıyla Eklendi.";
            header("Refresh:2");
		}else{
            $errors[] = "Bir Hatadan Dolayı Veritabanı Bilgileri Eklenemedi. Tekrar Deneyin.";
        }

        } catch (PDOException $e) {
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Eklemeye çalıştığınız Veritabanı Bilgileri veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
            }
        }

    }
##########################################################################################################
    // Veritabanı bilgileri güncelleme
    if(isset($_POST['guncelle']) && $_POST['guncelle'] == 1 && empty($errors)){
        $website_name       = $_POST['website_name'];
        $database_host      = str_replace(' ', '', $_POST['database_host']);
        $db_name            = str_replace(' ', '', $_POST['db_name']);
        $port               = str_replace(' ', '', $_POST['port']);

        if(isset($_POST['database_user']) && empty($_POST['database_user'])){
        $databaseuser = "";
        }else{
        $database_user      = $hash->make(str_replace(' ', '', $_POST['database_user'])); // Veri Tabanı kullanıcı adını şifrele
        $databaseuser = 'database_user=:database_user,';
        }

        if(isset($_POST['database_password']) && empty($_POST['database_password'])){
        $databasepassword  = "";
        }else{
        $database_password  = $hash->make(str_replace(' ', '', $_POST['database_password'])); // Veri Tabanı şifresini tekrar şifrele
        $databasepassword  = 'database_password=:database_password,';
        }

        $edit_id            = $_POST['edit_id'];

        try {
        $ftvtk = $PDOdb->prepare("UPDATE veritabanlari SET
                $databaseuser
                $databasepassword
                website_name=:website_name,
                database_host=:database_host,
                db_name=:db_name,
                port=:port
                WHERE id=:id");

                if(isset($_POST['database_user']) && !empty($_POST['database_user'])){
                $ftvtk->bindParam(':database_user', $database_user, PDO::PARAM_STR);
                }

                if(isset($_POST['database_password']) && !empty($_POST['database_password'])){
                $ftvtk->bindParam(':database_password', $database_password, PDO::PARAM_STR);
                }

                $ftvtk->bindParam(':website_name', $website_name, PDO::PARAM_STR);
                $ftvtk->bindParam(':database_host', $database_host, PDO::PARAM_STR);
                $ftvtk->bindParam(':db_name', $db_name, PDO::PARAM_STR);
                $ftvtk->bindParam(':port', $port, PDO::PARAM_INT);
                $ftvtk->bindParam(':id', $edit_id, PDO::PARAM_INT);
                $ftvtk->execute();

		if($ftvtk->rowCount()){
		    $messages[] = "Veritabanı Bilgileri Başarıyla Guncellendi.";
            //header("Refresh:2");
            //header("Location: ".$_SERVER['PHP_SELF']);
		}else{
            $errors[] = "Bir Hatadan Dolayı Veritabanı Bilgileri Güncellenemedi. Tekrar Deneyin.<br />Veya hiçbir değişiklik yapmadan güncelliyorsunuz";
        }

        } catch (PDOException $e) {
            $errors[] = $e->getMessage();
            $existingkey = "Integrity constraint violation: 1062 Duplicate entry";
            if (strpos($e->getMessage(), $existingkey) !== FALSE) {
                $errors[] = "Güncellemeye çalıştığınız Veritabanı Bilgileri veritabanında zaten kayıtlıdır";
            } else {
                throw $e;
                $errors[] = $e->getMessage();
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
                                <li class="breadcrumb-item active">Yeni Veri Tabanı Ekle / Seç</li>
                            </ol>
                        </div><!-- / <div class="col-sm-6"> -->
                    </div><!-- / <div class="row mb-2"> -->
                </div><!-- / <div class="container-fluid"> -->
            </div><!-- / <div class="content-header"> -->
<?php 

            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                //echo '<pre>' . print_r($_POST, true) . '</pre>';
            }
            if (isset($errors) && !empty($errors)) {
                echo "<div class='uyari'>";
                echo "<span title='Kapat' class='closebtn'>&times;</span>";
                echo "<span class='baslik'><span class='glyphicon glyphicon-ban-circle'></span></span><br />";
                foreach ($errors as $error) {
                    echo $error."<br />";
                }
                echo "</div>";
            }
            if (isset($messages)) {
                echo "<div class='uyari success'>";
                echo "<span title='Kapat' class='closebtn'>&times;</span>";
                echo "<span class='baslik'><span class='glyphicon glyphicon-ok'></span></span><br />";                
                foreach ($messages as $message) {
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
                                Veritabanı Ekle / Düzenle / Sil / Seç Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Web yönetiminde yöneteceğiniz web sitelerin veritabanı bilgilerini ekleyiniz</p>
                                <b>Veritabanı yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(BACKUPDIR)); ?></span><br />
                                <p><b>Web site zip yedeklerin bulunduğu dizin: </b><span id="yol"><?php echo strtolower(htmlpath(ZIPDIR)); ?></span></p>
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
    $edit = $PDOdb->prepare(" SELECT * FROM veritabanlari WHERE id=? ");
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

                    <form name="edit" id="edit" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                        <a name="ed" id="ed" style="scroll-margin-top: 50px;"></a>
                        <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">

                        <table class="table table-striped table-hover" style="min-width: 1000px;">
                        <colgroup span="3">
                            <col style="width:20%"></col>
                            <col style="width:30%"></col>
                            <col style="width:50%"></col>
                        </colgroup>
                            <thead>
                                <tr class="bg-primary" style="text-align: center;line-height: .30;font-size: 1rem;">
                                    <th colspan="3">Veri Tabanı Bilgileri Güncelle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Website Adı: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;"><input type="text" class="form-control" name="website_name" id="website_name" value="<?php echo $row['website_name']; ?>" placeholder="Web site adı" /></td>
                                    <td>Bu veritabanı hangı websiteye ait olduğunu belirlemek içindir</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Host Adı: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="database_host" id="database_host" value="<?php echo $row['database_host']; ?>" placeholder="Host adı" /></td>
                                    <td>Veritabanının host adını girin. Çoğunlukla "localhost" kullanılmaktadır</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Port: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="port" id="port" value="<?php echo $row['port']; ?>" placeholder="Port" /></td>
                                    <td>Veritabanı port genellikle 3306 dır. MySQL ve MariaDB ikisi kurulu ise MariaDB için 3307 olabilir.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Adı: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="db_name" id="db_name" value="<?php echo $row['db_name']; ?>" placeholder="Veritabanı adı" /></td>
                                    <td>Veritabanının adını girin.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Kullanıcı Adı: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="database_user" id="database_useredit" placeholder="Değiştirmiyorsanız boş bırakın" /></td>
                                    <td>Veritabanına bağlanma kullanıcı adını girin</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Şifre: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="database_password" placeholder="Değiştirmiyorsanız boş bırakın" /></td>
                                    <td>Veritabanına bağlanma şifreyi girin</td>
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

                    <table class="table table-sm table-striped table-hover" style="min-width: 1000px;">
                    <colgroup span="5">
                        <col style="width:40%"></col>
                        <col style="width:20%"></col>
                        <col style="width:20%"></col>
                        <col style="width:5%"></col>
                        <col style="width:5%"></col>
                    </colgroup>
                            <thead>
                            <tr class="bg-primary" style="line-height: 1.2;font-size: 1rem;">
                                <th>Website Adı</th>
                                <th>Host Adı</th>
                                <th>Veri Tabanı Adı</th>
                                <th>Düzelt</th>
                                <th>Sil</th>
                            </tr>
                        </thead>
                            <?php 
                            while($row = $stmt->fetch())
                            {
                                echo "<tr>";
                                echo "<td>{$row['website_name']}</td>";
                                echo "<td>{$row['database_host']}</td>";
                                echo "<td>{$row['db_name']}</td>";
                                echo "<td style='text-align: center;'><a href='?edit=".$row['id']."#ed'><span title='Görevi düzenlemek için tıkla' class='glyphicon glyphicon-edit'></span></a></td>";
                                echo "<td style='text-align: center;'><a href='#'><span data-name='".strip_tags($row['website_name'])."' id='veri_sil_".$row['id']."' title='Web site bilgileri silmek için tıkla' class='glyphicon glyphicon-remove'></span></a></td>";
                                echo "</tr>";
                            }
                            ?>    
                        <tfoot>
                            <tr>
                                <td colspan="5" style="text-align:right;">
                                <button id="eklebuton" class="btn btn-success btn-sm">
                                <span class="glyphicon glyphicon-plus"></span> Veritabanı Ekle
                                </button>
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

                    <form name="ekle" id="ekle" method="POST" action="db_bilgileri.php">
                        <a name="tbliste" id="tbliste" style="scroll-margin-top: 50px;"></a>
                        <table class="table table-striped table-hover" style="min-width: 1000px;">
                        <colgroup span="3">
                            <col style="width:20%"></col>
                            <col style="width:30%"></col>
                            <col style="width:50%"></col>
                        </colgroup>
                            <thead>
                                <tr class="bg-primary" style="text-align: center;line-height: .30;font-size: 1rem;">
                                    <th colspan="3">Yeni Veri Tabanı Bilgileri Ekle</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Website Adı: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;min-width: 200px;"><input type="text" class="form-control" name="website_name" id="website_name" placeholder="Web site adı" /></td>
                                    <td>Bu veritabanı hangı websiteye ait olduğunu belirlemek içindir</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Host Adı: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="database_host" id="database_host" placeholder="Host adı" /></td>
                                    <td>Veritabanının host adını girin. Çoğunlukla "localhost" kullanılmaktadır</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Port: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="port" id="port" placeholder="Port" /></td>
                                    <td>Veritabanı port genellikle 3306 dır. MySQL ve MariaDB ikisi kurulu ise MariaDB için 3307 olabilir.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Adı: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="db_name" id="db_name" placeholder="Veritabanı adı" /></td>
                                    <td>Veritabanının adını girin.</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Kullanıcı Adı: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="database_user" id="database_useredit" placeholder="Veritabanı kullanıcı adı" /></td>
                                    <td>Veritabanına bağlanma kullanıcı adını girin</td>
                                </tr>
                                <tr>
                                    <td>Veritabanı Şifre: </td>
                                    <td style="padding: 0rem 0.75rem 0rem 0.75rem;vertical-align: middle;"><input type="text" class="form-control" name="database_password" placeholder="Veritabanı şifre" /></td>
                                    <td>Veritabanına bağlanma şifreyi girin</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align:center;">
                                        <button type="submit" name="yeni_ekle" value="1" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Ekle </button> 
                                        <button type="reset" name="database_ekle-" value="1" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-erase"></span> Sıfırla </button>
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

    $(document).on('click', "[id^=veri_sil_]", function(){
        var id = $(this).attr('id');
        id = id.replace("veri_sil_",'');
        $("#veri_del").val(id);
        var veri_adi = $(this).attr('data-name');

      $(function()
        {
        jw('b secim',sil_dur).baslik("Silmeyi Onayla").icerik("<b>" + veri_adi + "</b> web site bilgilerini silmek istediğinizden emin misiniz?").en(450).kilitle().ac();
        })
      return false;

      function sil_dur(x){
        if(x==1){
      $(function()
        {
            $("#gonder").submit();
        })
        }
      }

    });
</script>

        </div><!-- / <div class="content-wrapper"> -->
        
<script type='text/javascript'>
    var satir = '';
    var query = '';
    var tarih = '';
    var firma = '';
</script>
<?php 
include('includes/footer.php');
?>