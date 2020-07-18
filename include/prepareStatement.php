<?php
include "config.php"; 
     if(isset($_post['Show']))
 	 {	
		if (isset($_request["kabupatenKode"]))
			{	$kabupatenKODE = $_request["kabupatenKode"];
			} 
		if (!empty($kabupatenKODE))
			{	$kabupatenKODE = $_post['kabupatenKode'];	
			}
		$kabupatenNAMA = $_post['kabupatenNama'];
		$kabupatenALAMAT= $_post['kabupatenAlamat'];
		$kabupatenKET = $_post['kabupatenKet'];
	$nama = $_fiels['file']['name']; 
	$file_tmp = $_files["file"]["tmp_name"];
	move_uploaded_file($file_tmp, 'images/iconkabupaten/'.$nama);
		$kabupatenFOTOICONKET = $_post['kabupatenFotoIconKet'];			
		
	/* Melakukan kueri prepare statement (stmt) */
	$stmt = $connection->prepare('insert into kabupaten values
	(:kodekab, :namakab, :alamatkab, :keterangankab, :namafotokab, :namaketfoto)');	   

	/* Menjalankan query (execute) */
	$stmt->execute(array(':kodekab' => $kabupatenKODE, ':namakab' => $kabupatenNAMA,
	':alamatkab' => $kabupatenALAMAT, ':keterangankab' => $kabupatenKET, 
	':namafotokab' => $nama, ':namaketfoto' => $kabupatenFOTOICONKET));
	}
	
	//membuat pagination
	$jumlahtampil = 5;
	$halaman = @$_GET['page'];
	if(empty($halaman))
	{
		$posisi_record = 0;
		$halaman = 1;
	}else {
		$posisi_record = ($halaman - 1) * $jumlahtampil;
	}
	
	$query = $connection->query("select * from kabupaten limit $posisi_record, $jumlahtampil");


	?>