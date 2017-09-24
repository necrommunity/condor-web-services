<?php
  session_start();
  $offset = $_SESSION['time'];
?>
<!DOCTYPE html>
<html lang = "en-US">
<head>
<link rel="stylesheet" type="text/css" href="/css/schedule.css">
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        if("<?php echo $offset; ?>".length==0){
            var visitortime = new Date().getTimezoneOffset();
            $.ajax({
                type: "GET",
                url: "//condor.host/timezone.php",
                data:
                  'time=' + visitortime,
                success: function(){
                    location.reload();
                }
            });
        }
    });
</script>
<meta charset = "UTF-8">
<meta http-equiv="refresh" content="300">
<title>CoNDOR Schedule</title>
<meta name="viewport" content="initial-scale=1.0; maximum-scale=1.0; width=device-width;">
</style>
</head>
<body>
<div class="table-title">
<h3>CoNDUIT 22</h3>
<h4><a href='http://stats.condorleague.tv' target='_blank'>Stats sheet</a> | <a href='https://condor.host/stat' target='_blank'>Live RTMP racers</a></h4>

<?php

  $offset = $offset/60;
  $displayOffset = $offset * -1;

  if ($offset >= 0) {
    $offset = 'GMT+'.$offset;
    $displayOffset = 'GMT'.$displayOffset;
  } else {
    $offset = 'GMT'.$offset;
    $displayOffset = 'GMT+'.$displayOffset;
  }

  echo "<h5>All times are in your local timezone - $displayOffset</h5>";
?>
<input type="text" id="searchInput" value="Type to filter">

<script>
$("#searchInput").keyup(function () {
    //split the current value of searchInput
    var data = this.value.toUpperCase().split(" ");
    //create a jquery object of the rows
    var jo = $("#scheduleTable tbody tr");
    if (this.value == "") {
        jo.show();
        return;
    }
    //hide all the rows
    jo.hide();

    //Recusively filter the jquery object to get results.
    $("#scheduleTable td:not(:nth-child(1),:nth-child(3),:nth-child(6))").filter(function (i, v) {
        var $t = $(this);
        for (var d = 0; d < data.length; ++d) {
            if ($t.text().toUpperCase().indexOf(data[d]) > -1) {
                return true;
            }
        }
        return false;
    })
    //show the rows that match.
    .parents("tr").show();
}).focus(function () {
    this.value = "";
    $(this).css({
        "color": "black"
    });
    $(this).unbind('focus');
}).css({
    "color": "#C0C0C0"
});
</script>
</div>
<?php

  date_default_timezone_set("Etc/UTC");

  $now = new DateTime("now");
  $append = "";
  try {
  $con= new PDO('mysql:host=localhost;dbname=necrobot', "necrobot-read", "necrobot-read");
  $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $query = "call necrobot.current_event_schedule('')";
  print "<table class='table-fill' id='scheduleTable'>";
  $result = $con->query($query);
  //return only the first row (we only need field names)
  $row = $result->fetch(PDO::FETCH_ASSOC);
  $result->closeCursor();
  print "<tbody class='table-hover'>";
  print "<thead>";
  print "<tr>";
  foreach ($row as $field => $value){
  print "<th>$field</th>";
  } // end foreach
  print " </tr>";
  print "</thead>";
  //second query gets the data
  $data = $con->query($query);
  $data->setFetchMode(PDO::FETCH_ASSOC);
  foreach($data as $row){
   print " <tr>";
   foreach ($row as $name=>$value){
    $colspan = "";
    if ($name === 'Start time'){
      if (strpos($value, "Completed") !== 0) {
    $value = new DateTime($value);
    $value->setTimezone(new DateTimeZone("Etc/$offset"));
    $timestamp = $value;
    if ($value < $now) {
      $append = " ago 🏃🏁";
    } else {
      $append = "";
    }
    $value = $value->diff($now)->format("%d days, %h hours, %i minutes");
    if (substr($value, 0, 8) === "0 days, "){
      $value = substr($value, 8); 
    } 
    if (substr($value, 0, 8) === "1 days, "){
      $value = substr_replace($value, "1 day, ", 0, 8);
    }
   if ((strpos($value, "10 hours, ") === false) AND (strpos($value, "20 hours, ") === false)){
    if (strpos($value, "0 hours, ") !== false) {
      $value = str_replace("0 hours,", "", $value);
    } 
  }
   if ((strpos($value, "11 hours, ") === false) AND (strpos($value, "21 hours, ") === false)) {
    if (strpos($value, "1 hours, ") !== false)  {
      $value = str_replace("1 hours, ", "1 hour, ", $value);
      }
    } 
    $diff = $timestamp->diff($now);
    $hours = $diff->h;
    $hours = $hours + ($diff->days*24);

    if (($hours > 11) AND ($now < $timestamp)) {
      $value = date_format($timestamp, 'F jS, H:i');
    } 

    $value = $value.$append;
  } elseif (substr($value, 0, 1) === '0') {
    str_replace('0','',$value);
  }
  }
   if ($name == 'Racer 1'){
    $valueR1 = strstr($value,' ',true);

    if (substr($valueR1,-1) == ")") {
    $valueR1Trim = substr($valueR1,0,-4);
    } else {
      $valueR1Trim = $valueR1;
    }
    }

   if ($name == 'Racer 2'){
    $valueR2 = strstr($value,' ',true);

    if (substr($valueR2,-1) == ")") {
    $valueR2Trim = substr($valueR2,0,-3); 
    } else {
      $valueR2Trim = $valueR2;
    }
   }

   // if ($name == 'Type'){
   //  if ($value == 'Wonder'){
   //    $value = '<img title="Wonder" src="/img/images/wonder.png"/> Cup';
   //  } elseif ($value == 'Becoming') {
   //    $value = '<img title="Becoming" src="/img/images/becoming.png"/> Cup';
   //  } 
   //  elseif ($value == 'Season 6') {
   //    $value = '<img title="Season 6" src="/img/images/pixelheart.png"/>';
   //  }
   // }

   if ($name == 'Cawmentary' && $value != 'Unclaimed'){
    $valueCaw = $value;
    $value = "<a href='https://condor.host/macsg/rtmp,$valueR1Trim,$valueR2Trim' target='_blank'>MacSG</a> | 📺 <a href='https://twitch.tv/$valueCaw' target='_blank'>$valueCaw</a>";
   }elseif ($name == 'Cawmentary' && $value == 'Unclaimed'){
    $value = "<a href='https://condor.host/macsg/rtmp,$valueR1Trim,$valueR2Trim' target='_blank'>MacSG</a> | <a href='https://rtmp.condorleague.tv/#$valueR1/$valueR2,,,,cawmunity' target='_blank'>Unclaimed</a>";
   }
   print " <td $colspan>$value</td>";
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
