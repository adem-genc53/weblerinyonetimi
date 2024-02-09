<?php
require "vendor/autoload.php";

use Teknomavi\Tcmb\Doviz;
$doviz = new Doviz();
echo " USD Alış:" . $doviz->kurAlis("USD", Doviz::TYPE_EFEKTIFALIS);
echo "<br />";
echo " USD Satış:" . $doviz->kurSatis("USD", Doviz::TYPE_EFEKTIFSATIS);
echo "<br />";
echo "<br />";
echo " EURO Efektif Alış:" . $doviz->kurAlis("EUR", Doviz::TYPE_EFEKTIFALIS);
echo "<br />";
echo " EURO Efektif Satış:" . $doviz->kurSatis("EUR", Doviz::TYPE_EFEKTIFSATIS);
echo "<br />";
echo "<br />";
echo " EURO/USD Çapraz Kur:" . $doviz->kurSatis("EUR", Doviz::TYPE_CAPRAZ);
