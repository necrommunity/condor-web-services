<?php

$macsg = null;

if (isset($_REQUEST['macsg'])) {
	$macsg = $_REQUEST['macsg'];
}

if(empty($macsg)) {
	if (!empty($_REQUEST['req'])) {
       	$macsg_req = $_REQUEST['req'];
       
       	$macsg_split = explode('/', $macsg_req);
        
       	if (count($macsg_split) == 1) {
       	    $macsg = $macsg_split[0];
       	}
   	}


	echo '<!DOCTYPE html>
		<html>
		<head>
			<title>MacSG redirect</title>
		</head>
		<body>
			<a href="macsg:'.$macsg.'">macsg:'.$macsg.'</a>

			<script type="text/javascript">
				location.href = "macsg:'.$macsg.'";
			</script>
		</body>
	</html>';
}