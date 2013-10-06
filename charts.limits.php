<?php
set_time_limit( 0);
ob_implicit_flush( 1);
//ini_set( 'memory_limit', '4000M');
for ( $prefix = is_dir( 'ajaxkit') ? 'ajaxkit/' : ''; ! is_dir( $prefix) && count( explode( '/', $prefix)) < 4; $prefix .= '../'); if ( ! is_file( $prefix . "env.php")) $prefix = '/web/ajaxkit/'; if ( ! is_file( $prefix . "env.php")) die( "\nERROR! Cannot find env.php in [$prefix], check your environment! (maybe you need to go to ajaxkit first?)\n\n");
foreach ( array( 'functions', 'env') as $k) require_once( $prefix . "$k.php"); clinit(); 
//clhelp( '');
//htg( clget( ''));

$FS = 16; $BS = 4.5;
$S = new ChartSetupStyle(); $S->style = 'D'; $S->lw = 0.1; $S->draw = '#000'; $S->fill = null;
$R = clone $S; $S2 = $R; $R->style = 'F'; $R->draw = null; $R->lw = 0; $R->fill = '#000'; $R->alpha = 0.4;
$R = clone $S2; $S3 = $R; $R->alpha = 0.6; $R->fill = '#f00'; $R->alpha = 0.8;

echo "\n\n"; $e = echoeinit(); $in = finopen( 'raw.graphs.bz64jsonl'); `rm -Rf tempdf*`; `rm -Rf charts.limits.pdf`; $pos = 0;
while ( ! findone( $in)) {
	list( $graph, $p) = finread( $in); if ( ! $graph) continue; echoe( $e, $p);
	for ( $i = 11; $i < 21; $i++) unset( $graph[ "$i"]);	// remove too deep masks
	$stats = array(); $maxmask = mmax( hk( $graph));
	foreach ( $graph as $mask => $h) $stats[ "$mask"] = msum( hv( $h));
	$vs = array(); // [ { mask: ratio ( heavy hitters / total packets)}, ...]
	for ( $i = 1; $i < 25; $i++) { $h = array(); foreach ( $graph as $mask => $h2) { 
		$vs2 = hv( $h2); rsort( $vs2, SORT_NUMERIC); 
		$top = array(); for ( $ii = 0; $ii < $i && isset( $vs2[ $ii]); $ii++) $top[ $ii] = $vs2[ $ii];
		$ratio = msum( $top) / msum( $vs2);
		$h[ "$mask"] = $ratio;
	}; ksort( $h, SORT_NUMERIC); lpush( $vs, $h); }
	// chart
	list( $C, $CS, $CST) = chartlayout( new MyChartFactory(), 'P', '1x1', 30, '0.2:0.1:0.25:0.15'); $C2 = lshift( $CS);
	foreach ( $vs as $h) $C2->train( hk( $h), hv( $h));
	$C2->autoticks( null, null, 10, 10);
	$C2->frame( 'IP Address Mask (suffix bit length)', 'Ratio (heavy hitters to all packets)');
	foreach ( $vs as $h) chartline( $C2, hk( $h), hv( $h), $S);
	$C->dump( sprintf( 'tempdf.%03d.pdf', $pos)); $pos++; 
}
finclose( $in); echo " OK\n";
echo "pdftk..."; procpdftk( 'tempdf*', 'charts.limits.pdf'); echo " OK\n";

?>