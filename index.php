<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css"/>
    <link rel="stylesheet" href="style.css">
    <title>BetterZSL</title>
    <script>
        function values(){ //funkcja odpowiedzialna za utrzymywanie zapisanych wartości w inputach
            document.getElementById('subj').value = "<?php if(isset($_GET['subj'])){echo $_GET['subj'];}else{echo "Obojętnie";}?>"
            document.getElementById('clas').value = "<?php if(isset($_GET['clas'])){echo $_GET['clas'];}else{echo "Obojętnie";}?>"
            document.getElementById('teac').value = "<?php if(isset($_GET['teac'])){echo $_GET['teac'];}else{echo "Obojętnie";}?>"
            document.getElementById('room').value = "<?php if(isset($_GET['room'])){echo $_GET['room'];}else{echo "Obojętnie";}?>"
        }
    </script>
</head>
<body onload="values()">
<?php
    require_once "connect.php";
    require_once "update.php";
    $connection = @new mysqli($host,$db_user,$db_password,$db_name);
?>
<nav class="container">
  <ul>
    <li><strong>BETTER ZSŁ</strong></li>
  </ul>
  <ul>
    <li><a href="https://github.com/BetterZSL/">Github</a></li>
  </ul>
</nav>
<div id="content">
    <div id="form" class="container">
        <form action="index.php" method="get">
            <fieldset >
                <div id="left1">
                Wybierz przedmiot:
                </div>
                <div id="right1">
                    <select name="subj" id="subj" class="selects">
                        <option class="option">Obojętnie</option>
                        <?php
                            $sql = "SELECT DISTINCT subj From plan Order By subj";//input przedmiot
                            $result = @$connection->query($sql);
                            if($result) {
                                while($row = mysqli_fetch_assoc($result)){
                                    echo '<option class="option">',$row["subj"],'</option>';
                                }
                            }  
                        ?>
                    </select>
                </div>
                <div id="left2">
                Wybierz klasę:
                </div>
                <div id="right2">
                    <select name="clas" id="clas" class="selects">
                        <option class="option">Obojętnie</option>
                        <?php
                            $sql = "SELECT DISTINCT `name` FROM `classes` ORDER BY `name`";//input klasa
                            $result = @$connection->query($sql);
                            if($result) {
                                while($row = mysqli_fetch_assoc($result)){
                                    echo '<option class="option">',$row["name"],'</option>';
                                }
                            }    
                        ?>
                    </select>
                </div>
                <div id="left3">
                Wybierz nauczyciela:
                </div>
                <div id="right3">
                    <select name="teac" id="teac" class="selects">
                        <option class="option">Obojętnie</option>
                        <?php
                            $sql = "SELECT DISTINCT teac From plan Order By teac";//input nauczyciel
                            $result = @$connection->query($sql);
                            if($result) {
                                while($row = mysqli_fetch_assoc($result)){
                                    echo '<option class="option">',$row["teac"],'</option>';
                                }
                            }
                        ?> 
                    </select>
                </div>
                <div id="left4">
                Wybierz salę:
                </div>
                <div id="right4">
                    <select name="room" id="room" class="selects">
                        <option class="option">Obojętnie</option>
                        <?php
                            $sql = "SELECT DISTINCT room From plan Order By room";//input sala
                            $result = @$connection->query($sql);
                            if($result) {
                                while($row = mysqli_fetch_assoc($result)){
                                    echo '<option class="option">',$row["room"],'</option>';
                                }
                            }
                        ?>
                    </select>
                </div>
                <div id="submit1">
                    <input type="submit" value="pokaż plan" id="submit" onclick="proceedingOn()">
                    <span aria-busy="true" id="proceeding" style="display: none;">Wykonywanie zapytania...</span>
                </div>
            </fieldset>
        </form>
    </div>
    <table id="table" class ="container" >
        <tr class="zero row">
            <th class="hd cell"></th>
            <th class="hd cell"></th>
            <th class="hd cell">Poniedziałek</th>
            <th class="hd cell">Wtorek</th>
            <th class="hd cell">Środa</th>
            <th class="hd cell">Czwartek</th>
            <th class="hd cell">Piątek</th>
        </tr>
        <?php
            $result = $connection->query("SELECT `time` From `hour` Order by `id` asc");
            $timeIndex=1;
            while($row =  mysqli_fetch_assoc($result)){//przypisanie godzin lekcyjnych do tablicy
                $time[$timeIndex] = $row['time'];
                $timeIndex++;
            }
            
            $br = false;
            if(isset($_GET['subj'])&&isset($_GET['clas'])&&isset($_GET['teac'])&&isset($_GET['room'])){//ustawianie wartości jeżeli ustawione 
                $subj = mysqli_real_escape_string($connection,$_GET['subj']);
                $clas = mysqli_real_escape_string($connection,$_GET['clas']);
                $teac = mysqli_real_escape_string($connection,$_GET['teac']);
                $room = mysqli_real_escape_string($connection,$_GET['room']);
            }
            else{//ustawianie wartości jeżeli nieustawione
                $subj = "Obojętnie";
                $clas = "Obojętnie";
                $teac = "Obojętnie";
                $room = "Obojętnie";
            }
            $sqls = "SELECT subj, clas, teac, room From plan Where";//formuowanie zapytania
            $first=true;
            if($subj!="Obojętnie"){//sprawdzanie czy użytkowik chce filtrować po tej kolumnie
                if(!$first){
                    $sqls .=" and ";
                }
                $sqls .=" subj = '$subj' ";
                $first = false;
            }
            if($clas!="Obojętnie"){
                if(!$first){
                    $sqls .=" and ";
                }
                $nospace = str_replace(" ","",$clas);//z powodu zapisu bez spacji (np hiszpański)
                $sqls .=" (clas like '%$clas%' or clas like '%$nospace%') ";
                $first = false;
            }
            if($teac!="Obojętnie"){
                if(!$first){
                    $sqls .=" and ";
                }
                $sqls .=" teac = '$teac'";
                $first = false;
            }
            if($room!="Obojętnie"){
                if(!$first){
                    $sqls .=" and ";
                }
                $sqls .=" room = '$room'";
                $first = false;
            }
            for($j=1; $j<=15; $j++){//pętla tworząca rzędy
                if($j%2==0){
                    echo "<tr class='zero row'>";
                }
                else{
                    echo "<tr class='one row'>";
                }
                for($i=0; $i<=5; $i++){//pętla tworząca kolumny
                    if($i==0){//sprawdzanie czy kolumna jest pierwsza dodanie godziny i cyfry do pierwszych dwóch kolumn
                        echo '<td class="cell">',$j,"</td>";
                        echo '<td class="cell">',$time[$j],"</td>";
                    }
                    else{//w innym przypadku wstawia lekcje do komórki
                        $sqlfor = $sqls; //przypisanie zapytania wprowadzonego przez użytkownika do zmiennej w pętli aby podstawowe zapytanie pozostało takie same
                        if(!$first){
                            $sqlfor .=" and ";
                        }
                        $sqlfor.=" day = $i and hour = $j Group by `teac`, `clas`, `room`, `subj`";
                        $result = @$connection->query($sqlfor);
                        echo'<td class="cell">';
                        if($result) {
                            while($row = mysqli_fetch_assoc($result)){//pętla wypisująca dane do komórki
                                if($br=true){//sprawdzanie czy należy dodać br, ponieważ lekcja jest druga lub większa w komórce
                                    echo"<br>";
                                }
                                echo '<strong>',$row["subj"],'</strong><br/>
                                <small>',$row["clas"],'-',$row["teac"],' (',$row["room"],')</small>';
                                $br=true;//ustawienie że komórka ma już dane
                            }
                            $br=false;
                        } 
                        echo"</td>";
                    }        
                }
                echo "</tr>";
            }
            $connection->close();
        ?>
    </table>
</div>
<script src="script.js" defer></script>
</body>
</html>