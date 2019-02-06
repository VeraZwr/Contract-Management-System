<?php

//First Time Connection
if (!isset($_POST["id"]) and !isset($_POST["password"])){
	session_start();
	session_destroy(); //Session should be resetted whenver user reached login page
	echo('
		<title>CMS Log-In</title>
		<h1>Contract Management System (CMS)</h1>
		<br>
		<form method="post" action="/login.php?login=1">  
		  Employee or Client ID:  <input type="text" placeholder="Required" name="id" required>
		  <br><br>
		  Password:  <input type="password" placeholder="Required" name="password" required>
		  <br><br>
		  
		  <input type="submit" name="submit" value="Log In">
		  <button type="reset" value="Clear">Reset</button>
		  <br>
		</form>'
	);
	if ($_GET["invalid"] == 1){
			  echo "<br>* Invalid ID or Password, Please Try Again";
		  }
}
else{
	session_start();

	//Connect to DB

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

	$id_input = $_POST["id"];
	$password_input = $_POST["password"];

	$sql = "SELECT EmployeeID, PWD, Role FROM scc353_1.Employee";
	$result = mysqli_query($conn, $sql);

	if (mysqli_num_rows($result) > 0) {
	// output data of each row
		while($row = mysqli_fetch_assoc($result)) {
			if (($row["EmployeeID"] == $id_input) and ($row["PWD"] == $password_input)){
				$_SESSION["EmployeeID"] = $row["EmployeeID"];
				$_SESSION["PWD"] = $row["PWD"];
				$_SESSION["Role"] = $row["Role"];
				if ($_SESSION["Role"] == "SalesAssociate"){
					header('Location: sa.php'); 
					die();
				}
				else if ($_SESSION["Role"] == "Manager"){
					header('Location: manager.php'); 
					die();					
				}
				else if ($_SESSION["Role"] == "Employee"){
					header('Location: employee.php'); 
					die();					
				}
				else if ($_SESSION["Role"] == "Admin"){
					header('Location: admin.php'); 
					die();					
				}
				else {
					header('Location: login.php'); 
					die();						
				}

			}
		}
	}

	$sql = "SELECT CompanyID, PWD FROM scc353_1.Company";
	$result = mysqli_query($conn, $sql);

	if (mysqli_num_rows($result) > 0) {
	// output data of each row
		while($row = mysqli_fetch_assoc($result)) {
			if (($row["CompanyID"] == $id_input) and ($row["PWD"] == $password_input)){
				$_SESSION["CompanyID"] = $row["CompanyID"];
				$_SESSION["PWD"] = $row["PWD"];
				header('Location: client.php'); 
				die();
			}
		}
		
		//If no account has been found
		header('Location: login.php?invalid=1'); 
		die();
	}
	mysqli_close($conn);	
}

?>
<!DOCTYPE HTML>  
<html>
<head>
</head>
<body>  



</body>
</html>