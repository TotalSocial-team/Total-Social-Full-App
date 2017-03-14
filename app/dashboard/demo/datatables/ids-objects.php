<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'users';

// Table's primary key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier - in this case object
// parameter names
$columns = array(
	array( 'db' => 'username', 'dt' => 0 ),
	array( 'db' => 'email',  'dt' => 1 ),
	array( 
		'db'        => 'gender',
		'dt'        => 2,
		'formatter' => function( $d, $row ) {
			if ($d = 'm'){
				return("Male");
			}else{
				return("Female");
			}
		}
	),
	array( 
		'db' 		=> 'verified', 
		'dt' 		=> 3,
		'formatter' => function($d, $row){
			if ($d == '0'){
				return 'No';
			}else {
				return 'Yes';
			}
		}
	),
	array( 
		'db' 		=> 'approved',     
		'dt'		=> 4,
		'formatter' => function($d, $row){
			if ($d == '0'){
				return 'No';
			}else {
				return 'Yes';
			}
		}
	),
	array(
		'db' 		=> 'userlevel',     
		'dt'		=> 5, 
		'formatter' => function($d, $row){
			switch($d)
			{
				case 'a':
					return 'Basic User';
				
				case 'b':
					return 'VIP';
				
				case 'c':
					return 'Admin/Compliance';
					
				case 'd':
					return 'Admin with Full Access';
			}
		}
	),
	
	
	array( 
		'db' 	=> 'activated',     
		'dt' 	=> 6,
		'formatter' => function($d, $row){
			if ($d == '0')
			{
				return 'Not activated';
			}else {
				return 'Fully Activated';
			}
	
		}
	),


);

include('conn.php');


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);

