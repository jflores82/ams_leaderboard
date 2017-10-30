<?PHP 

$db = new mysqli('localhost', 'root', '', 'ams_leaderboard');

$data_hora = date("Y-m-d H:i:s");
$update_time = date("d/m/Y - H:i:s");

$cron_q = "INSERT INTO `cron_test` (`pk_id`, `data_hora`, `ok`, `text`) VALUES (NULL, '$data_hora', '0', '');";
$cron_r = mysqli_query($db, $cron_q);

$files = glob('xml/*.{xml}', GLOB_BRACE);
foreach($files as $file) {
	$path = $file;

	$xmlfile = file_get_contents($path);
	$xmlfile = utf8_encode($xmlfile);

	$xml = new SimpleXMLElement($xmlfile);
	$server = $xml->RaceResults->ServerName;
	$track = $xml->RaceResults->TrackCourse;
	$track_length = $xml->RaceResults->TrackLength;

	echo 'path:'.$path.'<br>';

	$cron_q = "INSERT INTO `cron_test` (`pk_id`, `data_hora`, `ok`, `text`) VALUES (NULL, '$data_hora', '1', '$path');";
	$cron_r = mysqli_query($db, $cron_q);
	echo $cron_q;
	
	$date = date("Y-m-d");

	if(isset($xml->RaceResults->Practice1)) { $session = 'Practice1'; }
	if(isset($xml->RaceResults->Practice2)) { $session = 'Practice2'; }
	if(isset($xml->RaceResults->Practice3)) { $session = 'Practice3'; }
	
	if(isset($xml->RaceResults->Race)) { $session = 'Race'; }
	if(isset($xml->RaceResults->Race2)) { $session = 'Race2'; }
	if(isset($xml->RaceResults->Race3)) { $session = 'Race3'; }
	
	if(isset($xml->RaceResults->Warmup)) { $session = 'Warmup'; }
	
	if(isset($xml->RaceResults->Qualify)) { $session = 'Qualify'; }
	if(isset($xml->RaceResults->Qualify1)) { $session = 'Qualify1'; }
	if(isset($xml->RaceResults->Qualify2)) { $session = 'Qualify2'; }
	if(isset($xml->RaceResults->Qualify3)) { $session = 'Qualify3'; }
	if(isset($xml->RaceResults->Qualify4)) { $session = 'Qualify4'; }
	if(isset($xml->RaceResults->Qualify5)) { $session = 'Qualify5'; }
	
	foreach ($xml->RaceResults->$session->Driver as $d) {
		$driver = $d->Name;
		$vehfile = $d->VehFile;
		$cartype = $d->CarType;
		$carclass = $d->CarClass;
		$carnumber = $d->CarNumber;
		$teamname = $d->TeamName;
		$driving_aids = $d->ControlAndAids;
			
		foreach($d->Lap as $lap ) {
				
			$s1 = $lap['s1'];
			$s2 = $lap['s2'];
			$s3 = $lap['s3'];
			$lap_time =  $lap[0];
			$fuel = $lap['fuel'];
				
			$query = "INSERT INTO `laps` (`pk_id`, `server`, `track`, `driver_name`, `vehfile`, `cartype`, `carclass`, `carnumber`, `teamname`, `s1`, `s2`, `s3`, `lap_time`, `lap_date`, `lap_fuel`, `session`, `xml_file`, `update_time`, `track_length`, `driving_aids`) 
			VALUES 
			(NULL, '$server', '$track', '$driver', '$vehfile', '$cartype', '$carclass', '$carnumber', '$teamname', '$s1', '$s2', '$s3', '$lap_time', '$date', '$fuel', '$session', '$path', '$update_time', '$track_length', '$driving_aids');";
			$result = $db->query($query);
			echo $query.'<br>';
			$last_update = date("d/m/Y - H:i");
		}
	}
	//unlink($file);
	$newname = $file.".old";
	rename($file, $newname);
}
?>