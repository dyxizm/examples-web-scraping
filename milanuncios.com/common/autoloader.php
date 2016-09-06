<?php

function __dm_autoload_geo( $name )
{
	$map = array (
		'RollingCurl\\RollingCurl' => 'RollingCurl/RollingCurl.php',
		'RollingCurl\\Request' => 'RollingCurl/Request.php',
);
	if ( isset( $map[ $name ] ) )
	{
		require __DIR__ .'/'.$map[ $name ];
	}else{
		require __DIR__ .'/'.$name.'.class.php';
	}
}
spl_autoload_register( '__dm_autoload_geo' );
