<?php
header('Content-type: text/html; charset=utf-8');
try {
    $baglan=new PDO ('mysql:host=localhost;dbname=vtISmi', 'root', '');
    $baglan->exec("SET NAMES 'UTF-8'");
    $baglan->exec("SET character_set_connection = 'UTF8'");
    $baglan->exec("SET character_set_client = 'UTF8'");
    $baglan->exec("SET character_set_results = 'UTF8'");
} catch (PDOException $e) {
    echo "Veritabanı bağlantı hatası: ". $e->getMessage ();
}
$simdiSQL = $baglan->query("select now() from tablo")->fetch();
$t = $simdiSQL["now()"];
$simdi=explode (" ", $t);
$tarihSQL=$baglan->query("select * from tablo2")->fetch();
$t=$tarihSQL["tarih"];
$tarih=explode (" ", $t);
$tSimdi = strtotime($simdi[0]);
$tTarih = strtotime($tarih[0]);
if ($tSimdi==$tTarih) {
    $sonuc="";
    $dosya='backup/'.$simdi[0].'.sql';
    if (file_exists($dosya)) {
        echo $tarih[0].".sql isimli veritabanı yedeği var.";
    } else {
        $ara="";
        $sonuc="";
        $tablolar=$baglan->query("show tables");
        $tabloSayisi=$tablolar->rowCount();
        for ($i=0;$i<$tabloSayisi;$i++) {
            $satir=$tablolar->fetch(PDO::FETCH_NUM);
            if (preg_match("/$ara/", $satir[0])) {
                $tabloIsmi=$satir[0];
                $tablo_olustur=$baglan->query("show create table $tabloIsmi");
                $tut=$tablo_olustur->fetch(PDO::FETCH_NUM);
                $sonuc.=$tut[1].";";
                $sonuc.="\n\n\n";
                $tabloSutunlar=$baglan->query("select * from $tabloIsmi");
                $sutunSayisi=$tabloSutunlar->columnCount();
                $ssSayisi=$tabloSutunlar->rowCount();
                for ($j=0;$j<$ssSayisi;$j++) {
                    $sonuc.="insert into ".$tabloIsmi." values (";
                    $satir=$tabloSutunlar->fetch(PDO::FETCH_NUM);
                    for ($k=0;$k<$sutunSayisi;$k++) {
                        $veri=$satir[$k];
                        $sonuc.="'".addslashes($veri)."'";
                        if ($k<($sutunSayisi-1)) {
                            $sonuc.=", ";
                        }
                    }
                    $sonuc.=");\n";
                }
            }
        }
        $yaz=fopen($dosya, "w");
        fwrite($yaz,$sonuc);
        fclose($yaz);
        echo $tarih[0].".sql isimli veritabanı yedeği oluşturuldu.";
    }
}

?>