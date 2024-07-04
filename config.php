<?php

// ================ Configuration ================ \\
// MySQL database: ( Host / Username / Password / Database Name )
	@$sql_connection = mysqli_connect('localhost', 'username', 'password', 'db_name');
//
// MySQL table name: { CREATE TABLE nextbucket (id VARCHAR(64) NOT NULL, object VARCHAR(512) NOT NULL, bucket TEXT NOT NULL, cell TEXT NOT NULL, length BIGINT NOT NULL, tag TEXT NOT NULL, PRIMARY KEY(id), INDEX (object)); }
	$table = 'nextbucket';
//
// Time zone:
	date_default_timezone_set('Asia/Shanghai');
//
// =============================================== \\

?>