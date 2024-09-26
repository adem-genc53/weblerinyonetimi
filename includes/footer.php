

        <!-- Main Footer -->
        <footer class="main-footer" style="border-top: none;">
<!-- /  -->
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

<?php if($genel_ayarlar['gorevi_calistir'] == 2 && basename($_SERVER['SCRIPT_NAME']) != 'gorevzamanlayici.php' && basename($_SERVER['SCRIPT_NAME']) != 'db_bilgileri.php'){ ?>
<script type="text/javascript">
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "gorev.php", true);
    xhr.send();
</script>
<?php } ?>

</body>

</html>