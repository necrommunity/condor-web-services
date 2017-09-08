<!DOCTYPE html>
<html lang = "en-US">
<head>
<link rel="stylesheet" type="text/css" href="/css/raceresults.css"/>
<meta charset = "UTF-8">
<title>Necrobot results</title>
<meta name="viewport" content="initial-scale=1.0; maximum-scale=1.0; width=device-width;">
</head>
<body>
<div class="table-title">
<h3>Necrobot race results</h3>
<?php
$characterArray = array("cadence","bard","monk","dove","eli","bolt","dorian","melody","aria","coda","nocturna","diamond","mary","tempo");

$username = NULL;

if (isset($_REQUEST['username'])) {
    $username = $_REQUEST['username'];
}

$character = NULL;

if (isset($_REQUEST['character'])) {
    $character = $_REQUEST['character'];
}

$limit = NULL;

if (isset($_REQUEST['limit'])) {
    $limit = intval($_REQUEST['limit']);
}

if ((empty($username) || empty($character) || empty($limit))) {
    if (!empty($_REQUEST['req'])) {
        $username_character_limit = $_REQUEST['req'];

        $username_name_character_limit_split = explode('/',$username_character_limit);

        if (count($username_name_character_limit_split) == 1) {
            $username   = $username_name_character_limit_split[0];
        } 

        elseif (count($username_name_character_limit_split) == 2) {
            $username   = $username_name_character_limit_split[0];
            $character  = $username_name_character_limit_split[1];
        }

        elseif (count($username_name_character_limit_split) == 3) {
            $username   = $username_name_character_limit_split[0];
            $character  = $username_name_character_limit_split[1];
            $limit      = intval($username_name_character_limit_split[2]);
        }

	if (empty($character) || !(in_array(mb_strtolower($character),$characterArray))) {
	    $character = "Cadence";
	}

	if (empty($limit) || ($limit<1))  {
	    $limit = 3;
	}
    }
    
    if ((empty($username) || empty($character))) {
    echo '<pre>'."Please enter your query in the format /results/USERNAME/CHARACTER/COUNT".'</pre>';
	//header("HTTP/1.1 404 Not Found");
    //exit;
    }
}

$username = html_entity_decode($username);
$character = html_entity_decode($character);
$printedLimit = $limit;
print "<h4>Showing the $printedLimit latest results for $username playing $character</h4>";
print "</div>";
try {
$con= new PDO('mysql:host=localhost;dbname=necrobot', "necrobot-read", "necrobot-read");
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
print "<table class='table-fill'>";
$query = $con->prepare("call necrobot.getresults(:username,:character,:limit)");
$query->bindParam(':username', $username);
$query->bindParam(':character', $character);
$query->bindParam(':limit', intval($limit), PDO::PARAM_INT);
$query->execute();
  //return only the first row (we only need field names)
$row = $query->fetch(PDO::FETCH_ASSOC);
//$query->closeCursor();
print "<tbody class='table-hover'>";
print "<thead>";
print "<tr>";
foreach ($row as $field => $value){
print "<th class='text-left'>$field</th>";
} // end foreach
print " </tr>";
print "</thead>";
print "<tr>";
foreach ($row as $field => $value){
 print " <td>$value</td>";
} // end foreach
print " </tr>";
//second query gets the data
$query->setFetchMode(PDO::FETCH_ASSOC);
foreach($query as $row){
 print " <tr>";
 foreach ($row as $name=>$value){
 print " <td>$value</td>";
 } // end field loop
 print " </tr>";
} // end record loop
print "</table>";
} catch(PDOException $e) {
 echo 'ERROR: ' . $e->getMessage();
} // end try
?>
</tbody>
</table>
</body>
</html>
