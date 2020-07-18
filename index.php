


<?php 
$resultPage = 0;
include "include/config.php" ;

$stmtDelObyek = $connection->prepare('DELETE FROM hasilobyek');
$stmtDelObyek -> execute();

$stmtDelKueri = $connection->prepare('DELETE FROM hasilkueri');
$stmtDelKueri ->execute();


//CB VAR
$popular = '';
$downtown = '';
//END CB VAR

//SHOW BUTTON
if(isset($_POST['Show'])){
  $stmtDelObyek = $connection->prepare('DELETE FROM hasilobyek');
  $stmtDelObyek -> execute();

  $stmtDelKueri = $connection->prepare('DELETE FROM hasilkueri');
  $stmtDelKueri ->execute();
  $resultPage = 1; //SHOW RESULT PAGE 
  //INISIALISI
  $kabupatenNAMA = $_POST['mulai'];
  $dateStart = $_POST['dtpStart'];
  $dateEnd = $_POST['dtpEnd'];

  if(isset($_POST['cbPopular']))$popular = $_POST['cbPopular'];
  if(isset($_POST['cbDowntown'])) $downtown = $_POST['cbDowntown'];

  //END INISIALISI

  //KABUPATEN KODE
  $qKabupatenKode = $connection->prepare("SELECT*FROM kabupaten WHERE kabupatenNAMA = :kabNAMA");
  $qKabupatenKode->execute(['kabNAMA' => $kabupatenNAMA]);
  $rKabupatenKode = $qKabupatenKode->fetch();
  $kabupatenKODE = $rKabupatenKode['kabupatenKODE'];
  //END KABUPATEN KODE
  
$kodeAWAL='';
  //CARI KODE AWAL
  if($popular == '' AND $downtown == '') {
    $qObyekAwal = $connection->prepare("SELECT ow.* FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE AND ow.obyekWAKTUKUNJUNG = (SELECT MAX(obyekWAKTUKUNJUNG) FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE ) LIMIT 1");
  }
  else if($popular == 'cbPopular' AND $downtown == '') {
    $qObyekAwal = $connection->prepare("SELECT ow.* FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE AND ow.obyekPOPULARITAS = (SELECT MIN(obyekPOPULARITAS) FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE )  LIMIT 1");
  }
  else if($popular == '' AND $downtown == 'cbDowntown') {
    $qObyekAwal = $connection->prepare("SELECT ow.* FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE AND ow.obyekKEMUDAHAN = (SELECT MIN(obyekKEMUDAHAN) FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE ) LIMIT 1 ");
  }
  else if($popular == 'cbPopular' AND $downtown == 'cbDowntown')  {
    $qObyekAwal = $connection->prepare("SELECT ow.* FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE AND ow.obyekPOPULARITAS = (SELECT MIN(obyekPOPULARITAS) FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE ) OR ow.obyekKEMUDAHAN = (SELECT MIN(obyekKEMUDAHAN) FROM obyekwisata ow, kecamatan kec WHERE kec.kecamatanKODE = ow.kecamatanKODE AND kec.kabupatenKODE = :kabKODE ) LIMIT 1; ");
  }

  $qObyekAwal->execute(['kabKODE'=>$kabupatenKODE]);
  if($qObyekAwal->rowCount()>0){
    $rObyekAwal = $qObyekAwal->fetch();
    $kodeAWAL= $rObyekAwal['obyekKODE'];
  }
  //END CARI KODE AWAL


  //GREEDY
  $check = 0;
  $check2 = 0;
  $i = 0;
  $cust = 1;
  $jarak=0;
  $tempuh = 0;
  $destinasi = '';
  $jumlahjarak = 0;
  $jumlahwaktu = 0;
  $attraction = 0;
  $attraction2 = 0;
  $continue = 1;
  $control = 1;
  $control2 = 1;
  while($continue==1){
    //TODAY
    $qSorting = $connection->prepare("SELECT jo.ruteKODE, jo.obyekKODEasal, owa.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGmulai, owa.obyekPOPULARITAS AS obyekPOPULARITASmulai, owa.obyekKEMUDAHAN as obyekKEMUDAHANmulai, jo.obyekKODEtujuan, owd.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGdestinasi, owd.obyekPOPULARITAS AS obyekPOPULARITASdestinasi, owd.obyekKEMUDAHAN AS obyekKEMUDAHANdestinasi, jo.obyekjarak   , jo.obyektempuh, jo.obyekRUTE FROM jarakobyek jo, obyekwisata owa, obyekwisata owd, kecamatan keca, kecamatan kecd WHERE jo.obyekKODEasal = owa.obyekKODE AND jo.obyekKODEtujuan = owd.obyekKODE AND obyekKODEasal = :kodeASAL AND keca.kecamatanKODE = owa.kecamatanKODE AND kecd.kecamatanKODE = owd.kecamatanKODE AND NOT EXISTS (SELECT obyekKODE FROM hasilkueri WHERE obyekKODE = jo.obyekKODEtujuan)   ");
    $qSorting->execute(['kodeASAL'=>$kodeAWAL]);
    if($qSorting->rowCount()>0){
      while($row = $qSorting->fetch()){
        //KONDISI ATTRACTION
          if($popular == '' AND $downtown == '') $check = $row['obyekWAKTUKUNJUNGdestinasi'];
          else if($popular == 'cbPopular' AND $downtown == '') $check = $row['obyekPOPULARITASdestinasi'];
          else if($popular == '' AND $downtown == 'cbDowntown') $check = $row['obyekKEMUDAHANdestinasi'];
          else if($popular == 'cbPopular' AND $downtown == 'cbDowntown') {$check = $row['obyekKEMUDAHANdestinasi'];$check2 = $row['obyekPOPULARITASdestinasi'];} ;
        //END KONDISI ATTRACTION

          if($popular == 'cbPopular' AND $downtown == 'cbDowntown'){
            if($attraction == 0 AND $attraction2==0 OR ($check <$attraction OR $check2 < $attraction2)){
              
              $jarak = $row['obyekjarak'];
              $kodeAWAL = $row ['obyekKODEasal'];
              $destinasi = $row ['obyekKODEtujuan'];
              $tempuh = $row['obyektempuh'];
              $attraction = $check;
              $attraction2 = $check2;
            }
          }else if($popular == '' AND $downtown == ''){
            if($attraction == 0 OR $check >$attraction ){
              $jarak = $row['obyekjarak'];
              $kodeAWAL = $row ['obyekKODEasal'];
              $destinasi = $row ['obyekKODEtujuan'];
              $tempuh = $row['obyektempuh'];
              $attraction = $check;
            }
          }else{
            if($attraction == 0 OR $check <$attraction ){
              $jarak = $row['obyekjarak'];
              $kodeAWAL = $row ['obyekKODEasal'];
              $destinasi = $row ['obyekKODEtujuan'];
              $tempuh = $row['obyektempuh'];
              $attraction = $check;
            }

          }

      }
      $control2 = $control2+1;
    }else if ($qSorting->rowCount()==0 AND $control2 == 1) {
      //PROBLEM
      $kodeAWALbaru = '';
      $qSorting = $connection->prepare("SELECT jo.ruteKODE, jo.obyekKODEasal, owa.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGmulai, owa.obyekPOPULARITAS AS obyekPOPULARITASmulai, owa.obyekKEMUDAHAN as obyekKEMUDAHANmulai, jo.obyekKODEtujuan, owd.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGdestinasi, owd.obyekPOPULARITAS AS obyekPOPULARITASdestinasi, owd.obyekKEMUDAHAN AS obyekKEMUDAHANdestinasi, jo.obyekjarak   , jo.obyektempuh, jo.obyekRUTE FROM jarakobyek jo, obyekwisata owa, obyekwisata owd, kecamatan keca, kecamatan kecd WHERE jo.obyekKODEasal = owa.obyekKODE AND jo.obyekKODEtujuan = owd.obyekKODE AND obyekKODEasal != :kodeASAL AND keca.kecamatanKODE = owa.kecamatanKODE AND kecd.kecamatanKODE = owd.kecamatanKODE AND keca.kabupatenKODE = :kodeKAB ORDER BY RAND() LIMIT 1  ");
     $qSorting->execute(['kodeASAL'=>$kodeAWAL,'kodeKAB'=>$kabupatenKODE]);

     if($qSorting->rowCount()>0){
        $row = $qSorting->fetch();
        $kodeAWALbaru = $row['obyekKODEasal'];

        
        //SOLVER 
        //KONDISI ATTRACTION
          if($popular == '' AND $downtown == '') $check = $row['obyekWAKTUKUNJUNGdestinasi'];
          else if($popular == 'cbPopular' AND $downtown == '') $check = $row['obyekPOPULARITASdestinasi'];
          else if($popular == '' AND $downtown == 'cbDowntown') $check = $row['obyekKEMUDAHANdestinasi'];
          else if($popular == 'cbPopular' AND $downtown == 'cbDowntown') {$check = $row['obyekKEMUDAHANdestinasi'];$check2 = $row['obyekPOPULARITASdestinasi'];} ;
        //END KONDISI ATTRACTION

          if($popular == 'cbPopular' AND $downtown == 'cbDowntown'){
            if($attraction == 0 AND $attraction2==0 OR ($check <$attraction OR $check2 < $attraction2)){
              $jarak = $row['obyekjarak'];
              $kodeAWAL = $row ['obyekKODEasal'];
              $destinasi = $row ['obyekKODEtujuan'];
              $tempuh = $row['obyektempuh'];
              $attraction = $check;
              $attraction2 = $check2;
            }
          }else if($popular == '' AND $downtown == ''){
            if($attraction == 0 OR $check >$attraction ){
              $jarak = $row['obyekjarak'];
              $kodeAWAL = $row ['obyekKODEasal'];
              $destinasi = $row ['obyekKODEtujuan'];
              $tempuh = $row['obyektempuh'];
              $attraction = $check;
            }
          }else{
            if($attraction == 0 OR $check <$attraction ){
              $jarak = $row['obyekjarak'];
              $kodeAWAL = $row ['obyekKODEasal'];
              $destinasi = $row ['obyekKODEtujuan'];
              $tempuh = $row['obyektempuh'];
              $attraction = $check;
            }

          }
          $kodeAWAL = $kodeAWALbaru;
        //END SOLVER
     }else {
        //PROBLEM
        //SOLVER IN KAB
        $qObyekInKab = $connection->prepare("SELECT ow.* FROM obyekwisata ow, kecamatan k WHERE ow.kecamatanKODE = k.kecamatanKODE AND k.kabupatenKODE = :kode ORDER BY RAND() ");
        $qObyekInKab->execute(['kode'=>$kabupatenKODE]);
        if($qObyekInKab->rowCount()>0){
          while($rObyekInKab = $qObyekInKab->fetch()){
            //INSERT INTO HASILOBYEK
            $customer = 'A'.$cust;
            $kode = $rObyekInKab['obyekKODE'];
            $kecamatan = $rObyekInKab['kecamatanKODE'];
            $arrkecKODEasal = str_split($kecamatan,4);
            $kabupatenKODEasal = $arrkecKODEasal[0];
            $stmt = $connection->prepare("INSERT INTO hasilobyek (obyekKODEasal,kabupatenKODE_awal,Customer_ID,rute) VALUES (:kode,:kabupaten,:customer,1); ");
            $stmt->execute(['kode'=>$kode,'kabupaten'=>$kabupatenKODEasal,'customer'=>$customer]);
            $cust= $cust+1;

            //END HASILOBYEK

            //INSERT INTO HASILKUERI
            $kategori = $rObyekInKab['kategoriKODE'];
            $popularitas = $rObyekInKab['obyekPOPULARITAS'];
            $kemudahan=$rObyekInKab['obyekKEMUDAHAN'];
            $waktukunjung = $rObyekInKab ['obyekWAKTUKUNJUNG'];
            $jamBUKA = $rObyekInKab ['obyekJAMBUKA'];
            $jamTUTUP = $rObyekInKab ['obyekJAMTUTUP'];
            $stmt = $connection->prepare("INSERT INTO hasilkueri VALUES (:kode,:kecamatan,:kabupaten,:kategori,:obyekPOPULARITAS,:obyekKEMUDAHAN,:obyekWAKTUKUNJUNG,:jamBUKA,:jamTUTUP,'');  ");
            $stmt->execute(['kode'=>$kode,'kecamatan'=>$kecamatan,'kabupaten'=>$kabupatenKODEasal,'kategori'=>$kategori,'obyekPOPULARITAS'=>$popularitas,'obyekKEMUDAHAN'=>$kemudahan,'obyekWAKTUKUNJUNG'=>$waktukunjung,'jamBUKA'=>$jamBUKA,'jamTUTUP'=>$jamTUTUP]);
            //END HASILKUERI
          }
         
        }
        //END SOLVER IN KAB


        $kodeAWALbaru = '';
        $qSorting = $connection->prepare("SELECT jo.ruteKODE, jo.obyekKODEasal, owa.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGmulai, owa.obyekPOPULARITAS AS obyekPOPULARITASmulai, owa.obyekKEMUDAHAN as obyekKEMUDAHANmulai, jo.obyekKODEtujuan, owd.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGdestinasi, owd.obyekPOPULARITAS AS obyekPOPULARITASdestinasi, owd.obyekKEMUDAHAN AS obyekKEMUDAHANdestinasi, jo.obyekjarak   , jo.obyektempuh, jo.obyekRUTE FROM jarakobyek jo, obyekwisata owa, obyekwisata owd, kecamatan keca, kecamatan kecd WHERE jo.obyekKODEasal = owa.obyekKODE AND jo.obyekKODEtujuan = owd.obyekKODE AND obyekKODEasal != :kodeASAL AND keca.kecamatanKODE = owa.kecamatanKODE AND kecd.kecamatanKODE = owd.kecamatanKODE  ORDER BY RAND() LIMIT 1  ");
       $qSorting->execute(['kodeASAL'=>$kodeAWAL]);

       if($qSorting->rowCount()>0){
          $row = $qSorting->fetch();
          $kodeAWALbaru = $row['obyekKODEasal'];

          
          //SOLVER 
          //KONDISI ATTRACTION
            if($popular == '' AND $downtown == '') $check = $row['obyekWAKTUKUNJUNGdestinasi'];
            else if($popular == 'cbPopular' AND $downtown == '') $check = $row['obyekPOPULARITASdestinasi'];
            else if($popular == '' AND $downtown == 'cbDowntown') $check = $row['obyekKEMUDAHANdestinasi'];
            else if($popular == 'cbPopular' AND $downtown == 'cbDowntown') {$check = $row['obyekKEMUDAHANdestinasi'];$check2 = $row['obyekPOPULARITASdestinasi'];} ;
          //END KONDISI ATTRACTION

            if($popular == 'cbPopular' AND $downtown == 'cbDowntown'){
              if($attraction == 0 AND $attraction2==0 OR ($check <$attraction OR $check2 < $attraction2)){
                
                $jarak = $row['obyekjarak'];
                $kodeAWAL = $row ['obyekKODEasal'];
                $destinasi = $row ['obyekKODEtujuan'];
                $tempuh = $row['obyektempuh'];
                $attraction = $check;
                $attraction2 = $check2;
              }
            }else if($popular == '' AND $downtown == ''){
              if($attraction == 0 OR $check >$attraction ){
                $jarak = $row['obyekjarak'];
                $kodeAWAL = $row ['obyekKODEasal'];
                $destinasi = $row ['obyekKODEtujuan'];
                $tempuh = $row['obyektempuh'];
                $attraction = $check;
              }
            }else{
              if($attraction == 0 OR $check <$attraction ){
                $jarak = $row['obyekjarak'];
                $kodeAWAL = $row ['obyekKODEasal'];
                $destinasi = $row ['obyekKODEtujuan'];
                $tempuh = $row['obyektempuh'];
                $attraction = $check;
              }

            }
            $kodeAWAL = $kodeAWALbaru;
          //END SOLVER
       }
      
     }
      $control2 = $control2+1;
    }
    //BREAK PROBLEM
    if($destinasi == $kodeAWAL OR $destinasi == ''){
      break;
    }
    //END BREAK
    $jumlahwaktu = $jumlahwaktu+$tempuh;
    $jumlahjarak = $jumlahjarak+$jarak;
    $i = $i+1;
    $customer = 'A'.$cust;

    //CARI KODE KABUPATEN
      $qKabupatenAwal = $connection->prepare("SELECT DISTINCT k.kabupatenKODE
                          FROM `obyekwisata` ow JOIN kecamatan k JOIN jarakobyek jo
                          WHERE k.kecamatanKODE = ow.kecamatanKODE AND ow.obyekKODE = :kodeAWAL;");

      $qKabupatenAwal->execute(['kodeAWAL'=>$kodeAWAL]);
        $rkabAwala  = $qKabupatenAwal->fetch();
        $kodeKabAwal = $rkabAwala['kabupatenKODE'];

         $qKabupatenDestinasi = $connection->prepare("SELECT DISTINCT k.kabupatenKODE
                                FROM `obyekwisata` ow JOIN kecamatan k JOIN jarakobyek jo
                                WHERE k.kecamatanKODE = ow.kecamatanKODE AND ow.obyekKODE = :kodeBERIKUT;");
         $qKabupatenDestinasi->execute(['kodeBERIKUT'=>$destinasi]);
        $rkabDestinasi = $qKabupatenDestinasi->fetch();
        $kodeKabDestinasi = $rkabDestinasi['kabupatenKODE'];
        //END CARI KODE KABUPATEN

    $stmt = $connection->prepare("INSERT INTO hasilobyek  VALUES (:kodeAWAL,:destinasi,:jarak,:tempuh,:jumlahjarak,:jumlahwaktu,:kabupatenKODEawal,:kabupatenKODEberikut,:customer,:i)");
    $stmt->execute(['kodeAWAL'=>$kodeAWAL,'destinasi'=>$destinasi,'jarak'=>$jarak,'tempuh'=>$tempuh,'jumlahjarak'=>$jumlahjarak,'jumlahwaktu'=>$jumlahwaktu,'kabupatenKODEawal'=>$kodeKabAwal,'kabupatenKODEberikut'=>$kodeKabDestinasi, 'customer'=>$customer,'i'=>$i]);

    //INSERT INTO HASIL KUERI
    //SELECT kdoe awal
    if($control == 1){
      $qselectAWAL = $connection->prepare("SELECT * FROM obyekwisata WHERE obyekKODE = :kode ");
    $qselectAWAL->execute(['kode'=>$kodeAWAL]);
    $rselectAWAL = $qselectAWAL->fetch();

    $obyekKODE = $rselectAWAL['obyekKODE'];
    $kecamatanKODE = $rselectAWAL['kecamatanKODE'];
    $kategoriKODE = $rselectAWAL['kategoriKODE'];
    $obyekPOP = $rselectAWAL['obyekPOPULARITAS'];
    $obyekKEM = $rselectAWAL['obyekKEMUDAHAN'];
    $obyekWAK = $rselectAWAL['obyekWAKTUKUNJUNG'];
    $jamBUKA = $rselectAWAL ['obyekJAMBUKA'];
    $jamTUTUP = $rselectAWAL ['obyekJAMTUTUP'];
    $custom = '';

    $stmt = $connection->prepare("INSERT INTO hasilkueri VALUES (:obyekKODE,:kecamatanKODE,:kabupatenKODE,:kategoriKODE,:obyekPOPULARITAS,:obyekKEMUDAHAN,:obyekWAKTUKUNJUNG,:jamBUKA,:jamTUTUP,:customer) ");
    $stmt->execute(['obyekKODE'=>$obyekKODE,'kecamatanKODE'=>$kecamatanKODE,'kabupatenKODE'=>$kodeKabAwal,'kategoriKODE'=>$kategoriKODE,'obyekPOPULARITAS'=>$obyekPOP,'obyekKEMUDAHAN'=>$obyekKEM,'obyekWAKTUKUNJUNG'=>$obyekWAK,'jamBUKA'=>$jamBUKA,'jamTUTUP'=>$jamTUTUP,'customer'=>$custom]);
    $control = $control+1;
    }
    
    //END SELECT kode awal

    //SELECT KODE tujuan
    $qselectBERIKUT = $connection->prepare("SELECT * FROM obyekwisata WHERE obyekKODE = :kode ");
    $qselectBERIKUT->execute(['kode'=>$destinasi]);
    $rselectBERIKUT = $qselectBERIKUT->fetch();

    $obyekKODE = $rselectBERIKUT['obyekKODE'];
    $kecamatanKODE = $rselectBERIKUT['kecamatanKODE'];
    $kategoriKODE = $rselectBERIKUT['kategoriKODE'];
    $obyekPOP = $rselectBERIKUT['obyekPOPULARITAS'];
    $obyekKEM = $rselectBERIKUT['obyekKEMUDAHAN'];
    $obyekWAK = $rselectBERIKUT['obyekWAKTUKUNJUNG'];
    $jamBUKA = $rselectBERIKUT ['obyekJAMBUKA'];
    $jamTUTUP = $rselectBERIKUT ['obyekJAMTUTUP'];
    $custom = '';

    $stmt = $connection->prepare("INSERT INTO hasilkueri VALUES (:obyekKODE,:kecamatanKODE,:kabupatenKODE,:kategoriKODE,:obyekPOPULARITAS,:obyekKEMUDAHAN,:obyekWAKTUKUNJUNG,:jamBUKA,:jamTUTUP,:customer) ");
    $stmt->execute(['obyekKODE'=>$obyekKODE,'kecamatanKODE'=>$kecamatanKODE,'kabupatenKODE'=>$kodeKabDestinasi,'kategoriKODE'=>$kategoriKODE,'obyekPOPULARITAS'=>$obyekPOP,'obyekKEMUDAHAN'=>$obyekKEM,'obyekWAKTUKUNJUNG'=>$obyekWAK,'jamBUKA'=>$jamBUKA,'jamTUTUP'=>$jamTUTUP,'customer'=>$custom]);


     //END SELECT kode tujuan
    //END INSERT INTO HASIL KUERI

    //RESET
    $attraction = 0;
    $attraction2 = 0;
    $kodeAWAL = $destinasi;
    //END RESET
  }
  //END GREEDY

  //GREEDY 2
  $control = 1;
  $control2 = 1;
   while($continue == 1){

    //RESET
    $row='';
    $kodeAWAL = '';
    $check = 0;
    $check2 = 0;
    $i = 0;
    $cust = $cust+1;
    $jarak=0;
    $tempuh = 0;
    $destinasi = '';
    $jumlahjarak = 0;
    $jumlahwaktu = 0;
    $attraction = 0;
    $attraction2 = 0;
    $continue = 1;
    $control = 1;
    //END RESET
    $qFindRand = $connection->prepare("SELECT jo.*, owd.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGdestinasi, owd.obyekPOPULARITAS AS obyekPOPULARITASdestinasi, owd.obyekKEMUDAHAN AS obyekKEMUDAHANdestinasi FROM jarakobyek jo, obyekwisata owa, obyekwisata owd, kecamatan keca, kecamatan kecd WHERE jo.obyekKODEasal = owa.obyekKODE AND jo.obyekKODEtujuan = owd.obyekKODE AND owa.kecamatanKODE = keca.kecamatanKODE AND owd.kecamatanKODE = kecd.kecamatanKODE AND keca.kabupatenKODE = :kodeKAB AND NOT EXISTS (SELECT obyekKODE FROM hasilkueri WHERE obyekKODE = jo.obyekKODEasal) AND NOT EXISTS (SELECT obyekKODE FROM hasilkueri WHERE obyekKODE = jo.obyekKODEtujuan) ORDER BY RAND() LIMIT 1 ");

    $qFindRand->execute(['kodeKAB'=> $kodeKabDestinasi]);
    if($qFindRand->rowCount()>0){
      $row = $qFindRand->fetch();
      $kodeAWAL = $row['obyekKODEasal'];
      $kodeTUJUAN = $row['obyekKODEtujuan'];
    }

    if($kodeAWAL=='' OR $kodeTUJUAN ==''){
      $qFindRand = $connection->prepare("SELECT jo.*, owd.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGdestinasi, owd.obyekPOPULARITAS AS obyekPOPULARITASdestinasi, owd.obyekKEMUDAHAN AS obyekKEMUDAHANdestinasi FROM jarakobyek jo, obyekwisata owa, obyekwisata owd, kecamatan keca, kecamatan kecd WHERE jo.obyekKODEasal = owa.obyekKODE AND jo.obyekKODEtujuan = owd.obyekKODE AND owa.kecamatanKODE = keca.kecamatanKODE AND owd.kecamatanKODE = kecd.kecamatanKODE  AND NOT EXISTS (SELECT obyekKODE FROM hasilkueri WHERE obyekKODE = jo.obyekKODEasal) AND NOT EXISTS (SELECT obyekKODE FROM hasilkueri WHERE obyekKODE = jo.obyekKODEtujuan) ORDER BY RAND() LIMIT 1 ");
      $qFindRand->execute(['kodeKAB'=> $kodeKabDestinasi]);
      if($qFindRand->rowCount()>0){
        $row = $qFindRand->fetch();
        $kodeAWAL = $row['obyekKODEasal'];
        $kodeTUJUAN = $row['obyekKODEtujuan'];
      }
      //SISA OBYEKWISATA
      if($kodeAWAL=='' OR $kodeTUJUAN == ''){
        $qSisaObyek = $connection->prepare("SELECT ow.*, k.kabupatenKODE FROM obyekwisata ow, kecamatan k WHERE ow.kecamatanKODE = k.kecamatanKODE  AND NOT EXISTS (SELECT obyekKODE FROM hasilkueri WHERE obyekKODE = ow.obyekKODE) ORDER BY kabupatenKODE ");
        $qSisaObyek->execute();
        if($qSisaObyek->rowCount()>0){
          while($rSisaObyek = $qSisaObyek->fetch()){
            //INSERT INTO HASILOBYEK
            $customer = 'A'.$cust;
            $kode = $rSisaObyek['obyekKODE'];
            $kecamatan = $rSisaObyek['kecamatanKODE'];
            $arrkecKODEasal = str_split($kecamatan,4);
            $kabupatenKODEasal = $arrkecKODEasal[0];
            $stmt = $connection->prepare("INSERT INTO hasilobyek (obyekKODEasal,kabupatenKODE_awal,Customer_ID,rute) VALUES (:kode,:kabupaten,:customer,1); ");
            $stmt->execute(['kode'=>$kode,'kabupaten'=>$kabupatenKODEasal,'customer'=>$customer]);
            $cust= $cust+1;

            //END HASILOBYEK

            //INSERT INTO HASILKUERI
            $kategori = $rSisaObyek['kategoriKODE'];
            $popularitas = $rSisaObyek['obyekPOPULARITAS'];
            $kemudahan=$rSisaObyek['obyekKEMUDAHAN'];
            $waktukunjung = $rSisaObyek ['obyekWAKTUKUNJUNG'];
            $jamBUKA = $rSisaObyek ['obyekJAMBUKA'];
            $jamTUTUP = $rSisaObyek ['obyekJAMTUTUP'];
            $stmt = $connection->prepare("INSERT INTO hasilkueri VALUES (:kode,:kecamatan,:kabupaten,:kategori,:obyekPOPULARITAS,:obyekKEMUDAHAN,:obyekWAKTUKUNJUNG,:jamBUKA,:jamTUTUP,'');  ");
            $stmt->execute(['kode'=>$kode,'kecamatan'=>$kecamatan,'kabupaten'=>$kabupatenKODEasal,'kategori'=>$kategori,'obyekPOPULARITAS'=>$popularitas,'obyekKEMUDAHAN'=>$kemudahan,'obyekWAKTUKUNJUNG'=>$waktukunjung,'jamBUKA'=>$jamBUKA,'jamTUTUP'=>$jamTUTUP]);
            //END HASILKUERI
          }
         
        }
        //END SOLVER IN KAB
      }
      //END SISA OBYEKWISATA
    }
    

    if($kodeAWAL == '' OR $kodeTUJUAN =='' OR $kodeAWAL == $kodeTUJUAN  )break;

    //TEST
    while($continue==1){

        $qSorting = $connection->prepare("SELECT jo.ruteKODE, jo.obyekKODEasal, owa.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGmulai, owa.obyekPOPULARITAS AS obyekPOPULARITASmulai, owa.obyekKEMUDAHAN as obyekKEMUDAHANmulai, jo.obyekKODEtujuan, owd.obyekWAKTUKUNJUNG AS obyekWAKTUKUNJUNGdestinasi, owd.obyekPOPULARITAS AS obyekPOPULARITASdestinasi, owd.obyekKEMUDAHAN AS obyekKEMUDAHANdestinasi, jo.obyekjarak   , jo.obyektempuh, jo.obyekRUTE FROM jarakobyek jo, obyekwisata owa, obyekwisata owd, kecamatan keca, kecamatan kecd WHERE jo.obyekKODEasal = owa.obyekKODE AND jo.obyekKODEtujuan = owd.obyekKODE AND obyekKODEasal = :kodeASAL AND keca.kecamatanKODE = owa.kecamatanKODE AND kecd.kecamatanKODE = owd.kecamatanKODE AND NOT EXISTS (SELECT obyekKODE FROM hasilkueri WHERE obyekKODE = jo.obyekKODEtujuan) ");
      $qSorting->execute(['kodeASAL'=>$kodeAWAL]);
      if($qSorting->rowCount()>0){
        while($row = $qSorting->fetch()){
          //KONDISI ATTRACTION
            if($popular == '' AND $downtown == '') $check = $row['obyekWAKTUKUNJUNGdestinasi'];
            else if($popular == 'cbPopular' AND $downtown == '') $check = $row['obyekPOPULARITASdestinasi'];
            else if($popular == '' AND $downtown == 'cbDowntown') $check = $row['obyekKEMUDAHANdestinasi'];
            else if($popular == 'cbPopular' AND $downtown == 'cbDowntown') {$check = $row['obyekKEMUDAHANdestinasi'];$check2 = $row['obyekPOPULARITASdestinasi'];} ;
          //END KONDISI ATTRACTION

            if($popular == '' AND $downtown == ''){
              if($attraction == 0 OR $check >$attraction ){
                $jarak = $row['obyekjarak'];
                $kodeAWAL = $row ['obyekKODEasal'];
                $destinasi = $row ['obyekKODEtujuan'];
                $tempuh = $row['obyektempuh'];
                $attraction = $check;
              }
            }else{
              if($attraction == 0 OR $check <$attraction ){
                $jarak = $row['obyekjarak'];
                $kodeAWAL = $row ['obyekKODEasal'];
                $destinasi = $row ['obyekKODEtujuan'];
                $tempuh = $row['obyektempuh'];
                $attraction = $check;
              }

            }

        }
      }
      //BREAK PROBLEM
      if($destinasi == $kodeAWAL OR $destinasi == ''){
        break;
      }
      //END BREAK
      $jumlahwaktu = $jumlahwaktu+$tempuh;
      $jumlahjarak = $jumlahjarak+$jarak;
      $i = $i+1;
      $customer = 'A'.$cust;

      //CARI KODE KABUPATEN
        $qKabupatenAwal = $connection->prepare("SELECT DISTINCT k.kabupatenKODE
                            FROM `obyekwisata` ow JOIN kecamatan k JOIN jarakobyek jo
                            WHERE k.kecamatanKODE = ow.kecamatanKODE AND ow.obyekKODE = :kodeAWAL;");

        $qKabupatenAwal->execute(['kodeAWAL'=>$kodeAWAL]);
          $rkabAwala  = $qKabupatenAwal->fetch();
          $kodeKabAwal = $rkabAwala['kabupatenKODE'];

           $qKabupatenDestinasi = $connection->prepare("SELECT DISTINCT k.kabupatenKODE
                                  FROM `obyekwisata` ow JOIN kecamatan k JOIN jarakobyek jo
                                  WHERE k.kecamatanKODE = ow.kecamatanKODE AND ow.obyekKODE = :kodeBERIKUT;");
           $qKabupatenDestinasi->execute(['kodeBERIKUT'=>$destinasi]);
          $rkabDestinasi = $qKabupatenDestinasi->fetch();
          $kodeKabDestinasi = $rkabDestinasi['kabupatenKODE'];
          //END CARI KODE KABUPATEN

      $stmt = $connection->prepare("INSERT INTO hasilobyek  VALUES (:kodeAWAL,:destinasi,:jarak,:tempuh,:jumlahjarak,:jumlahwaktu,:kabupatenKODEawal,:kabupatenKODEberikut,:customer,:i)");
      $stmt->execute(['kodeAWAL'=>$kodeAWAL,'destinasi'=>$destinasi,'jarak'=>$jarak,'tempuh'=>$tempuh,'jumlahjarak'=>$jumlahjarak,'jumlahwaktu'=>$jumlahwaktu,'kabupatenKODEawal'=>$kodeKabAwal,'kabupatenKODEberikut'=>$kodeKabDestinasi, 'customer'=>$customer,'i'=>$i]);

      //INSERT INTO HASIL KUERI
      //SELECT kdoe awal
      if($control == 1){
        $qselectAWAL = $connection->prepare("SELECT * FROM obyekwisata WHERE obyekKODE = :kode ");
      $qselectAWAL->execute(['kode'=>$kodeAWAL]);
      $rselectAWAL = $qselectAWAL->fetch();

      $obyekKODE = $rselectAWAL['obyekKODE'];
      $kecamatanKODE = $rselectAWAL['kecamatanKODE'];
      $kategoriKODE = $rselectAWAL['kategoriKODE'];
      $obyekPOP = $rselectAWAL['obyekPOPULARITAS'];
      $obyekKEM = $rselectAWAL['obyekKEMUDAHAN'];
      $obyekWAK = $rselectAWAL['obyekWAKTUKUNJUNG'];
      $jamBUKA = $rselectAWAL ['obyekJAMBUKA'];
      $jamTUTUP = $rselectAWAL ['obyekJAMTUTUP'];
      $custom = '';

      $stmt = $connection->prepare("INSERT INTO hasilkueri VALUES (:obyekKODE,:kecamatanKODE,:kabupatenKODE,:kategoriKODE,:obyekPOPULARITAS,:obyekKEMUDAHAN,:obyekWAKTUKUNJUNG,:jamBUKA,:jamTUTUP,:customer) ");
      $stmt->execute(['obyekKODE'=>$obyekKODE,'kecamatanKODE'=>$kecamatanKODE,'kabupatenKODE'=>$kodeKabAwal,'kategoriKODE'=>$kategoriKODE,'obyekPOPULARITAS'=>$obyekPOP,'obyekKEMUDAHAN'=>$obyekKEM,'obyekWAKTUKUNJUNG'=>$obyekWAK,'jamBUKA'=>$jamBUKA,'jamTUTUP'=>$jamTUTUP,'customer'=>$custom]);
      $control = $control+1;
      }
      
      //END SELECT kode awal

      //SELECT KODE tujuan
      $qselectBERIKUT = $connection->prepare("SELECT * FROM obyekwisata WHERE obyekKODE = :kode ");
      $qselectBERIKUT->execute(['kode'=>$destinasi]);
      $rselectBERIKUT = $qselectBERIKUT->fetch();

      $obyekKODE = $rselectBERIKUT['obyekKODE'];
      $kecamatanKODE = $rselectBERIKUT['kecamatanKODE'];
      $kategoriKODE = $rselectBERIKUT['kategoriKODE'];
      $obyekPOP = $rselectBERIKUT['obyekPOPULARITAS'];
      $obyekKEM = $rselectBERIKUT['obyekKEMUDAHAN'];
      $obyekWAK = $rselectBERIKUT['obyekWAKTUKUNJUNG'];
      $jamBUKA = $rselectBERIKUT ['obyekJAMBUKA'];
      $jamTUTUP = $rselectBERIKUT ['obyekJAMTUTUP'];
      $custom = '';

      $stmt = $connection->prepare("INSERT INTO hasilkueri VALUES (:obyekKODE,:kecamatanKODE,:kabupatenKODE,:kategoriKODE,:obyekPOPULARITAS,:obyekKEMUDAHAN,:obyekWAKTUKUNJUNG,:jamBUKA,:jamTUTUP,:customer) ");
      $stmt->execute(['obyekKODE'=>$obyekKODE,'kecamatanKODE'=>$kecamatanKODE,'kabupatenKODE'=>$kodeKabDestinasi,'kategoriKODE'=>$kategoriKODE,'obyekPOPULARITAS'=>$obyekPOP,'obyekKEMUDAHAN'=>$obyekKEM,'obyekWAKTUKUNJUNG'=>$obyekWAK,'jamBUKA'=>$jamBUKA,'jamTUTUP'=>$jamTUTUP,'customer'=>$custom]);


       //END SELECT kode tujuan
      //END INSERT INTO HASIL KUERI

      //RESET
      $attraction = 0;
      $attraction2 = 0;
      $kodeAWAL = $destinasi;
      //END RESET
    }
    
    //END TEST

    
  } 
  //END GREEDY 2

 
  
}
//END SHOW BUTTON


//QUERY 
$qdisticntAsal = $connection->prepare("SELECT DISTINCT kabupatenNAMA FROM kabupaten");
$qdisticntAsal->execute();


$qHasilKueri = $connection->prepare("SELECT ow.obyekNAMA, kec.kecamatanNAMA, kab.kabupatenNAMA, kat.kategoriNAMA, hk.obyekPOPULARITAS, hk.obyekKEMUDAHAN, hk.obyekWAKTUKUNJUNG, hk.jamBUKA,hk.jamTUTUP  FROM hasilkueri hk, obyekwisata ow, kecamatan kec, kabupaten kab, kategoriwisata kat WHERE hk.obyekKODE = ow.obyekKODE AND hk.kecamatanKODE = kec.kecamatanKODE AND hk.kabupatenKODE = kab.kabupatenKODE AND hk.kategoriKODE = kat.kategoriKODE ");
$qHasilKueri->execute();
//END QUERY

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <?php include "include/import.php" ?>

</head>

<body>

  <!-- ======= Header ======= -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
 <?php include "include/menu.php" ?>     
 
  <!-- End Header -->

  <!-- ======= Hero Section ======= -->
  <section id="hero">
    <div class="hero-container" data-aos="fade-up">
      <h1>Welcome to Pesona Jawa</h1>
      <h2>make your travel easier</h2>
      <a href="#input" class="btn-get-started scrollto">Mulai</a>
    </div>
  </section><!-- End Hero -->

  <main id="main">

    <!-- ======= About Section ======= -->
    
    <form method="POST">
    <section id="input" class="about">
      <div class="container" data-aos="fade-up">

        

        <div class="row">

          <div class="col-lg-6 video-box align-self-baseline" style="margin-top : 16px; margin-bottom: 16px;">
              <img src="images/background.jpg" class="img-fluid" alt="">
            <a href="https://youtu.be/U5DvqDLSxbQ" class="venobox play-btn mb-4" data-vbtype="video" data-autoplay="true"></a>
          </div>

          <div class="col-lg-6 pt-3 pt-lg-0 content" style="margin-top : 16px">
            <h3>Travel Guide</h3>
            <p class="font-italic">
              new and easy way to plan your trip
            </p>
            <div class = "form-box" method="POST">
                <div class="booking-form">
                  
                  <label>Choose Destination</label>
                  
                  <select class="form-control select2" name = "mulai">

                      <?php 
                      $control = 0; //variabel control
                      if($qdisticntAsal->rowCount()>0){
                        while($row=$qdisticntAsal->fetch()){?>

                          <?php 
                          if ($resultPage == 0){?>
                              <option>
                                <?php echo $row["kabupatenNAMA"] ?>

                              </option>
                          <?php } else if ($resultPage == 1){ ?>
                            
                                <?php if($control == 0){?>
                                  <option selected>
                                  <?php echo $kabupatenNAMA; ?>
                                  </option>
                               <?php }  ?>
                            
                              
                              
                                <?php 
                                  if($row['kabupatenNAMA']!= $kabupatenNAMA){ ?>
                                    <option>
                                    <?php echo $row["kabupatenNAMA"]; ?>
                                    </option>
                                  <?php }
                                ?>
                              
                          <?php }
                          ?>
                          

                        <?php $control = $control+1; }

                      } ?>
                  </select>

                  <div class="input-grp">
                  <label>Start</label>
                  <input type="date" class="form-control select-date" name = "dtpStart" value = "<?php echo $dateStart ?>">
                </div>

                <div class="input-grp">
                  <label>End</label>
                  <input type="date" class="form-control select-date" name = "dtpEnd" value = "<?php echo $dateEnd ?>">
                </div>

                 <div class="input-grp">
                  <label>Attraction</label>
                    <div class="custom-control custom-checkbox" style="margin-left: -24px">
                      <?php if ($popular !='') { ?>
                          <input type="checkbox" class="custom-control-input" id="popularCheck" name = "cbPopular" value = "cbPopular" checked="">
                          <label class = "custom-control-label" for ="popularCheck" style="padding-left: 20px; padding-top: 3px">Popular</label>
                      <?php } else { ?>
                      <input type="checkbox" class="custom-control-input" id="popularCheck" name = "cbPopular" value = "cbPopular">
                      <label class = "custom-control-label" for ="popularCheck" style="padding-left: 20px; padding-top: 3px">Popular</label>
                    <?php } ?>
                    </div>
                    <div class="custom-control custom-checkbox" style="margin-left: -24px">
                      <?php if ($downtown !='') { ?>
                          <input type="checkbox" class="custom-control-input" id="centerCheck" name = "cbDowntown" value="cbDowntown" checked>
                          <label class = "custom-control-label" for ="centerCheck" style="padding-left: 20px; padding-top: 3px">Downtown / Pusat Kota</label>
                      <?php } else { ?>
                          <input type="checkbox" class="custom-control-input" id="centerCheck" name = "cbDowntown" value="cbDowntown">
                          <label class = "custom-control-label" for ="centerCheck" style="padding-left: 20px; padding-top: 3px">Downtown / Pusat Kota</label>
                      <?php } ?>
                    </div>

                </div>

                  
                  <br>
                  
                  <div class="input-grp">

                    <button type="submit" class="btn btn-primary travel" name = "Show" value="Show">Show</button>
                    
                  </div>
                  

                </div>
              </div>


          </div>

        

        </div>
      </div>


       <script>$('.select2').select2(); </script>


    </section><!-- End About Section -->

    <!--SHOW RESULT-->
    <?php
    if($resultPage==1){
      //HITUNG HARI
      $save = 0;
      $dateMulai = strtotime($dateStart);
      $dateSelesai= strtotime($dateEnd);
      $totalDays = round(abs($dateSelesai-$dateMulai) / (60*60*24),0);
      //END HITUNG HARI

      //VARIABEL KONTROL TIME
      $continuesTime = new DateTime($dateStart);
      $destinationTime = new DateTime ($dateEnd);

      $continuesTime->add(new DateInterval('PT8H')); // mulai dari jam 8 pagi.
      $destinationTime->add(new DateInterval('PT17H'));//selesai jam 5 sore.
      //END VKONTROL
      ?>
      
      <section id="faq" class="faq section-bg">
      <div class="container"> 

        <div class="section-title" data-aos="fade-up">
          <h2>Your <?php echo $totalDays  ?> Days Trip Plan Start From <?php echo $kabupatenNAMA ?> </h2>
          <p><?php echo date('D',$dateMulai).', '.date('j',$dateMulai).' '.date('F',$dateMulai).' '.date('Y',$dateMulai).' - '.date('D',$dateSelesai).', '.date('j',$dateSelesai).' '.date('F',$dateSelesai).' '.date('Y',$dateSelesai) ;?></p>
        </div>

      <?php
      //OUTPUT
      $customer = '';
      $lamaperjalanan = 0;
      $controls = 1;
      while($continuesTime->getTimeStamp() < $destinationTime->getTimestamp()){?>


      <div class="row faq-item d-flex align-items-stretch" data-aos="fade-up">
          
          <div class="col-lg-5">
            <i class="bx bx-check-circle"></i>
            <h4><?php $stampLEFT = $continuesTime->format('D ,d F Y'); echo $stampLEFT; ?></h4>
          </div>
          <div class="col-lg-7">
              <?php
              $stampHour = $continuesTime->format('H');
              $stampMinute = $continuesTime->format('i');
              while($stampHour < 17){
                //break
                $qJumlah = $connection->prepare("SELECT*FROM hasilkueri");
                $qJumlah->execute();

                if($qJumlah->rowCount()==0 OR $save==$stampHour) break;
                $save = $stampHour;
                //end break

                //ISI
                $qHasilObyek = $connection->prepare("SELECT ho.* FROM hasilobyek ho, hasilkueri hk WHERE hk.obyekKODE = ho.obyekKODEasal LIMIT 1");
                $qHasilObyek->execute();

                if($qHasilObyek->rowCount()>0){
                  $rHasilObyek = $qHasilObyek->fetch();
                $lamaperjalanan = $rHasilObyek['obyekwaktutempuh'];
                $jarakperjalanan = $rHasilObyek['obyekjarak'];

                //KALO BEDA KODE CUST
                if($customer != $rHasilObyek['Customer_ID']){
                  $customer = $rHasilObyek['Customer_ID'];
                  //CARI NAMA AWAL
                  $kode=$rHasilObyek['obyekKODEasal'];
                  $qhasil = $connection->prepare("SELECT*FROM hasilkueri WHERE obyekKODE = :kode ");
                  $qhasil->execute(['kode'=>$kode]);
                  $rhasil = $qhasil->fetch();
                    //CARI KODE
                  $kecamatan = $rhasil['kecamatanKODE'];
                  $kategori = $rhasil['kategoriKODE'];
                  $kabupaten = $rhasil['kabupatenKODE'];
                   $arr  =  str_split($kecamatan,5);
                  $kabupaten = $arr[0];
                  $waktukunjung = $rhasil['obyekWAKTUKUNJUNG'];
                  $jamBUKA = $rhasil['jamBUKA'];
                  $jamTUTUP =  $rhasil['jamTUTUP'];
                    //END CARI KODE
                  //kecamatan
                  $qnama = $connection->prepare("SELECT*FROM kecamatan WHERE kecamatanKODE = :kode ");
                  $qnama->execute(['kode'=>$kecamatan]);
                  $r = $qnama->fetch();
                  $kecamatanNAMA = $r['kecamatanNAMA'];

                  //kabupaten
                    $qnama = $connection->prepare("SELECT*FROM kabupaten WHERE kabupatenKODE = :kode ");
                    $qnama->execute(['kode'=>$kabupaten]);
                    $r = $qnama->fetch();
                    $kabupatenNAMA = $r['kabupatenNAMA'];

                    

                  //kategori
                  $qnama = $connection->prepare("SELECT*FROM kategoriwisata WHERE kategoriKODE = :kode ");
                  $qnama->execute(['kode'=>$kategori]);
                  $r = $qnama->fetch();
                  $kategoriNAMA = $r['kategoriNAMA'];

                  //obyek
                  $qnama = $connection->prepare("SELECT*FROM obyekwisata WHERE obyekKODE = :kode ");
                  $qnama->execute(['kode'=>$kode]);
                  $r = $qnama->fetch();
                  $obyekNAMA = $r['obyekNAMA'];

                  //DELETE ISI HASILKUERI
                  $stmt= $connection->prepare("DELETE FROM hasilkueri WHERE obyekKODE = :kode ");
                  $stmt->execute(['kode'=>$kode]);
                  //END CARI NAMA AWAL

                  //OUTPUT AWAL
                  ?>
                   <?php
                    if($controls==1){
                      $controls = $controls+1;
                    }else{ ?>
                      <p style="font-weight: bold"><?php echo "Jarak Perjalanan menuju ".$obyekNAMA." : [belum diketahui]"  ?></p>
                      <p style="font-weight: bold"><?php echo "Lama Perjalanan menuju ".$obyekNAMA." : [belum diketahui]"  ?></p>
                   <?php  }
                    ?>
                    <h3><?php echo $obyekNAMA ?></h3> 
                    <p style="font-weight: bold"><?php echo $kecamatanNAMA.', '.$kabupatenNAMA; ?></p>
                    <p style="font-weight: bold"><?php echo $kategoriNAMA ?></p>
                    
                    
                    <p><?php 
                    $stampDay = $continuesTime->format('D'); 
                    $stampTimeStart = $continuesTime->format('H : i');
                    $continuesTime->add(new DateInterval('PT'.$waktukunjung.'M')); //DATA WAKTU KUNJUNG
                    $stampTimeEnd = $continuesTime->format('H : i');
                    echo $stampDay.' '.$stampTimeStart.' - '.$stampTimeEnd; ?></p>
                    <p>-------------------------------------------------------------------</p>
                    <br>
                
                  <?php
                  //END OUTPUT AWAL

                  //CARI NAMA BERIKUT
                  $kode=$rHasilObyek['obyekKODEtujuan'];
                  if($kode!= ''){
                    $qhasil = $connection->prepare("SELECT*FROM hasilkueri WHERE obyekKODE = :kode ");
                    $qhasil->execute(['kode'=>$kode]);
                    $rhasil = $qhasil->fetch();
                      //CARI KODE
                    $kecamatan = $rhasil['kecamatanKODE'];
                    $kategori = $rhasil['kategoriKODE'];
                    $kabupaten = $rhasil['kabupatenKODE'];
                    $waktukunjung = $rhasil['obyekWAKTUKUNJUNG'];
                    $jamBUKA = $rhasil['jamBUKA'];
                    $jamTUTUP =  $rhasil['jamTUTUP'];
                      //END CARI KODE
                    //kecamatan
                    $qnama = $connection->prepare("SELECT*FROM kecamatan WHERE kecamatanKODE = :kode ");
                    $qnama->execute(['kode'=>$kecamatan]);
                    $r = $qnama->fetch();
                    $kecamatanNAMA = $r['kecamatanNAMA'];

                    //kabupaten
                    $qnama = $connection->prepare("SELECT*FROM kabupaten WHERE kabupatenKODE = :kode ");
                    $qnama->execute(['kode'=>$kabupaten]);
                    $r = $qnama->fetch();
                    $kabupatenNAMA = $r['kabupatenNAMA'];

                    //kategori
                    $qnama = $connection->prepare("SELECT*FROM kategoriwisata WHERE kategoriKODE = :kode ");
                    $qnama->execute(['kode'=>$kategori]);
                    $r = $qnama->fetch();
                    $kategoriNAMA = $r['kategoriNAMA'];

                    //obyek
                    $qnama = $connection->prepare("SELECT*FROM obyekwisata WHERE obyekKODE = :kode ");
                    $qnama->execute(['kode'=>$kode]);
                    $r = $qnama->fetch();
                    $obyekNAMA = $r['obyekNAMA'];

                    

                    //OUTPUT BERIKUT
                    ?>
                    <p style="font-weight: bold"><?php echo "Jarak Perjalanan menuju ".$obyekNAMA." : ".$jarakperjalanan.' km'  ?></p>
                    <p style="font-weight: bold"><?php echo "Lama Perjalanan menuju ".$obyekNAMA." : ".$lamaperjalanan.' menit'  ?></p>
                    <h3><?php echo $obyekNAMA ?></h3>
                    <p style="font-weight: bold"><?php echo $kecamatanNAMA.', '.$kabupatenNAMA; ?></p>
                    <p style="font-weight: bold"><?php echo $kategoriNAMA ?></p>
                    
                    <?php $continuesTime->add(new DateInterval('PT'.$lamaperjalanan.'M')); //DATA LAMA PERJALANAN ?> 
                    
                    <p><?php 
                    $stampDay = $continuesTime->format('D'); 
                    $stampTimeStart = $continuesTime->format('H : i');
                    $continuesTime->add(new DateInterval('PT'.$waktukunjung.'M')); //DATA WAKTU KUNJUNG
                    $stampTimeEnd = $continuesTime->format('H : i');
                    echo $stampDay.' '.$stampTimeStart.' - '.$stampTimeEnd; ?></p>
                    <p>-------------------------------------------------------------------</p>
                    <br>
                    <?php
                  }
                  
                  //END OUTPUT BERIKUT

                }
                //END BEDA KODE CUST

                //SAMA CUST
                else{
                  //CARI NAMA BERIKUT
                  $kode=$rHasilObyek['obyekKODEtujuan'];
                  $kodeDEL = $rHasilObyek['obyekKODEasal'];
                  if($kode!= ''){
                  
                    $qhasil = $connection->prepare("SELECT*FROM hasilkueri WHERE obyekKODE = :kode ");
                    $qhasil->execute(['kode'=>$kode]);

                    if($qhasil->rowCount()>0){
                      $rhasil = $qhasil->fetch();
                      //CARI KODE
                      $kecamatan = $rhasil['kecamatanKODE'];
                      $kategori = $rhasil['kategoriKODE'];
                      $kabupaten = $rhasil['kabupatenKODE'];
                      $waktukunjung = $rhasil['obyekWAKTUKUNJUNG'];
                      $jamBUKA = $rhasil['jamBUKA'];
                      $jamTUTUP =  $rhasil['jamTUTUP'];
                        //END CARI KODE
                      //kecamatan
                      $qnama = $connection->prepare("SELECT*FROM kecamatan WHERE kecamatanKODE = :kode ");
                      $qnama->execute(['kode'=>$kecamatan]);
                      $r = $qnama->fetch();
                      $kecamatanNAMA = $r['kecamatanNAMA'];

                      //kabupaten
                      $qnama = $connection->prepare("SELECT*FROM kabupaten WHERE kabupatenKODE = :kode ");
                      $qnama->execute(['kode'=>$kabupaten]);
                      $r = $qnama->fetch();
                      $kabupatenNAMA = $r['kabupatenNAMA'];

                      //kategori
                      $qnama = $connection->prepare("SELECT*FROM kategoriwisata WHERE kategoriKODE = :kode ");
                      $qnama->execute(['kode'=>$kategori]);
                      $r = $qnama->fetch();
                      $kategoriNAMA = $r['kategoriNAMA'];

                      //obyek
                      $qnama = $connection->prepare("SELECT*FROM obyekwisata WHERE obyekKODE = :kode ");
                      $qnama->execute(['kode'=>$kode]);
                      $r = $qnama->fetch();
                      $obyekNAMA = $r['obyekNAMA'];

                      //DELETE ISI HASILKUERI
                      $stmt= $connection->prepare("DELETE FROM hasilkueri WHERE obyekKODE = :kode ");
                      $stmt->execute(['kode'=>$kodeDEL]);
                      //END CARI NAMA BERIKUT
                    }
                    

                    //OUTPUT BERIKUT
                    ?>
                    <p style="font-weight: bold"><?php echo "Jarak Perjalanan menuju ".$obyekNAMA." : ".$jarakperjalanan.' km'  ?></p>
                    <p style = "font-weight: bold"><?php echo "Lama Perjalanan menuju ".$obyekNAMA." : ".$lamaperjalanan.' menit'  ?></p>
                    <h3><?php echo $obyekNAMA ?></h3>
                    <p style = "font-weight: bold"><?php echo $kecamatanNAMA.', '.$kabupatenNAMA; ?></p>
                    <p style="font-weight: bold"><?php echo $kategoriNAMA ?></p>
                    
                    <?php  $continuesTime->add(new DateInterval('PT'.$lamaperjalanan.'M')); //DATA LAMA PERJALANAN ?> 
                    
                    <p><?php 
                    $stampDay = $continuesTime->format('D'); 
                    $stampTimeStart = $continuesTime->format('H : i');
                    $continuesTime->add(new DateInterval('PT'.$waktukunjung.'M')); //DATA WAKTU KUNJUNG
                    $stampTimeEnd = $continuesTime->format('H : i');
                    echo $stampDay.' '.$stampTimeStart.' - '.$stampTimeEnd; ?></p>
                    <p>-------------------------------------------------------------------</p>
                    <br>
                    <?php
                  }
                  
                  //END OUTPUT BERIKUT
                }
                //END SAMA CUST
                }
                

                //END ISI

                //RESET IN LOOP P
                $stampHour = $continuesTime->format('H');
                //TEST
                $stampMinute = $continuesTime->format('i');
                //END RESET IN LOOP
              }
              ?>
              
       </div>
      </div><!-- End F.A.Q Item-->

       
        <?php
        //RESET LOOP 1
        //TEST
        $addMinute = 60-$stampMinute;
        $addHour = 20-($stampHour-11); //PR
        $continuesTime->add(new DateInterval('PT'.$addHour.'H'));
        $continuesTime->add(new DateInterval('PT'.$addMinute.'M'));

        //END RESET
      }

      //END OUTPUT
    }
    ?>
    <!--END SHOW RESULT-->


  
 <!-- </div>
</section> -->



 
    
  </form>



    

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <?php include "include/footer.php" ?>
  <!-- End Footer -->

  <!--=======Java Script========-->
  <?php include "include/js.php" ?>


  <!--=======END Java Script========-->

  <!-- Material unchecked -->

</body>

</html>