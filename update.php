<?php
//połączenie z bazą
require_once "connect.php";
$connection = @new mysqli($host,$db_user,$db_password,$db_name);

//zmienne
$from = ['  ','pracownia - fakultet','<td style="width:17%;" class="lekcja">','<strong>','</strong><br>','<small class="plan_small">',' - ','. (',')</small></td>',')</small><br>'];
$to=[' ','pracownia mysl& fakultet','rzad&otw','nawias&','kol&zam','kol&otw','kol&zam kol&otw','kol&zam kol&otw','kol&zam rzad&zam','kol&zam rzad&zam rzad&otw'];
$code=['mysl&','rzad&otw','nawias&','kol&zam','kol&otw','rzad&zam'];
$hourIn = ['<td style="width:12%; text-align:center;">','</td>'];
$hourOut = ["",""];
$characters=['-','(','"','"',',"',',&i, &j),'];
$kadra=['                 </td>','<td>',' 	<td>','                </td>','\n',' </td>','</td>',"\r", "\n",'dr ','hab.','inż.','ks.'];
$all = "INSERT into `plan` (`subj`, `clas`, `teac`, `room`, `hour`, `day`) VALUES ";
$allHour = "INSERT into `hour` (`id`, `time`) Values ";
$isFirstTable = true;
//sprawdzanie czy jest nowy plan
$htmlZSL = file_get_contents("https://zsl.poznan.pl");//pobranie strony głownej zsł w celu sprawdzenia czy plan zmienił się
$domZSL= new DOMDocument();
@$domZSL->loadHTML($htmlZSL);
$ALLa = $domZSL->getElementsByTagName("a");
foreach($ALLa as $a){//przeszukiwanie wszystkich odnośników
    if(substr($a->getAttribute('href'),0,41)=="https://zsl.poznan.pl/wp-content/uploads/" &&(str_contains($a->textContent,"Plan") || str_contains($a->textContent,"plan")) && $a->textContent != ""){//sprawdzanie czy odnośnik ma link do planu lekcji
        $sqlcheck = "SELECT link FROM link"; 
        $result = $connection->query($sqlcheck);//pobranie starego linku z bazy danych
        if($result){
            while($row = mysqli_fetch_assoc($result)){//pętla która tylko zapisuje stary link do zmiennej
                if($a->getAttribute('href')!=$row['link']){//sprawdzanie czy link się zmienił
                    $newLink = $a->getAttribute('href');
                    $sqlupdate = "UPDATE `link` SET `link`='$newLink'";
                    $resultUpdate = $connection->query($sqlupdate);//zapisanie nowego linku
                    $link=$newLink;
                    $html = file_get_contents($link);
                    $dom = new DOMDocument();
                    @$dom->loadHTML($html);
                    $tables = $dom->getElementsByTagName("table");// Pobranie wszystkich elementów o typie tabela

                    foreach ($tables as $table){//przeczesanie wszystkich tabel
                        $rows = $table->getElementsByTagName("tr");
                        $i=0;
                        foreach ($rows as $row){//przeczesanie wszystkich rzędów w każdej tabeli
                            $cells = $row->getElementsByTagName("td");
                            $j=0;
                            foreach($cells as $cell){//przeczesanie wszystkich komórek z planem  we wszystkich rzędach
                                if($j==1 && $isFirstTable){//sprawdzanie czy jest to kolumna z godziną
                                    if($i!=1){//sprawdzanie czy nie jest pierwszą wartością w kwerendzie
                                        $allHour.=",";
                                    }
                                    $allHour .= "(".$i.",'".str_replace($hourIn,$hourOut,$dom->saveHTML($cell))."')";
                                }
                                elseif($j>1){//sprawdzanie czy jest kolumną z planem
                                    if($dom->saveHTML($cell) != '<td style="width:17%;" class="lekcja"></td>'){//sprawdzanie czy nie jest puste
                                        $all .= str_replace('&i',$i,str_replace('&j',$j-1,str_replace($code,$characters,str_replace($from, $to, $dom->saveHTML($cell)))));//patologiczny kod pobierający komórkę przetwarzający ją i dodający powstały tekst do zapytania                                
                                    }                                      
                                }
                                $j++;
                            }
                            $i++;
                        }
                        if($isFirstTable){
                            $isFirstTable = false;
                        } 
                    }
                    $sqlClass = "INSERT INTO `classes` (`name`,`classYear`,`specialization`) VALUES ";
                    $xpath = new DOMXPath($dom);
                    $classes = $xpath->query(".//a[@class='plan_menu_link']");//pobranie wszystkich obiektów zawierających nazwy klas (np 2pT)
                    foreach ($classes as $class) {
                        $name = str_replace("» ","",$class->textContent);//przetwarzanie zapytania
                        $explodedname = explode(" ",$name);
                        $classYear = $explodedname[0];
                        $specialization = $explodedname[1];
                        for($i=2;$i<count($explodedname);$i++){
                            $specialization .=" ".$explodedname[$i];
                        }
                        $sqlClass .= "('".$name."','".$classYear."','".$specialization."'),";//przetworzone informacje są dodanie do zapytania
                    }
                    $connection->query("TRUNCATE plan");//czyszczenie tabel i zapisywanie danych
                    $connection->query("TRUNCATE classes");
                    $connection->query("TRUNCATE hour");
                    $all = rtrim($all,",");
                    $sqlClass= rtrim($sqlClass,",");
                    $connection->query($all);                    
                    $connection->query($allHour);
                    $connection->query($sqlClass);
                    date_default_timezone_set('Europe/Warsaw');
                    $connection->query("INSERT into `update`(`date`) values ('".date('d-m-Y H:i:s')."')");//zapisanie daty ostatniego updatu
                }
            }
        }
    }
}
$connection->close();
?>