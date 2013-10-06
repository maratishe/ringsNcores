<?php
set_time_limit( 0);
ob_implicit_flush( 1);
//ini_set( 'memory_limit', '4000M');
for ( $prefix = is_dir( 'ajaxkit') ? 'ajaxkit/' : ''; ! is_dir( $prefix) && count( explode( '/', $prefix)) < 4; $prefix .= '../'); if ( ! is_file( $prefix . "env.php")) $prefix = '/web/ajaxkit/'; if ( ! is_file( $prefix . "env.php")) die( "\nERROR! Cannot find env.php in [$prefix], check your environment! (maybe you need to go to ajaxkit first?)\n\n");
if ( is_file( 'requireme.php')) require_once( 'requireme.php'); else foreach ( array( 'functions', 'env') as $k) require_once( $prefix . "$k.php"); clinit(); 
//clhelp( '');
//htg( clget( ''));

echo "\n\n"; $e = echoeinit();
foreach ( flget( '.', '', '', 'packets') as $file) {
	$L = ttl( $file, '.'); lpop( $L); lpush( $L, 'flows'); $file2 = ltt( $L, '.');
	$c = "php /code/makascripts/traffic/packets2flows.php 16 1 0.1 $file $file2 status";
	echo "$c\n"; echopipee( $c); echo "\n";
	$c = "php /code/makascripts/traffic/flows.ipstats.php $file2 auto";
	echo "$c\n"; echopipee( $c); echo "\n";
}
echo " ALL DONE\n";

?>