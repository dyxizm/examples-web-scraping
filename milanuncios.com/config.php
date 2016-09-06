<?php

	$config = array();
	/* Mysql */
	$config['db']['host'] = 'localhost';
	$config['db']['user'] = '_USER_';
	$config['db']['password'] = '_PASS_';
	$config['db']['database'] = '_BD_';

	$config['imagesPath'] = '/home/dyx/workSpace/todo.es/images';

	// сколько обработать страниц за запуск, 0 - без ограничения
	$config['limit'] = 1;
	// количество потоков
	$config['threads'] = 1;

	/* milanuncios */
	$config['module']['milanuncios']['on'] = true;
	$config['module']['milanuncios']['threads'] = $config['threads'];
	$config['module']['milanuncios']['limit'] = $config['limit'];

	$config['module']['milanuncios']['url'] = 'http://www.milanuncios.com';
	$config['module']['milanuncios']['categories'] = array(
		'anuncios' => '/motor/?demanda=n'
	);


	return $config;

?>
