<?php

function sql_single ($conn, $sql){
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($result);
	return $row;
}

session_start();
echo '<title>Manager Portal</title>
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
	
	.collapsible {
		background-color: #777;
		color: white;
		cursor: pointer;
		padding: 18px;
		width: 100%;
		border: none;
		text-align: left;
		outline: none;
		font-size: 15px;
	}

	.active, .collapsible:hover {
		background-color: #555;
	}
	
	.content {
    display: none;
    overflow: hidden;
	}

	</style>		
';

//Illegal access with only URL
if ($_SESSION["Role"] != "Manager"){
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
	
	$sql = "SELECT FirstName, LastName 
	FROM Employee
	WHERE EmployeeID = ".$id.";";
	$result = mysqli_query($conn, $sql);
	
    echo '<h1>Welcome to the Manager Portal</h1>';
	
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo "<h3>".$row["FirstName"]." ".$row["LastName"]."</h3>";
			echo '<h3>Employee ID: '.$id.'</h3>';
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
	
	//Line of Business
	$sql = "SELECT LOB 
	FROM Specialized
	WHERE EmployeeID = ".$id.";";
	$result = mysqli_query($conn, $sql);
	
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo "<h3>Your specialized Line Of Business is ".$row["LOB"]."</h3><br>";
		}
	}
	
	
	//Filter Contract by Category
	echo '<h3>Filter Contract by Category</h3>
	<form action="/manager.php" method="post">
		<select name = "contractCatFilter">
			<option value="All">All</option>
			<option value="Premium">Premium</option>
			<option value="Diamond">Diamond</option>
			<option value="Gold">Gold</option>
			<option value="Silver">Silver</option>
		</select>
		<input type="submit" value="Show only this type of contract">
	</form><br>
	';
	
	if (isset($_POST["contractCatFilter"]) and $_POST["contractCatFilter"] != 'All'){
		$contractCatFilter = 'and Category = "'.$_POST["contractCatFilter"].'"';
	}
	
	
	//Execute Employee Operation on DB
	
	if (isset($_POST['EmployeeID_Add'])){
		$sql = '
		insert into WorksOn (EmployeeID, ContractID)
		values ('.$_POST["EmployeeID_Add"].', '.$_POST["ContractID"].');
		';
		
		if (mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">New employee has been allocated to the contract successfully. </h4>';
		} else {
			echo '<h4 style="color:red;">Failed to allocate new employee to the contract: ' . mysqli_error($conn). "</h4>";
		}
	}
	
	if (isset($_POST['EmployeeID_Remove'])){
		$sql = '
		delete from WorksOn
		where EmployeeID = '.$_POST["EmployeeID_Remove"].'
		and ContractID = '.$_POST["ContractID"].';
		';
		
		if (mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">Employee has been deallocated from the contract successfully. </h4>';
		} else {
			echo '<h4 style="color:red;">Failed to deallocate the employee from the contract: ' . mysqli_error($conn). "</h4>";
		}
	}

	//Contract ID array
	$contracts = array();
	
	
	//Display Manager's Active Contract
	echo '<h3>Active contracts assigned to you</h3>';
	
	$sql = '
	select Contract.ContractID, Category, Service, ACV, InitialAmount, StartDate as SortDate, DATE_FORMAT(StartDate,"%b-%d-%Y") as StartDate, 
	DATE_FORMAT(FirstDelivered,"%b-%d-%Y") as FirstDelivered, DATE_FORMAT(SecondDelivered,"%b-%d-%Y") as SecondDelivered, 
	DATE_FORMAT(ThirdDelivered,"%b-%d-%Y") as ThirdDelivered, DATE_FORMAT(FourthDelivered,"%b-%d-%Y") as FourthDelivered, 
	SatisfactionScore
	from Contract
	where ContractID in
	
	(select ContractID
	from ResponsibleFor 
	where ManagerID = "'.$id.'")
	'.$contractCatFilter.'
	and
	(((Category = "Silver") and (FourthDelivered is null))
	or
	((Category != "Silver") and (ThirdDelivered is null)))

	order by SortDate DESC;
	
	';
	
	$result = mysqli_query($conn, $sql);
	
	if (mysqli_num_rows($result) > 0){
		

		
		//Table Header
		echo '		
		<div style="overflow-x:auto;">
		  <table>
			<tr>
			  <th>Contract ID</th>
			  <th>Client ID & Company Name</th>
			  <th>Category</th>
			  <th>Line of Business</th>
			  <th>Service</th>
			  <th>ACV</th>
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
			
			array_push($contracts,$row["ContractID"]);
			
			$sql = '
			select CompanyID, CompanyName
			from Company
			where CompanyID in 
		
			(select CompanyID
			from SignedBy
			where ContractID = '.$row["ContractID"].')
			
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
			echo '<td>' .$name["CompanyID"].'   '.$name["CompanyName"]. '</td>';
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
		echo '<h4 style="color:magenta;">No active contracts '. mysqli_error($conn). "</h4>";
	}
	
	echo "<br><br>";
	
	
	//Display Manager's Expired Contract
	
	echo '<button class="collapsible">Expired contracts assigned to you (Click to expand)</button>
			<div class="content">';
	echo '<h3>Expired contracts assigned to you</h3>';
	
	$sql = '
	select Contract.ContractID, Category, Service, ACV, InitialAmount, StartDate as SortDate, DATE_FORMAT(StartDate,"%b-%d-%Y") as StartDate, 
	DATE_FORMAT(FirstDelivered,"%b-%d-%Y") as FirstDelivered, DATE_FORMAT(SecondDelivered,"%b-%d-%Y") as SecondDelivered, 
	DATE_FORMAT(ThirdDelivered,"%b-%d-%Y") as ThirdDelivered, DATE_FORMAT(FourthDelivered,"%b-%d-%Y") as FourthDelivered, 
	SatisfactionScore
	from Contract
	where ContractID in
	
	(select ContractID
	from ResponsibleFor 
	where ManagerID = "'.$id.'")
	'.$contractCatFilter.'
	
	and
	(((Category = "Silver") and (FourthDelivered is not null))
	or
	((Category != "Silver") and (ThirdDelivered is not null)))

	order by SortDate DESC;
	
	';
	
	$result = mysqli_query($conn, $sql);
	
	if (mysqli_num_rows($result) > 0){
		

		
		//Table Header
		echo '		
		<div style="overflow-x:auto;">
		  <table>
			<tr>
			  <th>Contract ID</th>
			  <th>Client ID & Company Name</th>
			  <th>Category</th>
			  <th>Line of Business</th>
			  <th>Service</th>
			  <th>ACV</th>
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
			
			array_push($contracts,$row["ContractID"]);
			
			$sql = '
			select CompanyID, CompanyName
			from Company
			where CompanyID in 
		
			(select CompanyID
			from SignedBy
			where ContractID = '.$row["ContractID"].')
			
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
			echo '<td>' .$name["CompanyID"].'   '.$name["CompanyName"]. '</td>';
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
		echo '<h4 style="color:magenta;">No expired contracts '. mysqli_error($conn). "</h4>";
	}
	
	echo "</div><br><br>";
	
	
	//Display Other Manager's Contract
	echo '<button class="collapsible">Contracts assigned to other manager (Click to expand)</button>
			<div class="content">';
	
	$sql = '
	select Contract.ContractID, Category, Service, ACV, InitialAmount, StartDate as SortDate, DATE_FORMAT(StartDate,"%b-%d-%Y") as StartDate, 
	DATE_FORMAT(FirstDelivered,"%b-%d-%Y") as FirstDelivered, DATE_FORMAT(SecondDelivered,"%b-%d-%Y") as SecondDelivered, 
	DATE_FORMAT(ThirdDelivered,"%b-%d-%Y") as ThirdDelivered, DATE_FORMAT(FourthDelivered,"%b-%d-%Y") as FourthDelivered, 
	SatisfactionScore
	from Contract
	where ContractID in
	
	(select ContractID
	from ResponsibleFor 
	where ManagerID != "'.$id.'")
	'.$contractCatFilter.'
	
	order by SortDate DESC;
	
	';
	
	$result = mysqli_query($conn, $sql);
	
	if (mysqli_num_rows($result) > 0){
		
		//Table Header
		echo '		
		<div style="overflow-x:auto;">
		  <table>
			<tr>
			  <th>Contract ID</th>
			  <th>Client ID & Company Name</th>
			  <th>Category</th>
			  <th>Line of Business</th>
			  <th>Service</th>
			  <th>ACV</th>
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
			select CompanyID, CompanyName
			from Company
			where CompanyID in 
		
			(select CompanyID
			from SignedBy
			where ContractID = '.$row["ContractID"].')
			
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
			echo '<td>' .$name["CompanyID"].'   '.$name["CompanyName"]. '</td>';
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
		echo '<h4 style="color:magenta;">Failed to retrieve contracts: '. mysqli_error($conn). "</h4>";
	}
	
	echo '</div> <br><br>';
	
	
	
	//Employee Operation
	
	echo "<h3>Employee Operation</h3>";
	
	foreach ($contracts as $ContractID) {
		
		echo '<button class="collapsible">Contract ID: '.$ContractID.'</button>
		<div class="content">';
		
		//Employees working on your contracts
		
		echo '<h4>Employees working on this contract</h4>';
		
		$sql = '
		
		select Hours, Employee.EmployeeID, DATE_FORMAT(HiredDate,"%b-%d-%Y") as HiredDate, 
		Insurance, Department, FirstName, LastName, Role, PWD, ContractCat, Province 
		from Employee, WorksOn
		where Employee.EmployeeID = WorksOn.EmployeeID
		and ContractID = '.$ContractID.';
		
		';
		
		$result = mysqli_query($conn, $sql);
		
		//echo(mysqli_error($conn));
		
		//If there are employees working on this contract
		if (mysqli_num_rows($result) > 0) {
			
			//Table Header
			echo '		
			<div style="overflow-x:auto;">
			  <table>
				<tr>
				  <th>Employee ID</th>
				  <th>Name</th>
				  <th>Hours worked on this contract</th>
				  <th>Hired Date</th>
				  <th>Insurance Plan</th>
				  <th>Department</th>
				  <th>Preferred Contract Category</th>
				  <th>Province</th>
				</tr>
			';
			
			//Store an array for EmployeeID
			if (isset($employeeOns)){
				unset($employeeOns);
			}
			$employeeOns = array();

			
			while($row = mysqli_fetch_assoc($result)) {
				
				//Push EmployeeID into array
				array_push($employeeOns,$row["EmployeeID"]);
				
				echo "<tr>";
				
				echo '<td>' .$row["EmployeeID"]. '</td>';
				echo '<td>' .$row["FirstName"].' '.$row["LastName"]. '</td>';
				echo '<td>' .$row["Hours"]. '</td>';
				echo '<td>' .$row["HiredDate"]. '</td>';
				echo '<td>' .$row["Insurance"]. '</td>';
				echo '<td>' .$row["Department"]. '</td>';
				echo '<td>' .$row["ContractCat"]. '</td>';
				echo '<td>' .$row["Province"]. '</td>';
				
				echo "</tr>";
				
			}
			echo "</table></div>";
			
			//Remove Employee
			echo '<h4>Remove employee from this contract</h4>';
			echo '<form action="/manager.php" method="post">';
			echo '<select id="EmployeeID_Remove" name="EmployeeID_Remove">';
			foreach ($employeeOns as $EmployeeID){
				echo '<option value='.$EmployeeID.'>'.$EmployeeID;
			}
			
			echo '</select> <br><br>';
			echo '<input type="hidden" id="ContractID" name="ContractID" value="'.$ContractID.'">';
			echo '<input type="submit" value="Submit">';
			echo '</form>';
			
		} else {
			echo "No employee currently working on this contract.";
		}
		
		//Employees available for your contracts
		
		echo '<h4>Employees available for this contract</h4>';
		
		$sql = '
		
		select EmployeeID, DATE_FORMAT(HiredDate,"%b-%d-%Y") as HiredDate, 
		Insurance, Department, FirstName, LastName, Role, PWD, ContractCat, Province 
		from Employee
		where ContractCat in
		
		(select Category
		from Contract
		where ContractID = '.$ContractID.')
		
		and EmployeeID not in
		
		(select EmployeeID
		from WorksOn
		where ContractID = '.$ContractID.');
		
		';
		
		$result = mysqli_query($conn, $sql);
		echo (mysqli_error($conn));
		
		//If there are employees available for this contract
		if (mysqli_num_rows($result) > 0) {
			
			//Table Header
			echo '		
			<div style="overflow-x:auto;">
			  <table>
				<tr>
				  <th>Employee ID</th>
				  <th>Name</th>
				  <th>Hired Date</th>
				  <th>Insurance Plan</th>
				  <th>Department</th>
				  <th>Preferred Contract Category</th>
				  <th>Province</th>
				</tr>
			';
			
			//Store an array for EmployeeID
			if (isset($employeeAvais)){
				unset($employeeAvais);
			}
			$employeeAvais = array();

			
			while($row = mysqli_fetch_assoc($result)) {
				
				//Push EmployeeID into array
				array_push($employeeAvais,$row["EmployeeID"]);
				
				echo "<tr>";
				
				echo '<td>' .$row["EmployeeID"]. '</td>';
				echo '<td>' .$row["FirstName"].' '.$row["LastName"]. '</td>';
				echo '<td>' .$row["HiredDate"]. '</td>';
				echo '<td>' .$row["Insurance"]. '</td>';
				echo '<td>' .$row["Department"]. '</td>';
				echo '<td>' .$row["ContractCat"]. '</td>';
				echo '<td>' .$row["Province"]. '</td>';
				
				echo "</tr>";
				
			}
			echo "</table></div>";
			
			//Add Employee to this contract
			echo '<h4>Add employee to work on this contract</h4>';
			echo '<form action="/manager.php" method="post">';
			echo '<select id="EmployeeID_Add" name="EmployeeID_Add">';
			foreach ($employeeAvais as $EmployeeID){
				echo '<option value='.$EmployeeID.'>'.$EmployeeID;
			}
			echo '</select> <br><br>';
			echo '<input type="hidden" id="ContractID" name="ContractID" value="'.$ContractID.'">';
			echo '<input type="submit" value="Submit">';
			echo '</form>';
			
		} else {
			echo "No additional employees available for this contract.";
		}
				
		
		
		
		echo '</div> <br><br>';
		
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
		<form action="/manager.php" method = "post">
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
	
	//JS
	echo'
	<script>
	//Adapted From https://www.w3schools.com/howto/howto_js_collapsible.asp
	
	var coll = document.getElementsByClassName("collapsible");
	var i;

	for (i = 0; i < coll.length; i++) {
		coll[i].addEventListener("click", function() {
			this.classList.toggle("active");
			var content = this.nextElementSibling;
			if (content.style.display === "block") {
				content.style.display = "none";
			} else {
				content.style.display = "block";
			}
		});
	}
	</script>
	';
	mysqli_close($conn);
}
	
	
?>