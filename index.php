<?php 

///set defaults
$request = "person";
$offset = 0;
$limit = 1;

if (array_key_exists("request", $_GET)) {
	$request = $_GET["request"];
}
if (array_key_exists("offset", $_GET)) {
	$offset = $_GET["offset"];
}
if (array_key_exists("limit", $_GET)) {
	$limit = $_GET["limit"];
}

echo read_csv($request, $offset, $limit);

function read_csv($request, $offset, $limit) {
	//initiate array for json encoding
	$trimmed_array = array();

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
		$trimmed_array[$row_array[0]] = array();
		/*
		for each row, we want to create a key-value pair,
		with the idx field as the key and an array as the value.
		Said array will use the field names as keys and the values read from the csv row
		as values.
		*/
		for ($i = 1; $i < count($fields); $i++) {
			$trimmed_array[$row_array[0]][$fields[$i]] = $row_array[$i];

		}
		$row++;

	}

	return json_encode(array($request => $trimmed_array));
}



 ?>