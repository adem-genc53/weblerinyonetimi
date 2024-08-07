        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.php" class="nav-link">Anasayfa</a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="#" class="nav-link">İletişim</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                        <i class="fas fa-search"></i>
                    </a>
                    <div class="navbar-search-block">
                        <form class="form-inline">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="search" placeholder="Search"
                                    aria-label="Search">
                                <div class="input-group-append">
                                    <button class="btn btn-navbar" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </li>

                <!-- Messages Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-comments"></i>
                        <span class="badge badge-danger navbar-badge">1</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="#" class="dropdown-item">
                            <!-- Message Start -->
                            <div class="media">
                                <img src="images/user1-128x128.jpg" alt="User Avatar"
                                    class="img-size-50 mr-3 img-circle">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        Adem GENÇ
                                        <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                    </h3>
                                    <p class="text-sm">Ne zaman istersen beni ara...</p>
                                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Saat Önce</p>
                                </div>
                            </div>
                            <!-- Message End -->
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">Tüm Mesajları Gör</a>
                    </div>
                </li>
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">1</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right notify-arrow-yellow">
                        <div class="notify-arrow notify-arrow-yellow"></div>
                        <span class="dropdown-item dropdown-header yellow">1 Bildirim</span>
                <div class="modal-scrollbar">
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 1 yeni mesaj
                            <span class="float-right text-muted text-sm">3 dakika önce</span>
                        </a>
                </div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">Tüm Bildirimleri Gör</a>
                    </div>
                </li>
<!------------------------------------------------------------------------------------------------------------------------->
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">1</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right notify-arrow-yellow">
                        <div class="notify-arrow notify-arrow-yellow"></div>
                        <span class="dropdown-item dropdown-header yellow">1 Bildirim</span>
                <div class="modal-scrollbar">
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 1 yeni mesaj
                            <span class="float-right text-muted text-sm">3 dakika önce</span>
                        </a>
                </div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">Tüm Bildirimleri Gör</a>
                    </div>
                </li>
<!------------------------------------------------------------------------------------------------------------------------->
                <!-- User Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i><span class="mobilde_isim_gizle"><?php if(isset($_SESSION['user_name'])){ echo $_SESSION['user_name']; } ?></span> <i class="fa fa-caret-down mr-2" aria-hidden="true"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="uye_yonetimi.php" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> Kullanıcı Ayarları
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="db_bilgileri.php" class="dropdown-item">
                            <i class="fa fa-gear fa-fw mr-2"></i> Veri Tabanı Seçimi
                        </a>
                        <div class="dropdown-divider"></div>
                            <a href="javascript:;" onclick="cikis();" class="dropdown-item"><i class="fa fa-sign-out fa-fw mr-2"></i> Çıkış Yap </a>
                    </div>
                </li>
<!------------------------------------------------------------------------------------------------------------------------->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button" title="Tam Ekran">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

            </ul>
        </nav>
        <!-- /.navbar -->
