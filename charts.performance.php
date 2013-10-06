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
$R = clone $S; $SGD = $R; $R->lw = 1.0; $R->style = 'D'; $R->fill = null; $R->draw = '#093'; 
$R = clone $SGD; $SGF = $R; $R->lw = 0; $R->style = 'F'; $R->fill = $R->draw; $R->draw = null; $R->alpha = 0.4;
$R = clone $SGF; $SGB = $R; $R->alpha = 1.0;
$R = clone $SGD; $SBD = $R; $R->draw = '#06f';
$R = clone $SGF; $SBF = $R; $R->fill = '#06f';
$R = clone $SGB; $SBB = $R; $R->fill = '#06f';
list( $C, $CS, $CST) = chartlayout( new MyChartFactory(), 'L', '2x1', 30, '0.2:0.05:0.25:0.15'); 

// read data
$history = array(); $spread = array(); 
foreach ( ttl( 'history,spread') as $m) { $in = finopen( "data.$m.bz64jsonl"); while ( ! findone( $in)) { 
	list( $h, $p) = finread( $in); if ( ! $h) continue;
	$R =& $$m; lpush( $R, $h); unset( $R);
}; finclose( $in); }


$C2 = lshift( $CS); // compare diff in load per core
foreach ( ttl( 'spread,history') as $m) { $v = $$m; $C2->train( hltl( $v, 'mask'), hltl( $v, 'diffmin')); $C2->train( hltl( $v, 'mask'), hltl( $v, 'diffmax')); }
$C2->autoticks( null, null, 8, 8);
$C2->frame( 'IP Address Mask (suffix)', 'Core diff (kpps)');
chartarea( $C2, hltl( $history, 'mask'), hltl( $history, 'diffmax'), hltl( $history, 'diffmin'), $SGF);
chartline( $C2, hltl( $history, 'mask'), hltl( $history, 'diffavg'), $SGD);
chartscatter( $C2, hltl( $history, 'mask'), hltl( $history, 'diffavg'), 'circle', $BS, $SGB);
chartarea( $C2, hltl( $spread, 'mask'), hltl( $spread, 'diffmax'), hltl( $spread, 'diffmin'), $SBF);
chartline( $C2, hltl( $spread, 'mask'), hltl( $spread, 'diffavg'), $SBD);
chartscatter( $C2, hltl( $spread, 'mask'), hltl( $spread, 'diffavg'), 'rect', $BS, $SBB);

$C2 = lshift( $CS); // distributions of counts
foreach ( ttl( 'spread,history') as $m) { $v = $$m; $C2->train( hltl( $v, 'mask'), hltl( $v, 'countmin')); $C2->train( hltl( $v, 'mask'), hltl( $v, 'countmax')); }
$C2->autoticks( null, null, 8, 8);
$C2->frame( 'IP Address Mask (suffix)', 'Masks per core');
chartarea( $C2, hltl( $history, 'mask'), hltl( $history, 'countmax'), hltl( $history, 'countmin'), $SGF);
chartline( $C2, hltl( $history, 'mask'), hltl( $history, 'countavg'), $SGD);
chartscatter( $C2, hltl( $history, 'mask'), hltl( $history, 'countavg'), 'circle', $BS, $SGB);
chartarea( $C2, hltl( $spread, 'mask'), hltl( $spread, 'countmax'), hltl( $spread, 'countmin'), $SBF);
chartline( $C2, hltl( $spread, 'mask'), hltl( $spread, 'countavg'), $SBD);
chartscatter( $C2, hltl( $spread, 'mask'), hltl( $spread, 'countavg'), 'rect', $BS, $SBB);
$CL = new ChartLegendOR( $C2);
$CL->add( 'circle', $BS, 0.5, "History model", $SGB);
$CL->add( 'rect', $BS, 0.5, "Spread model", $SBB);
$CL->draw();


$C->dump( 'chart.performance.pdf');


?>