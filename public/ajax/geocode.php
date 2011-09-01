<?php

require( '../../system/config/config.php' );

// This turns {address}, {city}, {state} from the config
// into 123 main st, san diego, ca for geocoding
$address = preg_replace_callback( '~\{(.*?)\}~', function( $m ) use( $_GET ){ return $_GET[$m[1]]; }, $config['geocode_string'] );

require( DIR_LIB . '/Geocoder/Geocoder.php' );
$geocode = Geocoder::geocode( $address );

if ( $geocode instanceof StdClass ) {
	if ( isset( $_GET['save_store'] ) ) {
		require( DIR_SYSTEM . '/models/StoreTableGateway.php' );
		require( DIR_SYSTEM . '/models/Store.php' );
		require( DIR_CORE . '/Db.php' );
		$db = $db = Db::connect( $config['db_user'], $config['db_password'], $config['db_name'], $config['db_host'], $config['db_type'] ) or die( json_encode( array( 'status' => 0, 'message' => 'Unable to connect to database' ) ) );
		$stg = new StoreTableGateway( $db, $config['db_table'], $config['column_map'] );
		$store = new Store( $config['column_map'], array( $config['column_map']['id'] => $_GET[$config['column_map']['id']], $config['column_map']['lat'] => $geocode->lat, $config['column_map']['lng'] => $geocode->lng ) );
		if ( $stg->saveStore( $store ) ) {
			$json = array( 'status' => 1, 'message' => 'Geocoding successful' );
		}
		else {
			$json = array( 'status' => 0, 'message' => 'Error saving the store' );
		}
	}
	else {
		$json = array( 'status' => 1, 'lat' => $geocode->lat, 'lng' => $geocode->lng, 'message' => 'Store geocoded successfully' );
	}
}
else {
	$json = array( 'status' => 0, 'message' => 'Unable to geocode address' );
}
die( json_encode( $json ) );