<?php
set_time_limit( 0);
ob_implicit_flush( 1);
//ini_set( 'memory_limit', '4000M');
for ( $prefix = is_dir( 'ajaxkit') ? 'ajaxkit/' : ''; ! is_dir( $prefix) && count( explode( '/', $prefix)) < 4; $prefix .= '../'); if ( ! is_file( $prefix . "env.php")) $prefix = '/web/ajaxkit/'; if ( ! is_file( $prefix . "env.php")) die( "\nERROR! Cannot find env.php in [$prefix], check your environment! (maybe you need to go to ajaxkit first?)\n\n");
foreach ( array( 'functions', 'env') as $k) require_once( $prefix . "$k.php"); clinit(); 
//clhelp( '');
//htg( clget( ''));


// read data
echo "\n\n"; $H = array(); $e = echoeinit();  // { method: { mask: { diffs: [ diff sum, ...], counts: [ count, ...]} ...}, ...}
foreach ( ttl( 'spread,history') as $method) { $in = finopen( "raw.$method.bz64jsonl"); while ( ! findone( $in)) { 
	list( $h, $p) = finread( $in); if ( ! $h) continue; echoe( $e, "reading $method $p");
	extract( $h); // MASKDEPTH, eval, gene, GRAPH
	htouch( $H, "$method"); htouch( $H[ "$method"], "$MASKDEPTH");
	$L = array(); foreach ( $gene as $c) lpush( $L, msum( hv( $c)));
	sort( $L, SORT_NUMERIC); $L = mdistance( $L);
	htouch( $H[ "$method"][ "$MASKDEPTH"], 'diffs'); lpush( $H[ "$method"][ "$MASKDEPTH"][ 'diffs'], msum( $L)); // diffs
	htouch( $H[ "$method"][ "$MASKDEPTH"], 'counts'); foreach ( $gene as $c) lpush( $H[ "$method"][ "$MASKDEPTH"][ 'counts'], count( $c));
}; finclose( $in); }
echo " OK\n";
foreach ( $H as $m => $h1) { ksort( $h1, SORT_NUMERIC); $out = foutopen( "data.$m.bz64jsonl", 'w'); foreach ( $h1 as $mask => $h2) { 
	extract( $h2); // counts, diffs
	$countavg = mavg( $counts); $countvar = mvar( $counts); $countmin = $countavg - $countvar; $countmax = $countavg + $countvar; if ( $countmin < 0) $countmin = 0;
	for ( $i = 0; $i < count( $diffs); $i++) $diffs[ $i] = 0.001 * $diffs[ $i];
	$diffavg = mavg( $diffs); $diffvar = mavg( $diffs); $diffmin = $diffavg - $diffvar; $diffmax = $diffavg + $diffvar; if ( $diffmin < 0) $diffmin = 0;
	foutwrite( $out, compact( ttl( 'mask,countavg,countvar,countmin,countmax,diffavg,diffvar,diffmin,diffmax'))); 
}; foutclose( $out); }


?>