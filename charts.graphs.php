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

echo "\n\n"; $e = echoeinit(); $in = finopen( 'raw.graphs.bz64jsonl'); `rm -Rf tempdf*`; `rm -Rf charts.graphs.pdf`; $pos = 0;
while ( ! findone( $in)) {
	list( $graph, $p) = finread( $in); if ( ! $graph) continue; echoe( $e, $p);
	for ( $i = 11; $i < 21; $i++) unset( $graph[ "$i"]);	// remove too deep masks
	$stats = array(); $maxmask = mmax( hk( $graph));
	foreach ( $graph as $mask => $h) $stats[ "$mask"] = msum( hv( $h));
	$vs = array(); // { mask, mip, range, min, max, ratio}
	foreach ( $graph as $mask => $h) { foreach ( $h as $mip => $count) {
		$min = $mip | 0x00000000; $max = $mip & 0xffffffff; $range = $max - $min;
		$min += 0.1 * $range; $max -= 0.1 * $range;
		$ratio = $count / $stats[ "$mask"];
		lpush( $vs, compact( ttl( 'mask,mip,range,min,max,ratio')));
	}}
	// chart
	list( $C, $CS, $CST) = chartlayout( new MyChartFactory(), 'P', '1x1', 30, '0.2:0.1:0.25:0.1'); $C2 = lshift( $CS);
	$C2->train( hltl( $vs, 'mask'), hltl( $vs, 'min'));
	$C2->train( hltl( $vs, 'mask'), hltl( $vs, 'max'));
	$C2->autoticks( null, null, 10, 10);
	$C2->frame( 'IP Address Mask (suffix bit length)', null);
	foreach ( $vs as $h) {
		extract( $h); // mask, mip, range, min, max, ratio
		chartshaperect( $C2, $mask, $mip, 5, 2, $S2);
		chartshaperect( $C2, "$mask:0.05", "$mip:-0.05", $ratio * 4.9, 1.9, $S3);
	}
	$C->dump( sprintf( 'tempdf.%03d.pdf', $pos)); $pos++; 
}
finclose( $in); echo " OK\n";
echo "pdftk..."; procpdftk( 'tempdf*', 'charts.graphs.pdf'); echo " OK\n";

?>