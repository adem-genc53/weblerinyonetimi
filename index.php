<?php 
// Bismillahirrahmanirrahim
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/check-login.php';
require_once __DIR__ . '/includes/turkcegunler.php';

##########################################################################################################
    //echo '<pre>' . print_r($gorevler, true) . '</pre>';
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
                                <li class="breadcrumb-item active">Anasayfa</li>
                            </ol>
                        </div><!-- / <div class="col-sm-6"> -->
                    </div><!-- / <div class="row mb-2"> -->
                </div><!-- / <div class="container-fluid"> -->
            </div><!-- / <div class="content-header"> -->

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
                                Web Siste Yönetimi Hakkında Bilmeniz Gerekenler !
                                </button>
                            </h5>
                            </div>

                            <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">
                                <p>Bir veya birden fazla web siteniz varsa zaman zaman veritabanı yedeği alınması gerekir, bunun bir çok sebebi olabilir, istemeden yapacağınız bir yanlış geri dönülmesi sağlayacağı gibi sitenize başkaları tarafından saldırıda veritabanındaki verilerin silinmesi gibi durumlarda sitenizin geri getirilmesini sağlar.
                                </p>
                                <p>Ayrıca web sitelerinin dizinleride yedeklenmesi de önemlidir, web site dosyalarına zararlı yazılımların bulaşması durumunda zararlı web site olarak etiketlenmeniz ziyaretçileriniz web sitenize erişimi engelenmiş olacaktır, bu zararlı yazılıamların dosyalardan ayıklanması çok zor olduğu gibi imkansızda olabilir, bu gibi durumlarda hızlı bir şekilde web sitelerin geri getirilmesi için web sitelerin dizinlerini zip formatında sıkıştırıp yedeklemek gerekir, bu sayede sitenizin geri getirilmesini sağlar.
                                </p>
                                <p>Ancak bu yedeklerin bu hosting dışında başka yerlerde yedeklenmesi çok daha önemlidir. Bu script başka bir <strong>FTP/Hosting</strong> hesabınız varsa FTP yolu ile otomatikman yedekleme yapar. Diğer yedekleme yöntemi ise <strong>Google Drive IP Service Account</strong> ile yedekleme yöntemidir. Görev Zamanlama alanında zamanlayacağınız görevler zaman geldiğinde otomatikman yedekleme yaparak uzak sunuculara yedekleme yapar.
                                </p>
                                <p>Uzak sunucudan veya bu hosting üzerinde yedeklenen yedeklerin URL ile doğrudan ulaşılması mümkün değil, FTP hesabı için FileZila FTP programı ile ulaşmak mümkündür. <strong>Google Drive IP</strong> hesabı ise bu script içinde <strong>Uzak Bulut - Yedekle/indir</strong> alanını kullanarak yedeklerin bu hosting alanına indirmek mümkündür.
                                </p>
                                <p>Tüm uzak sunucuda yedeklenen yedekler bu script içinde&nbsp;<strong>Uzak Bulut - Yedekle/indir</strong> aalanında bu hosting alanına hızlıca indirebilir ve web sitelerinizin geri getirilmesini sağlayabilirsiniz.
                                </p>
                                <p><b>ÖNEMLİ NOT:</b> sitenizde riskli değişiklikler ve ayarlamalar yapmadan önce veritabanın yedeğini almanız şiddetle önerilir.
                                </p>
                                <p>&nbsp;
                                </p>
                                <p><strong>BİR HOSTİNG ÜZERİNDEN BİR VEYA BİRDEN FAZLA WEB SİSTELERİN OLUŞTURULMASI İÇİN ÖNERİLEN YERLEŞİM:</strong>
                                </p>
                                <p>Hosting kiralanırken belirlenen domain zorunlu olarak &quot;<strong>public_html</strong>&quot; alanın kullanılması gerekiyor ancak bu domaine ait alt-domainler veya eklenecek diğer domainler için aşağıdaki gibi yerleşimi öneriyoruz.
                                </p>
                                <p><strong>/home/user/</strong>
                                <br />
                                ----<strong>public_html</strong> (bu ana domain web sitenin klasörüdür)
                                <br />
                                ----<strong>alt-domain.com</strong> (bu alt-domain.com web sitenin klasörüdür)
                                <br />
                                ----<strong>website2.com</strong> (bu website2.com web sitenin klasörüdür)
                                <br />
                                ----<strong>website3.com</strong> (bu website3.com web sitenin klasörüdür)
                                <br />
                                ----<strong>website4.com</strong> (bu website4.com web sitenin klasörüdür)
                                <br />
                                ----<strong>websiteler_yonetimi.domain.com</strong> (bu scriptin klasörüdür. (ister alt ister harici domain kullanabilirsiniz) )
                                <br />
                                ----<strong>VERITABANI_YEDEKLERI</strong> (bu scriptin yedekleyeceği veritabanı yedeklerin depolanacağı klasörüdür)
                                <br />
                                ----<strong>WEBSITE_ZIP_YEDEKLER</strong> (bu scriptin yedekleyeceği web site klasörlerin zip dosyaların depolanacağı klasörüdür)
                                </p>
                                <p>&nbsp;
                                </p>
                                <p><strong>ZAMANLANMIŞ GÖREVLERİN TAM ZAMANINDA NASIL ÇALIŞIR?</strong>
                                </p>
                                <p>Zamanlanmış görevlerin zamanı gelen görevin çalışması için web site yönetimi sitenin birileri tarafından ziyaret edilmesi gerekiyor ki gorev.php çalışsın ki zamanı gelen görevleri yerine getirsin.
                                </p>
                                <p>Zamanın gelen görev siteyi kimse ziyaret etmediği için görev yerine getirilmediğinde ilk ziyaret edilmesi sırasında gecikmeli olarak zaman geçen görev yerine getirilir.
                                </p>
                                <p>Eğer görevlerin tam zamanında yerine getirilmesini istiyorsanız &quot;<strong>Cron İşleri</strong>&quot; kullanmanız gerekir.
                                </p>
                                <p><strong>cPanele</strong> Giriş yapın &gt; <strong>Cron Jobs/Cron İşleri</strong> tıklayın <strong>Ortak Ayarlar</strong> alanında görevlerinize uygun bir zaman seçin <strong>Komut:</strong> alanına "<b>/usr/local/bin/php <?php echo $_SERVER['DOCUMENT_ROOT']; ?>/gorev.php</b>>/dev/null 2>&1" girip kaydedin (<em>domain adını düzenlemeyi unutmayın</em>)
                                </p>
                                <p>Artık siteyi kimse tarafında ziyaret edilmese bile <strong>Cron İşleri</strong>nde belirlediğiniz zaman dilimlerinde gorev.php dosyayı çalıştıracak ve zamanı gelen görevler zamanında yerine gitirilecektir.&nbsp;
                                </p>
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



    <!-- Gövde İçerik Başlangıcı -->
    <section class="content">
    <div class="container-fluid">
        <div class="row mb-2">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body p-0">

                <table class="table table-sm table-striped table-hover" style="min-width: 1000px;">
                    <colgroup span="6">
                        <col style="width:25%"></col>
                        <col style="width:25%"></col>
                        <col style="width:25%"></col>
                        <col style="width:25%"></col>
                    </colgroup>
                    <thead>
                        <tr class="bg-primary" style="line-height: 1.2;font-size: 1rem;">
                            <th colspan="4" style="text-align: center;">GÖREVE GÖRE EN SON ÇALIŞTIRILAN GÖREVLER</th>
                        </tr>
                    </thead>
                    <thead>
                        <tr class="bg-primary" style="line-height: 1.2;font-size: 1rem;">
                            <th>Çalıştığı Zaman</th>
                            <th>Görev Adı</th>
                            <th>Çalıştırılan Dosya</th>
                            <th>Çalışma Sonucu</th>
                        </tr>
                    </thead>
                    <tbody>
<?php 
        $son_gorevler = $PDOdb->prepare("SELECT calisma_zamani, gorev_adi, calistirilan_dosya, calistirma_ciktisi, id
            FROM zamanlanmisgorev_gunluk
            WHERE id IN(SELECT MAX(id) FROM zamanlanmisgorev_gunluk GROUP BY gorev_adi) ORDER BY calisma_zamani DESC");
        $son_gorevler->execute();
        $son_gorevler_arr = $son_gorevler->fetchAll();

        //echo '<pre>' . print_r($son_gorevler_arr, true) . '</pre>';
        
        if(count($son_gorevler_arr)){
            foreach($son_gorevler_arr AS  $row){
                echo '<tr><td>'.near_date($row['calisma_zamani']).'</td><td>'.$row['gorev_adi'].'</td><td>'.$row['calistirilan_dosya'].'</td><td>'.$row['calistirma_ciktisi'].'</td></tr>';
            }
        }else{
            echo '<tr><td colspan="4" style="text-align: center;">ŞİMDİLİK HERHANGİ BİR YERİNE GETİRİLEN GÖREV MEVCUT DEĞİL</td></tr>';
        }
?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">&nbsp;</td>
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
