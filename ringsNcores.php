<?php
set_time_limit( 0);
ob_implicit_flush( 1);
//ini_set( 'memory_limit', '4000M');
for ( $prefix = is_dir( 'ajaxkit') ? 'ajaxkit/' : ''; ! is_dir( $prefix) && count( explode( '/', $prefix)) < 4; $prefix .= '../'); if ( ! is_file( $prefix . "env.php")) $prefix = '/web/ajaxkit/'; if ( ! is_file( $prefix . "env.php")) die( "\nERROR! Cannot find env.php in [$prefix], check your environment! (maybe you need to go to ajaxkit first?)\n\n");
if ( is_file( 'requireme.php')) require_once( 'requireme.php'); else foreach ( array( 'functions', 'env') as $k) require_once( $prefix . "$k.php"); clinit(); 
//clhelp( 'PURPOSE: to run simulation');
//clhelp( '[pdir] directory containing .packets files');
clhelp( '[model]  history | spread');
htg( clgetq( 'model'));

// setup
$CORES = 7; $MASKDEPTH = mt_rand( 5, 16); $TYPE = lr( ttl( 'homogeneous,heterogeneous'));
$GRAPH = null;	// graph from raw.graphs.bz64jsonl


echo "\n\n"; $e = echoeinit(); 
class MyGA extends GA {
	public function fitness( $g) { 
		$sums = array(); $vars = array(); foreach ( $g as $c) { lpush( $sums, msum( hv( $c))); lpush( $vars, mvar( hv( $c))); }
		$eval = 10000 * ( ( 100 / mavg( $sums)) - ( mavg( $vars) ? ( 1 / mavg( $vars)) : 0));
		//die( jsonraw( $sums) . "\n" . jsonraw( $vars) . "\n" . jsonraw( $eval));
		return $eval;
	}
	public function isvalid( $g) { return true; }	// all genes are valid by definition
	public function makechromosome( &$g, $pos, $new) { 
		global $CORES, $MASKDEPTH, $GRAPH;
		if ( ! $new) return; 	// no mutation
		if ( $pos != 0) return;	// makes all chromosome when the first one is called
		// this is the first chromosome, make all of them
		for ( $i = 0; $i < $CORES; $i++) $g[ $i] = array();
		$h = $GRAPH[ "$MASKDEPTH"];	shuffle( $h); // { mask: count}
		while ( count( $h)) { for ( $i = 0; $i < $CORES && count( $h); $i++) { 
			list( $mask, $count) = hshift( $h); $g[ $i][ "$mask"] = $count;
		}}
		
	}
	public function generationreport( $gen, $evals) { }
	// unusual overwrite -- need new crossover operator
	public function crossover( $p1, $p2) {
		$L1 = array(); for ( $cpos = 0; $cpos < count( $p1); $cpos++) foreach ( $p1[ $cpos] as $mask => $pps) lpush( $L1, compact( ttl( 'cpos,mask,pps')));
		$L2 = array(); for ( $cpos = 0; $cpos < count( $p2); $cpos++) foreach ( $p2[ $cpos] as $mask => $pps) lpush( $L2, compact( ttl( 'cpos,mask,pps')));
		shuffle( $L1); shuffle( $L2); $count = round( 0.5 * mmin( array( count( $L1), count( $L2))));
		$c1 = $p1; $c2 = $p2;
		for ( $i = 0; $i < $count; $i++) {	// position of element in the list
			extract( $L1[ $i]); $cpos1 = $cpos; $mask1 = $mask; $pps1 = $pps;
			extract( $L2[ $i]); $cpos2 = $cpos; $mask2 = $mask; $pps2 = $pps;
			unset( $c1[ $cpos1][ "$mask1"]); $c1[ $cpos1][ "$mask2"] = $pps2;
			unset( $c2[ $cpos2][ "$mask2"]); $c2[ $cpos2][ "$mask1"] = $pps1;
		}
		$h = array(); foreach ( ttl( 'p1,p2') as $k) { $v = $$k; $h[ "$k"] = $this->fitness( $v); }
		arsort( $h, SORT_NUMERIC); list( $k, $before) = hfirst( $h);
		foreach ( ttl( 'c1,c2') as $k) { $v = $$k; $h[ "$k"] = $this->fitness( $v); }
		arsort( $h, SORT_NUMERIC); 
		list( $k1, $after) = hshift( $h); $v1 = $$k1;
		list( $k2, $v) = hshift( $h); $v2 = $$k2;
		return array( $v1, $v2, $after - $before);
	}
	
}
class MyGAhistory extends MyGA { public function makechromosome( &$g, $pos, $new) { 
	global $CORES, $MASKDEPTH, $GRAPH;
	if ( ! $new) return; 	// no mutation
	if ( $pos != 0) return;	// makes all chromosome when the first one is called
	// this is the first chromosome, make all of them
	for ( $i = 0; $i < $CORES; $i++) $g[ $i] = array();
	$h = $GRAPH[ "$MASKDEPTH"];	shuffle( $h); // { mask: count}
	while ( count( $h)) {  
		$g2 = array(); foreach ( $g as $c => $h2) $g2[ $c] = msum( hv( $h2));
		asort( $g2, SORT_NUMERIC); list( $i, $count) = hfirst( $g2);
		list( $mask, $count) = hshift( $h); 
		$g[ $i][ "$mask"] = $count; 
	}
	
}}
class MyGAspread extends MyGA { public function fitness( $g) { 
	$diffs = array(); 
	foreach ( $g as $c => $h) {
		ksort( $h, SORT_NUMERIC);
		$L = mdistance( hk( $h));
		lpush( $diffs, count( $L) ? msum( $L) : 0);
	}
	return mavg( $diffs);
}}
while ( 1) { $in = finopen( 'raw.graphs.bz64jsonl'); while ( ! findone( $in)) {
	$MASKDEPTH = mt_rand( 4, 10);
	list( $graph, $p) = finread( $in); if ( ! $graph) continue;
	if ( ! $GRAPH) { $GRAPH = $graph; continue; }
	if ( $model == 'history') $ga = new MyGAhistory();
	else $ga = new MyGAspread();
	list( $gs, $evals) = $ga->optimize( 100, 1, 0.5, 0, 0.3, 5, 50, 6);
	list( $pos, $eval) = hfirst( $evals); $gene = $gs[ $pos];
	$out = foutopen( "raw.$model.bz64jsonl", 'a'); foutwrite( $out, compact( ttl( 'MASKDEPTH,eval,gene,GRAPH'))); foutclose( $out);
	$GRAPH = $graph;
};  finclose( $in);  }
echo " ALL DONE\n";

?>