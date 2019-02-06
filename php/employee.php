<?php

function sql_single ($conn, $sql){
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($result);
	return $row;
}

session_start();
echo '<title>Employee Portal</title>
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
if ($_SESSION["Role"] != "Employee"){
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
	
	
	//Greeting Information
	$id = $_SESSION["EmployeeID"];
	
	$sql = "SELECT FirstName,LastName,ContractCat
	FROM Employee
	WHERE EmployeeID = ".$id.";";
	$result = mysqli_query($conn, $sql);
	
    echo '<h1>Welcome to the Employee Portal</h1>';
	
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo "<h3>".$row["FirstName"]." ".$row["LastName"]."</h3>";
			echo '<h3>Employee ID: '.$id.'</h3><br>';
			$ContractCat = $row["ContractCat"];
		}
	} else {
		echo "Sorry, we are unable to retreive your employee information at the moment";
	}
	
	//Update Insurance Plan
	
	if(isset($_POST['Insurance'])){
		$sql = '
		update Employee
		set Insurance = "'.$_POST['Insurance'].'"
		where EmployeeID = '.$id.';
		';
		
		if (mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">Your employee insurance plan has been updated successfully. </h4>';
		} else {
			echo '<h4 style="color:red;">Failed to update your employee insurance plan: ' . mysqli_error($conn). "</h4>";
		}
	}
	
	//Update Working Hours
	if (isset($_POST['ContractID']) and isset($_POST['hours'])){
		$sql = '
		update WorksOn
		set Hours = '.$_POST['hours'].'
		where EmployeeID = '.$id.'
		and ContractID = '.$_POST['ContractID'].';
		';
		if (mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">Working hours on Contract ID: '.$_POST['ContractID'].' has been updated successfully. </h4>';
		} else {
			echo '<h4 style="color:red;">Failed to update working hours on Contract ID: '.$_POST['ContractID']. mysqli_error($conn). "</h4>";
		}
	}
	
	//Update preferred Contract Category
	if (isset($_POST['ContractCat'])){
		$sql = '
		update Employee
		set ContractCat = "'.$_POST['ContractCat'].'"
		where EmployeeID = '.$id.';
		';
		if (mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">Preferred Contract Category has been updated successfully. </h4>';
		} else {
			echo '<h4 style="color:red;">Failed to update Preferred Contract Category '.$_POST['ContractID']. mysqli_error($conn). "</h4>";
		}
	}	
	
	//Preferred Contract Category
	
	if (isset($_POST['ContractCat'])){
		echo '<h3>Your preferred contract category is '.$_POST['ContractCat'].'</h3>';
	} else if ($ContractCat != ''){
		echo '<h3>Your preferred contract category is '.$ContractCat.'</h3>';
	} else {
		echo '<h3>Your preferred contract category is undecided</h3>';
	}
	
	echo 'Update your preferred contract category <br><br>
		<form action="/employee.php" method="post">
		<select name="ContractCat">
		  <option value="Premium">Premium</option>
		  <option value="Diamond">Diamond</option>
		  <option value="Gold">Gold</option>
		  <option value="Silver">Silver</option>
		</select>
		 <input type="submit" value="Submit">
		</form> 
	
	';
	
	
	//Logging Working Hour
	
	$sql = '
	select Hours, Contract.ContractID, Category, Service, ACV, InitialAmount, 
		DATE_FORMAT(StartDate,"%b-%d-%Y") as StartDate, 
		DATE_FORMAT(FirstDelivered,"%b-%d-%Y") as FirstDelivered, 
		DATE_FORMAT(SecondDelivered,"%b-%d-%Y") as SecondDelivered, 
		DATE_FORMAT(ThirdDelivered,"%b-%d-%Y") as ThirdDelivered, 
		DATE_FORMAT(FourthDelivered,"%b-%d-%Y") as FourthDelivered, SatisfactionScore
	from WorksOn, Contract
	where WorksOn.ContractID = Contract.ContractID 
	and WorksOn.EmployeeID = '.$id.';
	';
	$result = mysqli_query($conn, $sql);
	
	echo '<h3>Contracts you are working on</h3>';
	
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			
			$sql = '
			select EmployeeID, FirstName, LastName
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
			
			echo '		
			<div style="overflow-x:auto;">
			  <table>
				<tr>
				  <th>Contract ID</th>
				  <th>Manager</th>
				  <th>Hours you worked on</th>
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
			
			echo "<tr>";
			
			echo '<td>' .$row["ContractID"]. '</td>';
			echo '<td> ID: ' .$name["EmployeeID"].' '.$name["FirstName"].' '.$name["LastName"]. '</td>';
			echo '<td>' .$row["Hours"]. '</td>';
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
			echo "</table></div>";
			
			echo '';
			echo '
			<form action="/employee.php" method="post">
			  <h4>Update the number of hours you worked on Contract ID: '.$row["ContractID"].'</h4>
			  <input type="hidden" id="ContractID" name="ContractID" value="'.$row["ContractID"].'">
			  <input type="number" name="hours" value='.$row["Hours"].'><br><br>
			  <input type="submit" value="Submit">
			</form>
			';
			
		}
	} else {
		echo "<h4>You are not woking on any contract currently</h4>";
	}

	//Employee Insurance Plan Display
	echo '<br><h3>Employee Insurance Plan</h3>';
	
	$sql = '
	select Insurance
	from Employee
	where EmployeeID = '.$_SESSION["EmployeeID"].';
	
	';
	
	$result = mysqli_query($conn, $sql);
	
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo "<h4>Your current insurance plan is ".$row["Insurance"]."</h4>";
		}
		
		echo 'Change your plan selection <br><br>';
		echo '
		<form action="/employee.php" method = "post">
		<select name="Insurance">
			<option value="Premium">Premium
			<option value="Silver">Silver
			<option value="Normal">Normal
		</select>
		<input type="submit" value="Submit">
		</form>
		';
	} else {
		echo "Sorry, we are unable to retreive your employee insurance information at the moment";
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