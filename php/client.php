<?php

function sql_single ($conn, $sql){
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($result);
	return $row;
}

session_start();
echo '<title>Client Portal</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
	table {
	border-collapse: collapse;
	border-spacing: 0;
	width: 100%;
	border: 1px solid #ddd;
	}

	th, td {
	text-align: left;
	padding: 8px;
	}

	tr:nth-child(even){background-color: #f2f2f2}
	#rate {
	border-style: dotted;
	padding: 30px;
	}
	</style>		
';

//Illegal access with only URL
if (!isset($_SESSION["CompanyID"])){
	echo '<h1>You are accessing this page illegally. </h1>';
	echo '
		<form action="/login.php">
		  <input type="submit" value="Redirect to Log In">
		</form> 
	';
}

else{
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
	
	//Rating Submission
	if (isset($_POST["ContractID"]) and isset($_POST["Score"])){
		$sql = 'update Contract set SatisfactionScore = '.$_POST["Score"].' where ContractID = '.$_POST["ContractID"].';';
		
		if (mysqli_query($conn, $sql)) {
			$update = "Your rating has been recorded successfully.";
		} else {
			$update = "Error updating record: " . mysqli_error($conn);
		}
		
	}
	
	//Greeting Information
	$id = $_SESSION["CompanyID"];
	
	$sql = "SELECT CompanyName, ContactFirstName, ContactMiddleInitial, ContactLastName 
	FROM scc353_1.Company
	WHERE CompanyID = ".$id.";";
	$result = mysqli_query($conn, $sql);
	
    echo '<h1>Welcome to the Client Portal</h1>';
	
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo "<h3>".$row["ContactFirstName"]." ".$row["ContactMiddleInitial"]." ".$row["ContactLastName"] ." from ". $row["CompanyName"]."</h3>";
			echo '<h3>Client ID: '.$id.'</h3><br>';
		}
	} else {
		echo "Sorry, we are unable to retreive your client information at the moment";
	}
	
	//List active contract with this client
	$sql = '
	select Contract.ContractID, LOB, Category, Service, ACV, InitialAmount, DATE_FORMAT(StartDate,"%b-%d-%Y") as StartDate, DATE_FORMAT(FirstDelivered,"%b-%d-%Y") as FirstDelivered, DATE_FORMAT(SecondDelivered,"%b-%d-%Y") as SecondDelivered, DATE_FORMAT(ThirdDelivered,"%b-%d-%Y") as ThirdDelivered, DATE_FORMAT(FourthDelivered,"%b-%d-%Y") as FourthDelivered, SatisfactionScore
	from Contract
	where ContractID in

	(select ContractID
	from SignedBy
	where CompanyID = '.$id.')

	and
	(((Category = "Silver") and (FourthDelivered is null))
	or
	((Category != "Silver") and (ThirdDelivered is null)))

	order by ContractID
	;
	';
	
	$result = mysqli_query($conn, $sql);
	
	echo '<h3>Active Contracts</h3>';
	
	echo (mysqli_error($conn));
	
		if (mysqli_num_rows($result) > 0) {
		
		//Table Header
		echo '		
		<div style="overflow-x:auto;">
		  <table>
			<tr>
			  <th>Contract ID</th>
			  <th>Manager</th>
			  <th>Category</th>
			  <th>Line of Business</th>
			  <th>Service</th>
			  <th>Annual Contract Value</th>
			  <th>Initial Amount</th>
			  <th>Start Date</th>
			  <th>First Delivered</th>
			  <th>Second Delivered</th>
			  <th>Third Delivered</th>
			  <th>Fourth Delivered</th>
			  <th>Satisfaction Score</th>
			</tr>
		';
		
		while($row = mysqli_fetch_assoc($result)) {
			
			$sql = '
			select FirstName, LastName
			from Employee, Contract, ResponsibleFor
			where Contract.ContractID = ResponsibleFor.ContractID
			and EmployeeID = ResponsibleFor.ManagerID
			and Contract.ContractID = '.$row["ContractID"].';
			';
			
			$name = sql_single ($conn, $sql);
			
			$sql = '
			select LOB
			from AssociatedWith
			where ContractID = '.$row["ContractID"].';
			';
			
			$LOB = sql_single ($conn, $sql);
			
			echo "<tr>";
			
			echo '<td>' .$row["ContractID"]. '</td>';
			echo '<td>' .$name["FirstName"].' '.$name["LastName"]. '</td>';
			echo '<td>' .$row["Category"]. '</td>';
			echo '<td>' .$LOB["LOB"]. '</td>';
			echo '<td>' .$row["Service"]. '</td>';
			echo '<td>$' .$row["ACV"]. '</td>';
			echo '<td>$' .$row["InitialAmount"]. '</td>';
			echo '<td>' .$row["StartDate"]. '</td>';
			echo '<td>' .$row["FirstDelivered"]. '</td>';
			echo '<td>' .$row["SecondDelivered"]. '</td>';
			echo '<td>' .$row["ThirdDelivered"]. '</td>';
			echo '<td>' .$row["FourthDelivered"]. '</td>';
			echo '<td>' .$row["SatisfactionScore"]. '</td>';
			
			
			echo "</tr>";
			
		}
		echo "</table></div>";
	} else {
		echo "No active contract in the system.";
	}	
	
	//List expired contract with this client
	$sql = '
	select Contract.ContractID, LOB, Category, Service, ACV, InitialAmount, DATE_FORMAT(StartDate,"%b-%d-%Y") as StartDate, DATE_FORMAT(FirstDelivered,"%b-%d-%Y") as FirstDelivered, DATE_FORMAT(SecondDelivered,"%b-%d-%Y") as SecondDelivered, DATE_FORMAT(ThirdDelivered,"%b-%d-%Y") as ThirdDelivered, DATE_FORMAT(FourthDelivered,"%b-%d-%Y") as FourthDelivered, SatisfactionScore
	from Contract
	where ContractID in

	(select ContractID
	from SignedBy
	where CompanyID = '.$id.')

	and
	(((Category = "Silver") and (FourthDelivered is not null))
	or
	((Category != "Silver") and (ThirdDelivered is not null)))

	order by ContractID
	;
	';
	
	$result = mysqli_query($conn, $sql);
	
	echo '<h3>Expired Contracts</h3>';
	
	echo (mysqli_error($conn));
	
		if (mysqli_num_rows($result) > 0) {
		
		//Table Header
		echo '		
		<div style="overflow-x:auto;">
		  <table>
			<tr>
			  <th>Contract ID</th>
			  <th>Manager</th>
			  <th>Category</th>
			  <th>Line of Business</th>
			  <th>Service</th>
			  <th>Annual Contract Value</th>
			  <th>Initial Amount</th>
			  <th>Start Date</th>
			  <th>First Delivered</th>
			  <th>Second Delivered</th>
			  <th>Third Delivered</th>
			  <th>Fourth Delivered</th>
			  <th>Satisfaction Score</th>
			</tr>
		';
		
		while($row = mysqli_fetch_assoc($result)) {
			
			$sql = '
			select FirstName, LastName
			from Employee, Contract, ResponsibleFor
			where Contract.ContractID = ResponsibleFor.ContractID
			and EmployeeID = ResponsibleFor.ManagerID
			and Contract.ContractID = '.$row["ContractID"].';
			';
			
			$name = sql_single ($conn, $sql);
			
			$sql = '
			select LOB
			from AssociatedWith
			where ContractID = '.$row["ContractID"].';
			';
			
			$LOB = sql_single ($conn, $sql);
			
			echo "<tr>";
			
			echo '<td>' .$row["ContractID"]. '</td>';
			echo '<td>' .$name["FirstName"].' '.$name["LastName"]. '</td>';
			echo '<td>' .$row["Category"]. '</td>';
			echo '<td>' .$LOB["LOB"]. '</td>';
			echo '<td>' .$row["Service"]. '</td>';
			echo '<td>$' .$row["ACV"]. '</td>';
			echo '<td>$' .$row["InitialAmount"]. '</td>';
			echo '<td>' .$row["StartDate"]. '</td>';
			echo '<td>' .$row["FirstDelivered"]. '</td>';
			echo '<td>' .$row["SecondDelivered"]. '</td>';
			echo '<td>' .$row["ThirdDelivered"]. '</td>';
			echo '<td>' .$row["FourthDelivered"]. '</td>';
			echo '<td>' .$row["SatisfactionScore"]. '</td>';
			
			
			echo "</tr>";
			
			
		}
		echo "</table></div>";
	} else {
		echo "No expired contract in the system.";
	}	
	
	
	//Satisfaction Rating
	
	$sql = 'SELECT Contract.ContractID
	FROM Company, Contract, SignedBy
	WHERE Company.CompanyID = SignedBy.CompanyID
	AND Contract.ContractID = SignedBy.ContractID
	AND Company.CompanyID = '.$id.';';
	
	$result = mysqli_query($conn, $sql);

	
	if (mysqli_num_rows($result) > 0) {
		
		echo '<br><br>
			<div id="rate">
			<h4>Your review is important and valuable for us!</h4>
			<h4>Please enter your satisfaction score.</h4>
			<form action="/client.php" method="post">
			<p>Contract ID
			<select name="ContractID"> 
		';
		
		while($row = mysqli_fetch_assoc($result)) {
			echo '<option value="'.$row["ContractID"].'">'.$row["ContractID"].'</option>';
		}
		echo '
			</select>
			Your rate
			<select name="Score">
			  <option value="1">1</option>
			  <option value="2">2</option>
			  <option value="3">3</option>
			  <option value="4">4</option>
			  <option value="5">5</option>
			</select></p>
			 <input type="submit" value="Submit">
			</form> 
			<h4>'.$update.'</h4>
			</div>
		';
		
	}
	
	//Manager Rating Display
	
	$sql = '
	select Employee.FirstName, Employee.LastName, Contract.ContractID, Contract.Category, SatisfactionScore
	from Employee, Contract, ResponsibleFor
	where Contract.ContractID = ResponsibleFor.ContractID
	and EmployeeID = ResponsibleFor.ManagerID
	and ManagerID in 

	(select ManagerID
	from ResponsibleFor
	where ContractID in 

	(select ContractID
	from SignedBy
	where CompanyID = '.$id.'))

	and Contract.ContractID not in 

	(select ContractID
	from SignedBy
	where CompanyID = '.$id.')
	
	order by FirstName;

	';
	
	$result = mysqli_query($conn, $sql);
	echo (mysqli_error($conn));
	
	if (mysqli_num_rows($result) > 0) {
		
		echo '<h3>Manager Performance Statistics</h3>';
		echo '<h4>Please understand that contract details from other clients are hided for privacy reasons</h4>';
		
		//Table Header
		echo '		
		<div style="overflow-x:auto;">
		  <table>
			<tr>
			  <th>Manager</th>
			  <th>Contract ID</th>
			  <th>Category</th>
			  <th>Satisfaction Score</th>
			</tr>
		';
		
		while($row = mysqli_fetch_assoc($result)) {
			
			echo "<tr>";
			
			echo '<td>' .$row["FirstName"].' '.$row["LastName"]. '</td>';
			echo '<td>' .$row["ContractID"]. '</td>';
			echo '<td>' .$row["Category"]. '</td>';
			echo '<td>' .$row["SatisfactionScore"]. '</td>';
			
			echo "</tr>";
		}
		echo "</table></div>";
	}
	
	//Log Out Button
	echo '<br><br>
		<form action="/login.php">
		  <input type="submit" value="Log Out">
		</form> 
	';
	mysqli_close($conn);
}
?>