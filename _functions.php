<?PHP

// convert seconds to minutes //
function convert_seconds($lap_time) {
	list($seconds, $decs) = explode('.', $lap_time);
	$minutes = floor($seconds % 3600 / 60);
	$seconds = $seconds % 60;
	$decs = substr($decs, 0, 3);
	$secs = str_pad($seconds, 2, '0', STR_PAD_LEFT); 
	echo $minutes.":".$secs.":".$decs;
}


// color the background, according to the default css //
function decide_bg($odd, $p) { 
	if($odd == 1) { echo " grey "; }
		
	switch($p) { 
		case 1:
			echo " gold ";
		break;
		
		case 2:
			echo " silver ";
		break;
		
		case 3:
			echo " bronze ";
		break; 
	}
	
}

function process_lang ($langfile) {
	$lang_text = explode(PHP_EOL, file_get_contents($langfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	return $lang_text;
}	

?>