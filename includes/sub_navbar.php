        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">

            <!-- Brand Logo -->
            <a href="index.php" class="brand-link">
                <img src="images/pngegg.jpg" alt="Web Yönetimi" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">Web Yönetimi</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="uye_yonetimi.php?edit=<?php if(isset($_SESSION['user_id'])){ echo $_SESSION['user_id']; } ?>#ed" class="d-block">Adem GENÇ</a>
                    </div>
                </div>


                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" id="side-menu" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- .nav-icon sınıfını font-awesome veya başka herhangi bir simge yazı tipi kitaplığıyla kullanarak bağlantılara simgeler ekleyin -->

                        <li class="nav-item">
                            <a href="index.php" class="nav-link">
                                <i class="fa fa-dashboard fa-fw"></i>
                                <p> Yönetim</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="yedekle.php" class="nav-link">
                                <i class="fa fa-database"></i>
                                <p> Veritabanı Yedekle</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="bigdump.php?do=1" class="nav-link">
                                <i class="fa fa-history"></i>
                                <p> Veritabanı Geri Yükle</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="yedekkontrolu.php" class="nav-link">
                                <i class="fa fa-compress"></i>
                                <p> Yedeği Karşılaştır</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="db_bilgileri.php" class="nav-link">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                <p> Veritabanı Ekle/Düzelt</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="dizinlericek.php" class="nav-link">
                                <i class="fa fa-folder"></i>
                                <p> Web Site Dizinleri</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="zipli_dizinler.php" class="nav-link">
                                <i class="fa fa-file-zip-o"></i>
                                <p> Zipli Web Site Dizinleri</p>
                            </a>
                        </li>

<li id="menu-open" class="nav-item">
    <a href="#" id="acilir" class="nav-link">
        <i class="fa fa-cloud" aria-hidden="true"></i>
        <p>Uzak Bulut - Yedekle/İndir
        <i class="fa fa-angle-left right"></i>
        </p>
    </a>

    <ul class="nav nav-treeview">

    <li class="nav-item">
        <a href="ftp_indir_yonetimi.php" class="nav-link">
            <i class="fa fa-cloud-download" aria-hidden="true"></i>
            <p> FTP'den Yerel Dizine İndir</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="google_indir_yonetimi.php" class="nav-link">
            <i class="fa fa-cloud-download" aria-hidden="true"></i>
            <p> Google'dan Yerel Dizine İndir</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="veritabani_ftpye_yedekle.php" class="nav-link">
            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
            <p> Veritabanı FTP'ye Yedekle</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="veritabani_googleye_yedekle.php" class="nav-link">
            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
            <p> Veritabanı Google'la Yedekle</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="yerel_dizin_ftpye_yedekle.php" class="nav-link">
            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
            <p> Web Dizin FTP'ye Yedekle</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="yerel_dizin_googleye_yedekle.php" class="nav-link">
            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
            <p> Web Dizin Google'la Yedekle</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="yerel_web_dizin_zip_ftpye_yedekle.php" class="nav-link">
            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
            <p> Web Dizin Zip FTP'ye Yedekle</p>
        </a>
    </li>

    <li class="nav-item">
        <a href="yerel_web_dizin_zip_googleye_yedekle.php" class="nav-link">
            <i class="fa fa-cloud-upload" aria-hidden="true"></i>
            <p> Web Dizin Zip Google'la Yedekle</p>
        </a>
    </li>

    </ul>
</li>

                        <li class="nav-item">
                            <a href="gorevzamanlayici.php" class="nav-link">
                                <i class="fa fa-clock-o"></i>
                                <p> Görev Zamanlayıcı</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="gz_gunluk.php" class="nav-link">
                                <i class="fa fa-sticky-note"></i>
                                <p> Görev Günlükleri</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="uye_yonetimi.php" class="nav-link">
                                <i class="fa fa-users"></i>
                                <p> Üyeler</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="genel_ayarlar.php" class="nav-link">
                                <i class="fa fa-cog"></i>
                                <p> Genel Ayarlar</p>
                            </a>
                        </li>

                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

<script>
$(function() {
    $("#side-menu li a").each(function () {
        var fileName = "";
        var urller = this.href.split('?')[0];
        var gecerliurl = window.location.href.split('?')[0];
        var fileName = gecerliurl.split('/').pop();
        var myarray = ["yerel_web_dizin_zip_googleye_yedekle.php","yerel_web_dizin_zip_ftpye_yedekle.php","ftp_indir_yonetimi.php","google_indir_yonetimi.php","veritabani_ftpye_yedekle.php","veritabani_googleye_yedekle.php","yerel_dizin_ftpye_yedekle.php","yerel_dizin_googleye_yedekle.php"];
        if(jQuery.inArray(fileName, myarray) !== -1){
            $('#acilir').addClass('active');
            $('#menu-open').addClass('menu-open');
        }else{
            $('#acilir').removeClass('active');
            $('#menu-open').removeClass('menu-open');
        }
            if (urller == gecerliurl) {
                $(this).addClass('active');
            }
    });
});
</script>