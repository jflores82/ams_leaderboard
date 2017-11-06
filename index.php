<?PHP 

// AMS Leaderboard - XML results extracting and parsing. 
// Reading DB Module // 
// Programmed by Juliano F. - classicgames.com.br //
// Support free technologies. //

include ('_settings.php'); 				// Loads all the settings // 
include ('_dbconn.php'); 				// Connects to the database //
include ('_functions.php'); 			// All the custom functions //
$langtext = process_lang($langfile); 	// Load up the text //

// Here we peocess the variable V, which can come from POST or GET, and we separate the results into $server and $track //
if(isset($_GET['v'])) { 
	$v = $_GET['v'];
	$v_explode = explode('|', $v);
	$server = $v_explode[0];
	$track = $v_explode[1];
}
if(isset($_POST['v'])) { 
	$v = $_POST['v'];
	$v_explode = explode('|', $v);
	$server = $v_explode[0];
	$track = $v_explode[1];
}

// We can also get $server and $track already split, both from POST and GET //
if(isset($_GET['s'])) { $server = $_GET['s']; }
if(isset($_GET['t'])) { $track = $_GET['t']; }
if(isset($_POST['s'])) { $server = $_GET['s']; }
if(isset($_POST['t'])) { $server = $_POST['t']; }

// Used to create a loop where each iteration is a different driver. //
$query_laps = "select distinct driver_name from laps where (server = '$server' and track = '$track');";
$laps_result = mysqli_query($db, $query_laps);

// Get the time/date when the last lap was recorded on the db. //
$update_q = "select update_time from laps order by pk_id desc limit 1";
$update_r = mysqli_query($db, $update_q);
while($i = mysqli_fetch_array($update_r)) { $update_time = $i['update_time']; }

// Used to create the server/track combobox. //
$query_track = "SELECT distinct track, server FROM `laps`";
$r_track = mysqli_query($db, $query_track);
?>

<html>
	<head>
		<meta charset="utf-8">
		<title><?PHP echo $server_title; ?></title>
		
		<link href="https://fonts.googleapis.com/css?family=Montserrat:200" rel="stylesheet"> 
		<link href="style.css" rel="stylesheet">
	</head>
	
	<body>
		<span class="font-montserrat">
			<?PHP echo $langtext[1]; ?>
			<form action="index.php" method="post">
				<select name="v">
				<?PHP while($i_t = mysqli_fetch_array($r_track)) { ?>
					<option value="<?PHP echo $i_t['server']."|".$i_t['track']; ?>" <?PHP if( ($i_t['track'] == $track) and ($i_t['server'] == $server) ) { echo "selected"; } ?>><?PHP echo $i_t['server'].' - '.$i_t['track']; ?></option>
				<?PHP } ?>
				</select>
				<input type="submit" value="Filtrar">
			</form>
		
			<div style="text-align:right;padding-right:5%;" class="font-montserrat">
				<?PHP echo $langtext[2]; ?>: Juliano F. @ <a href="http://www.classicgames.com.br/">classicgames.com.br</a>
			</div>
	
			<?PHP echo $langtext[3]; ?>: <?PHP echo $server; ?><br>
			<?PHP echo $langtext[4]; ?>: <?PHP echo $track; ?><br>
			<!-- Última Atualização: <?PHP echo $update_time; ?> -->
		</span>
		<br><br>
		<table class="table font-montserrat" cellspacing="0" cellpadding="0">
			<tr class="table_header">
				<th><?PHP echo $langtext[5]; ?></th>
				<th><?PHP echo $langtext[6]; ?></th>
				<th><?PHP echo $langtext[7]; ?></th>
				<th><?PHP echo $langtext[8]; ?></th>
				<th>&nbsp;</th>
				<th><?PHP echo $langtext[9]; ?></th>
				<th><?PHP echo $langtext[10]; ?></th>
			</tr>
		<?PHP 
			while($r = mysqli_fetch_array($laps_result)) {
				$count = 0;
				$driver = $r['driver_name'];
				$query_driver = "select driver_name, lap_time, lap_fuel, s1, s2, s3 from laps where (server = '$server' and track = '$track' and driver_name = '$driver' and lap_time not in ('--.----')) order by lap_time ASC limit 1;";
				$min_result = mysqli_query($db, $query_driver);
				while($j = mysqli_fetch_array($min_result)) { 
					$lap_time = $j['lap_time'];
					$fuel = $j['lap_fuel'];
					$sector1_time = $j['s1'];
					$sector2_time = $j['s2'];
					$sector3_time = $j['s3'];
					$drivers[$driver] = $lap_time;
					$fuel_list[$driver] = $fuel;
					$lapid[$driver] = $j['pk_id'];
					$sector1[$driver] = $sector1_time;
					$sector2[$driver] = $sector2_time;
					$sector3[$driver] = $sector3_time;
				}
			}
			asort($drivers);
			$p = 1;
			foreach ($drivers as $key => $val) {
				$driver_name = $key;
				$id = $lapid[$driver_name];
				$q_driver_info = "select vehfile, cartype, carclass, carnumber, teamname, track_length from laps where pk_id = '$id';";
				$r_driver_info = mysqli_query($db, $q_driver_info); 
				while($j = mysqli_fetch_array($r_driver_info)) { 
					$vehfile = $j['vehfile'];
					$cartype = $j['cartype'];
					$carclass = $j['carclass'];
					$carnumber = $j['carnumber'];
					$teamname = $j['teamname'];
					$track_length = $j['track_length'];
				}
				
				// Total laps from specific driver //
				$q_tl = "select count(driver_name) as tl from laps where (server = '$server' and track = '$track' and driver_name = '$driver_name');";
				$r_tl = mysqli_query($db, $q_tl);
				while($j = mysqli_fetch_array($r_tl)) { $total_laps = $j['tl']; $total_laps_global += $total_laps; } 
				
				// Total number of Valid Laps from specific driver //
				$q_tl = "select count(driver_name) as tl from laps where (server = '$server' and track = '$track' and driver_name = '$driver_name') and (lap_time not in ('--.----'));";
				$r_tl = mysqli_query($db, $q_tl);
				while($j = mysqli_fetch_array($r_tl)) { $total_valid = $j['tl']; }
				
				// Best Sector 1 from Driver //
				$q_s1 = "SELECT driver_name, s1 as sector FROM `laps` WHERE (driver_name = '$driver_name' and (track = '$track' and server='$server') and (s1 not in (''))) order by s1 *1 limit 1;";
				$r_s1 = mysqli_query($db, $q_s1);
				while($j = mysqli_fetch_array($r_s1)) { $best_s1 = $j['sector']; array_push($best_s1_a, $best_s1); }
				
				// Best Sector 2 from Driver //
				$q_s2 = "SELECT driver_name, s2 as sector FROM `laps` WHERE (driver_name = '$driver_name' and (track = '$track' and server='$server') and (s2 not in (''))) order by s2 *1 limit 1;";
				$r_s2 = mysqli_query($db, $q_s2);
				while($j = mysqli_fetch_array($r_s2)) { $best_s2 = $j['sector']; array_push($best_s2_a, $best_s2);}
				
				// Best Sector 3 from Driver //
				$q_s3 = "SELECT driver_name, s3 as sector FROM `laps` WHERE (driver_name = '$driver_name' and (track = '$track' and server='$server') and (s3 not in (''))) order by s3 *1 limit 1;";
				$r_s3 = mysqli_query($db, $q_s3);
				while($j = mysqli_fetch_array($r_s3)) { $best_s3 = $j['sector']; array_push($best_s3_a, $best_s3);}
				
				// Driver Stats //
				$virtual_best = $best_s1 + $best_s2 + $best_s3;
				$lap_sec = $val;
				$percent_valid = ($total_valid*100) / $total_laps;
				$total_distance = $track_length * $total_laps;
				$total_dist = $total_distance / 1000;
				$total_d = round($total_dist, 2);
			?>
							
			<tr class="<?PHP decide_bg($odd, $p); ?>">
				<td rowspan="2" align="center" class="border" style="font-size:4vh;"><b><?PHP echo $p; ?></b></td>
				<td rowspan="2" class="border padding-left"><?PHP echo $driver_name; ?></td>
				<td class="border padding-left"><b><?PHP convert_seconds($lap_sec); ?></b></td>
				<td class="border padding-left"><?PHP echo $teamname; ?></td>
				<td class="border padding-left">#<?PHP echo $carnumber; ?></td>
				<td class="border padding-left"><?PHP echo $cartype." - ".$carclass." - ".$vehfile; ?></td>
				<td class="border padding-left"><?PHP echo $total_laps; ?></td>
			</tr>
			
			<tr class="<?PHP decide_bg($odd, $p); ?>">
				<td colspan="3" class="border padding-left">
					<?PHP echo $langtext[11]; ?>: <?PHP convert_seconds($virtual_best); ?><br>
					<?PHP echo $langtext[10]; ?>: <?PHP echo $total_valid; ?> <?PHP echo $langtext[13]; ?>
				</td>
				<td colspan="2" class="border" align="center">
					<?PHP echo $langtext[12]; ?>: <?PHP echo $total_d; ?> <?PHP echo $langtext[14]; ?>.
				</td>
			</tr>
				
			<?PHP 
				$p++; 
				$odd++;
				if($odd == "2") { $odd = 0; }
			?>
			<?PHP } ?>
			<?PHP 
				asort($best_s1_a);
				$bs1 = reset($best_s1_a);
				asort($best_s2_a);
				$bs2 = reset($best_s2_a);
				asort($best_s3_a);
				$bs3 = reset($best_s3_a);
				$virtual_best_global = $bs1 + $bs2 + $bs3;
			
			?>
			<tr>
				<td colspan="7" align="right"><?PHP echo $langtext[10]; ?>: <?PHP echo $total_laps_global; ?></td>
			</tr>
		</table>
	</body>
</html>