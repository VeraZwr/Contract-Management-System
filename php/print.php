<?php

echo '<meta charset="ISO-8859-1">';

function display_data($data) {
    $output = "<table>";
    foreach($data as $key => $var) {
        //$output .= '<tr>';
        if($key===0) {
            $output .= '<tr>';
            foreach($var as $col => $val) {
                $output .= "<td>" . $col . '</td>';
            }
            $output .= '</tr>';
            foreach($var as $col => $val) {
                $output .= '<td>' . $val . '</td>';
            }
            $output .= '</tr>';
        }
        else {
            $output .= '<tr>';
            foreach($var as $col => $val) {
                $output .= '<td>' . $val . '</td>';
            }
            $output .= '</tr>';
        }
    }
    $output .= '</table>';
    echo $output;
}

$servername = "scc353.encs.concordia.ca";
$username = "scc353_1";
$password = "353gogo";
$dbname = "scc353_1";


// Create connection
$conn = mysqli_connect($servername, $username, $password,$dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}	

/*
$sql = "SELECT * FROM scc353_1.Manager";
$result = mysqli_query($conn, $sql);
echo '<pre>';
print_r ($result);
echo '</pre>';

display_data($result);

echo "<br><br>";


$sql = "show tables";
$result = mysqli_query($conn, $sql);

display_data($result);*/


$sql = "show tables";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
	
    // output data of each row
    while($row = mysqli_fetch_array($result)) {

		//print_r ($row);
		echo "Table   <b>$row[0]</b> <br>";
        $sql = "SELECT * FROM scc353_1.$row[0]";
		$row_array = mysqli_query($conn, $sql);
		display_data($row_array);
		echo "End of Table <b>$row[0]</b> <br><br><br>";

    }
} else {
    echo "0 results";
}

mysqli_close($conn);
?>