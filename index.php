<?php 

///set defaults
$request = "person";
$offset = 0;
$limit = 1;

/*
check for user-supplied parameters,
and replace defaults if they are given and valid
*/
if (array_key_exists("request", $_GET) && 
	($_GET["request"] == "person" or $_GET["request"] == "items")) {
	$request = $_GET["request"];
}
if (array_key_exists("offset", $_GET) && is_numeric($_GET["offset"])) {
	$offset = $_GET["offset"];
}
if (array_key_exists("limit", $_GET) && is_numeric($_GET["limit"])) {
	$limit = $_GET["limit"];
}

echo csv_to_json($request, $offset, $limit);

function csv_to_json($request, $offset, $limit) {
	//initiate array for json encoding
	$json_array = array();

	$handle = fopen($request . ".csv", "r");
	//first row gives us array of field names
	$fields = fgetcsv($handle);

	/*iterate through the rows we want to skip.
	We could simply load the whole csv and slice it but this way is
	probably easier on memory. */
	$row = 1;
	while ($row <= $offset) {
		$row++;
		fgetcsv($handle);
	}

	/*
	go through the remaining rows until we've read them in the number given by $limit
	*/
	$row = 1;
	while ($row <= $limit) {
		$row_array = fgetcsv($handle);
		/*
		for each row, we want to create a key-value pair,
		with the idx field($row_array[0]) as the key and an array as the value.
		Said array will use the field names as keys and the csv values read from $row_array
		as values.
		*/
		$json_array[$row_array[0]] = array();
		
		for ($i = 1; $i < count($fields); $i++) {
			$json_array[$row_array[0]][$fields[$i]] = $row_array[$i];

		}
		$row++;

	}

	return json_encode(array($request => $json_array));
}



 ?>