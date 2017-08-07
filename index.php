<?php 
/*
Reads rows from either person.csv or items.csv(which is determined by URL parameter request), located in same directory as this file.

URL parameters offset and limit determine how many rows to skip and how many rows to read, respectively. Defaults are request=person&offset=0&limit=1.

Reads only to the end of the csv file if limit parameter is higher than remaining number of rows.

Returns empty object ("{}") if offset parameter is higher than the number of rows.

Returns to the browser a JSON-encoded string. Top-level key is "person" or "items".

Each element below that is a key-value pair based on an individual row in the requested csv. The key is the idx number given as the first field in the csv row. The value is an array of key-value pairs where a given key is the name of a csv field, e.g. "motionOnly" and the value is the value for that field in the csv row corresponding to the idx number. 

Example: request=person&offset=0&limit=2 would read the first two rows of person.csv and give the following JSON-encoded string(reformatted for better readability here):

{"person":{
	"256":{"gender":"0","motionOnly":"0","status":"1"},
	"257":{"gender":"0","motionOnly":"1","status":"1"}
	}
}
*/

///set parameter defaults
$request = "person";
$offset = 0;
$limit = 1;

/*
Check for user-supplied parameters,
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
	//Initiate array for json encoding
	$json_array = array();

	//Open csv file, return error message if not found
	if (($handle = fopen($request . ".csv", "r")) == FALSE) {
		return "Requested file not found";
	}
	//First row gives us array of field names
	$fields = fgetcsv($handle);

	/*Iterate through the rows we want to skip.
	We could simply load the whole csv and slice it but this way is
	probably easier on memory. */
	$row = 1;
	while ($row <= $offset) {
		$row++;
		fgetcsv($handle);
	}

	/*
	Iterate through and process the remaining rows until we've read a number of them 
	given by $limit
	*/
	$row = 1;
	while ($row <= $limit) {
		$row_array = fgetcsv($handle);

		//Stop process if we reach the end of the csv file
		if ($row_array == NULL) {
			break;
		}

		/*
		For each row, we want to create a key-value pair,
		with the idx field($row_array[0]) as the key and an array as the value.
		Said array will use the field names as keys and the csv values read from $row_array
		as values.
		*/
		$json_array[$row_array[0]] = array();

		//fixed bug: '<=' changed to '<'
		for ($i = 1; $i < count($fields); $i++) {
			$json_array[$row_array[0]][$fields[$i]] = $row_array[$i];

		}
		$row++;
	}

	/*
	JSON encode and return the array we've built, adding a top level element named after the request type. return "{}" if the offset parameter put us past all the rows.
	*/
	if ($json_array == NULL) {
		return "{}";
	}
	//added bug: '$json_array' changed to '$jason_array'
	return json_encode(array($request => $jason_array));
}
 ?>