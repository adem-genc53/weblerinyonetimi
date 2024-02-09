

        <!-- Main Footer -->
        <footer class="main-footer" style="border-top: none;">
            <b>&copy; 2002-<?php echo date("Y"); ?> Tüm Haklar <a target="_blank" href="#">Adem GENÇ</a>.</b> aittir & kopyalanamaz.
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
    navigator.sendBeacon("gorev.php");
<?php } ?>
</script>

</body>

</html>