

        <!-- Main Footer -->
        <footer class="main-footer" style="border-top: none;">
            <form method="POST" id="mobil_pc">
                <input type="hidden" name="mobil_pc">
                    <b>&copy; 2002-<?php echo date("Y"); ?> Tüm Haklar <a target="_blank" href="https://uzaysat.com.tr">UZAYSAT BİNA ELEKTRONİK SİS SAN VE TİC LTD ŞTİ</a>.</b> aittir & kopyalanamaz.
                    <div class="float-right d-none- d-sm-inline-block"><span id="mobil-versiyon">
                        Mobil Versiyon: <input type="checkbox" name="gecis" value="2" <?php if(isset($_COOKIE['mobil_pc_gecis']) && $_COOKIE['mobil_pc_gecis']=='mobil'){ echo "checked"; } ?> onchange="document.getElementById('mobil_pc').submit()"></span> <b>Version</b> <?php echo VERSIYON; ?>
                    </div>
            </form>
        </footer>
        </div><!-- jswindow_website_cerceve -->
    </div><!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <script type="text/javascript" src="jswindow/jswindow-min.js"></script>
    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE -->
    <script src="js/adminlte.js"></script>
    <!-- jw Pencereleri içindir -->

    <link href="css/alert.css" rel="stylesheet">
    <script src="js/alert.js"></script>
    <script src="js/pagination.js"></script>

<script type="text/javascript">
<?php if(basename($_SERVER['SCRIPT_NAME']) != 'gorevzamanlayici.php' && basename($_SERVER['SCRIPT_NAME']) != 'db_bilgileri.php'){ ?>
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "gorev.php", true);
    xhr.send();
<?php } ?>
</script>

</body>

</html>