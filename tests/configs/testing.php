<?php
return [
	'db' => [
		'databases' => [
			'testdb' => [
				'dsn' => "mysql:host=127.0.0.1;dbname=testdb;charset=latin1",
				'username' => 'root',
				'password' => '',
				'options' => [
					\PDO::ATTR_TIMEOUT => 5,
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
				],
			],
		],
	],
];
