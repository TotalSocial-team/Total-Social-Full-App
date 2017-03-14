<?php
// DB table to use
$table = 'status';

sleep(1); // to simulate

//Table's primay key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => 'account_name', 'dt' => 0 ),
	array( 'db' => 'author',  'dt' => 1 ),
	array( 
		'db'        => 'data',
		'dt'        => 2
	),
	array( 
		'db' 		=> 'type', 
		'dt' 		=> 3,
		'formatter' => function($d, $row){
			switch($d)
			{
				case 'a':
					return 'Written to yourself';
					
				case 'c':
					return 'Written to a friend';
			}
		}
	),
	array(
		'db' 		=> 'postdate',     
		'dt'		=> 4,
		'formatter' => function($d, $row){
			return date( 'jS M y', strtotime($d));
		}
	),
);

include 'conn.php';


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */


require( 'ssp.class.php' );

echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);

?>