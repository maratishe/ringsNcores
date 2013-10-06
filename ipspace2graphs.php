<?php
set_time_limit( 0);
ob_implicit_flush( 1);
//ini_set( 'memory_limit', '4000M');
for ( $prefix = is_dir( 'ajaxkit') ? 'ajaxkit/' : ''; ! is_dir( $prefix) && count( explode( '/', $prefix)) < 4; $prefix .= '../'); if ( ! is_file( $prefix . "env.php")) $prefix = '/web/ajaxkit/'; if ( ! is_file( $prefix . "env.php")) die( "\nERROR! Cannot find env.php in [$prefix], check your environment! (maybe you need to go to ajaxkit first?)\n\n");
foreach ( array( 'functions', 'env') as $k) require_once( $prefix . "$k.php"); clinit(); 
clhelp( 'PURPOSE: to process a bunch of .ipstats files and produce graphs for each of them');
clhelp( 'NOTE: outputs raw.graphs.bz64jsonl  -- each line: { time, graph: { ip: { ip: { ....}}}}');
//htg( clget( ''));

echo "\n\n"; $e = echoeinit(); $out = foutopen( 'raw.graphs.bz64jsonl', 'w');
foreach ( flget( '.', '', '', 'ipstats') as $file) { 
	$H = array(); $in = finopen( $file); 
	while ( ! findone( $in)) {
		list( $h, $p) = finread( $in); if ( ! $h) continue; echoe( $e, "$file $p");
		extract( $h); // ip, packetsin, packetsout
		for ( $mask = 2; $mask < 20; $mask++) { 
			$mip = bhead( $ip, $mask);
			htouch( $H, "$mask"); htouch( $H[ "$mask"], "$mip", 0, false, false); 
			$H[ "$mask"][ "$mip"] += $packetsin + $packetsout; 
		}
		
	}
	finclose( $in);
	foutwrite( $out, $H);
}
foutclose( $out); echo " OK\n";

?>