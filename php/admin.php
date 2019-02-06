<?php

function sql_single ($conn, $sql){
	$result = mysqli_query($conn, $sql);
	echo mysqli_error($conn);
	$row = mysqli_fetch_array($result);
	return $row;
}

function select_helper ($b){
	if ($b){
		return ' selected ';
	}
}

function null_helper ($value){
	if($value == ''){
		return 'null';
	} else {
		return '"'.$value.'"';
	}
}

session_start();
echo '<title>Administrator Portal</title>
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
if ($_SESSION["Role"] != "Admin"){
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
	
    echo '<h1>Welcome to the Administrator Portal</h1>';
	
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo "<h3>".$row["FirstName"]." ".$row["LastName"]."</h3>";
			echo '<h3>Employee ID: '.$id.'</h3><br>';
			$ContractCat = $row["ContractCat"];
		}
	} else {
		echo "Sorry, we are unable to retreive your employee information at the moment";
	}
	
	//print.php redirect
	echo '<br><br>
		<form action="/report.php">
		  <input type="submit" value="Reports Page">
		</form> 
	';
	
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
	
	//Client Update Action and Response
	if ($_POST['Client_action'] == "Submit All Changes"){
		
		$sql = '
		
		update Company
		
		set 
		CompanyID = '.null_helper($_POST["CompanyID_update"]).',
		PWD = '.null_helper($_POST["PWD_update"]).',
		CompanyName = '.null_helper($_POST["CompanyName_update"]).',
		ContactFirstName = '.null_helper($_POST["ContactFirstName_update"]).',
		ContactLastName = '.null_helper($_POST["ContactLastName_update"]).',
		ContactMiddleInitial = '.null_helper($_POST["ContactMiddleInitial_update"]).',
		ContactNumber = '.null_helper($_POST["ContactNumber_update"]).',
		ContactEmail = '.null_helper($_POST["ContactEmail_update"]).',
		Street = '.null_helper($_POST["Street_update"]).',
		City = '.null_helper($_POST["City_update"]).',
		Province = '.null_helper($_POST["Province_update"]).',
		PostalCode = '.null_helper($_POST["PostalCode_update"]).'

		where CompanyID = '.$_POST['CompanyID_old'].';

		';
		
		if (mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">Selected client has been updated successfully. </h4>';

			$sql = '
			
			update SignedBy
			set CompanyID = '.$_POST["CompanyID_update"].'
			where CompanyID = '.$_POST['CompanyID_old'].';

			';
			if (!mysqli_query($conn, $sql)){
				echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
			} 
			
		} else {
			echo '<h4 style="color:red;">Failed to update the selected client: ' . mysqli_error($conn). "</h4>";
		}
		

	}
	
	//Client Delete Action and Response
	if ($_POST['Client_action'] == "Delete"){
		
		$sql = '
		select ContractID
		from SignedBy
		where CompanyID = '.$_POST['CompanyID_old'].';
		
		';
		$result = mysqli_query($conn, $sql);	
		
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {

				$sql = '
				delete from Contract where ContractID = '.$row['ContractID'].';
				';
				if (!mysqli_query($conn, $sql)){
					echo '<h4 style="color:red;">Failed to delete the selected contract: ' . mysqli_error($conn). "</h4>";
				}
				
				$sql = '
				delete from SignedBy where ContractID = '.$row['ContractID'].';
				';
				if (!mysqli_query($conn, $sql)){
					echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
				} 
				
				
				$sql = '
				delete from ResponsibleFor where ContractID = '.$row['ContractID'].';
				';
				if (!mysqli_query($conn, $sql)){
					echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
				} 

				$sql = '
				delete from AssociatedWith where ContractID = '.$row['ContractID'].';
				';
				if (!mysqli_query($conn, $sql)){
					echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
				} 			
				
			}
		}
		
		$sql = 'delete from SignedBy where CompanyID = '.$_POST['CompanyID_old'].';';
		
		if (mysqli_query($conn, $sql)){
			$sql = 'delete from Company where CompanyID = '.$_POST['CompanyID_old'].';';
			if (mysqli_query($conn, $sql)){
				echo '<h4 style="color:red;">Selected client has been deleted successfully. </h4>';
			} else {
				echo '<h4 style="color:red;">Failed to delete the selected client: ' . mysqli_error($conn). "</h4>";
			}
		}

	}
	
	//Contract Update Action and Response
	if ($_POST['Contract_action'] == "Submit All Changes"){
		
		$sql = '
		
		update Contract
		
		set 
		ContractID = '.null_helper($_POST["ContractID_update"]).',
		Category = '.null_helper($_POST["Category_update"]).',
		Service = '.null_helper($_POST["Service_update"]).',
		ACV = '.null_helper($_POST["ACV_update"]).',
		InitialAmount = '.null_helper($_POST["InitialAmount_update"]).',
		StartDate = '.null_helper($_POST["StartDate_update"]).',
		FirstDelivered = '.null_helper($_POST["FirstDelivered_update"]).',
		SecondDelivered = '.null_helper($_POST["SecondDelivered_update"]).',
		ThirdDelivered = '.null_helper($_POST["ThirdDelivered_update"]).',
		FourthDelivered = '.null_helper($_POST["FourthDelivered_update"]).',
		SatisfactionScore = '.null_helper($_POST["SatisfactionScore_update"]).'

		where ContractID = '.$_POST['ContractID_old'].';

		';
		
		if (mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">Selected contract has been updated successfully. </h4>';

			$sql = '
			
			update SignedBy
			set ContractID = '.$_POST["ContractID_update"].'
			where ContractID = '.$_POST['ContractID_old'].';

			';
			if (!mysqli_query($conn, $sql)){
				echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
			} 
			
			
			$sql = '
			
			update ResponsibleFor
			set ContractID = '.$_POST["ContractID_update"].'
			where ContractID = '.$_POST['ContractID_old'].';
			
			';
			if (!mysqli_query($conn, $sql)){
				echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
			} 

			$sql = '
			
			update AssociatedWith
			set ContractID = '.$_POST["ContractID_update"].'
			where ContractID = '.$_POST['ContractID_old'].';
			
			';
			if (!mysqli_query($conn, $sql)){
				echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
			} 
			
			
		} else {
			echo '<h4 style="color:red;">Failed to update the selected contract: ' . mysqli_error($conn). "</h4>";
		}
		

	}
	
	//Contract Delete Action and Response
	if ($_POST['Contract_action'] == "Delete"){
		
		$sql = '
		delete from Contract where ContractID = '.$_POST['ContractID_old'].';
		';
		if (mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">Selected contract has been deleted successfully. </h4>';
		} else {
			echo '<h4 style="color:red;">Failed to delete the selected contract: ' . mysqli_error($conn). "</h4>";
		}
		
		$sql = '
		delete from SignedBy where ContractID = '.$_POST['ContractID_old'].';
		';
		if (!mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
		} 
		
		
		$sql = '
		delete from ResponsibleFor where ContractID = '.$_POST['ContractID_old'].';
		';
		if (!mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
		} 

		$sql = '
		delete from AssociatedWith where ContractID = '.$_POST['ContractID_old'].';
		';
		if (!mysqli_query($conn, $sql)){
			echo '<h4 style="color:red;">'. mysqli_error($conn). '</h4>';
		} 
	}
	
	//Client Selection
	
	$sql = 'select CompanyID from Company;';
	$result = mysqli_query($conn, $sql);	
	
	echo '
	<h3>Please choose the client to change or delete</h3>
	<form action = "/admin.php" method = "post">
		<select  name="CompanyID_old">';
		
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo '<option value="'.$row["CompanyID"].'" ';
			if ($row["CompanyID"] == $_POST["CompanyID_old"]){
				echo ' selected ';
			}
			echo '>'.$row["CompanyID"];
		}
	}else{
		echo '<option value="">No client is in the system';
	}		
		
		
	echo'	</select>
	
	<input type="submit" name="Client_action" value="Change">
	<input type="submit" name="Client_action" value="Delete">

	</form>
	
	
	';
	
	
	//Client Update Form Display
	
	if ($_POST['Client_action'] == "Change"){
		
		echo '<h4>Client Details for Client ID: '.$_POST['CompanyID_old'].'</h4>';
		
		$sql = '
		select *
		from Company
		where CompanyID = '.$_POST["CompanyID_old"].';
		
		';
		
		//Company to be modified
		$company = sql_single($conn, $sql);
		
			
		echo '
		
		<form action = "/admin.php" method = "post">
		
		<input type="hidden" name="CompanyID_old" value='.$_POST["CompanyID_old"].'>

		Client ID: 
		<input type="number" name="CompanyID_update" value="'.$company['CompanyID'].'" required><br><br>
		
		Client Log-In Password: 
		<input type="password" name="PWD_update" value="'.$company['PWD'].'" required><br><br>
		
		Client Name: 
		<input type="text" name="CompanyName_update" value="'.$company['CompanyName'].'" required><br><br>

		First Name of Contact Person: 
		<input type="text" name="ContactFirstName_update" value="'.$company['ContactFirstName'].'"><br><br>
		
		Middle Initial of Contact Person: 
		<input type="text" name="ContactMiddleInitial_update" value="'.$company['ContactMiddleInitial'].'"><br><br>
		
		Last Name of Contact Person: 
		<input type="text" name="ContactLastName_update" value="'.$company['ContactLastName'].'"><br><br>

		Contact Number: 
		<input type="number" name="ContactNumber_update" value="'.$company['ContactNumber'].'"><br><br>

		Contact Email: 
		<input type="text" name="ContactEmail_update" value="'.$company['ContactEmail'].'"><br><br>	

		Address Street: 
		<input type="text" name="Street_update" value="'.$company['Street'].'"><br><br>		

		Address City: 
		<input type="text" name="City_update" value="'.$company['City'].'"><br><br>				
		
		Address Province: 
		<input type="text" name="Province_update" value="'.$company['Province'].'"><br><br>		

		Address PostalCode: 
		<input type="text" name="PostalCode_update" value="'.$company['PostalCode'].'"><br><br>		
		
		
		';		

		echo '<input type="submit" name="Client_action" value="Submit All Changes">';
		echo '</form>';
		
	}
	
	
	//Contract Selection
	
	$sql = 'select ContractID from Contract;';
	$result = mysqli_query($conn, $sql);	
	
	echo '
	<h3>Please choose the contract to change or delete</h3>
	<form action = "/admin.php" method = "post">
		<select  name="ContractID_old">';
		
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo '<option value="'.$row["ContractID"].'" ';
			if ($row["ContractID"] == $_POST["ContractID_old"]){
				echo ' selected ';
			}
			echo '>'.$row["ContractID"];
		}
	}else{
		echo '<option value="">No contract is in the system';
	}		
		
		
	echo'	</select>
	
	<input type="submit" name="Contract_action" value="Change">
	<input type="submit" name="Contract_action" value="Delete">

	</form>
	
	
	';
	
	
	//Contract Update Form Display
	
	if ($_POST['Contract_action'] == "Change"){
		
		echo '<h4>Contract Details for Contract ID: '.$_POST['ContractID_old'].'</h4>';
		
		$sql = '
		select Employee.EmployeeID, FirstName, LastName, Contract.ContractID, LOB, Category, Service, ACV, InitialAmount, 
		DATE_FORMAT(StartDate,"%Y-%m-%d") as StartDate, DATE_FORMAT(FirstDelivered,"%Y-%m-%d") as FirstDelivered, 
		DATE_FORMAT(SecondDelivered,"%Y-%m-%d") as SecondDelivered, DATE_FORMAT(ThirdDelivered,"%Y-%m-%d") as ThirdDelivered, 
		DATE_FORMAT(FourthDelivered,"%Y-%m-%d") as FourthDelivered, SatisfactionScore
		
		from Contract, Employee, ResponsibleFor
		where Contract.ContractID = ResponsibleFor.ContractID
		and Employee.EmployeeID = ResponsibleFor.ManagerID
		and Contract.ContractID = '.$_POST["ContractID_old"].';
		
		';
		
		//Contract to be modified
		$contract = sql_single($conn, $sql);
		
			
		echo '
		
		<form action = "/admin.php" method = "post">
		
		<input type="hidden" name="ContractID_old" value='.$_POST["ContractID_old"].'>

		Contract ID: 
		<input type="number" name="ContractID_update" value="'.$contract['ContractID'].'"><br><br>';
		
		//Manager Selection
		echo 'Manager Assigned: 
		<select name="ManagerID_update">';
		
		$sql = 'SELECT Employee.EmployeeID, FirstName, LastName
		FROM Employee
		WHERE Role = "Manager";';
		
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				echo '<option value="'.$row["EmployeeID"].'" ';
				if ($row["EmployeeID"] == $contract["EmployeeID"]){
					echo ' selected ';
				}
				echo '>ID: '.$row["EmployeeID"].' '.$row["FirstName"].' '.$row["LastName"];
			}
		}else{
			echo '<option value="">No manager is available in the system';
		}
		echo '</select> <br><br>';
		
		//LOB Selection
		echo '
		
		Line of Business: 
		<select name="LOB_update">
		';
		
		$sql = 'SELECT LOB
		FROM LineOfBusiness';
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				echo '<option value="'.$row["LOB"].'" ';
				if ($row["LOB"] == $contract["LOB"]){
					echo ' selected ';
				}
				echo '>'.$row["LOB"];
			}
		}else{
			echo '<option value="">'.(mysqli_error($conn));
		}
		
		echo '</select> <br><br>';
		
		echo '
		
		Category: 
		<select name="Category_update">
		  <option value="Premium" '.select_helper($contract['Category'] == "Premium").'>Premium
		  <option value="Diamond" '.select_helper($contract['Category'] == "Diamond").'>Diamond
		  <option value="Gold" '.select_helper($contract['Category'] == "Gold").'>Gold
		  <option value="Silver" '.select_helper($contract['Category'] == "Silver").'>Silver
		</select><br><br>
		
		Service: 
		<select name="Service_update">
		  <option value="Cloud" '.select_helper($contract['Service'] == "Cloud").'>Cloud
		  <option value="OnPremises" '.select_helper($contract['Service'] == "OnPremises").'>OnPremises
		</select><br><br>
		
		Annual Contract Value (ACV): $
		<input type="number" name="ACV_update" value="'.$contract['ACV'].'"><br><br>
		
		Initial Amount: $
		<input type="number" name="InitialAmount_update" value="'.$contract['InitialAmount'].'"><br><br>
	
		Contract Start Date (YYYY-MM-DD)
		<input type="text" name="StartDate_update" value="'.$contract['StartDate'].'"><br><br>

		First Deliverable Delivered Date (YYYY-MM-DD)
		<input type="text" name="FirstDelivered_update" value="'.$contract['FirstDelivered'].'"><br><br>

		Second Deliverable Delivered Date (YYYY-MM-DD)
		<input type="text" name="SecondDelivered_update" value="'.$contract['SecondDelivered'].'"><br><br>

		Third Deliverable Delivered Date (YYYY-MM-DD)
		<input type="text" name="ThirdDelivered_update" value="'.$contract['ThirdDelivered'].'"><br><br>
		
		Fourth Deliverable Delivered Date (YYYY-MM-DD) For Silver Level Contract Only
		<input type="text" name="FourthDelivered_update" value="'.$contract['FourthDelivered'].'"><br><br>
		
		SatisfactionScore
		<select name="SatisfactionScore_update">
		  <option value="" '.select_helper($contract['SatisfactionScore'] == "").'>Not Rated
		  <option value=1 '.select_helper($contract['SatisfactionScore'] == "1").'>1
		  <option value=2 '.select_helper($contract['SatisfactionScore'] == "2").'>2
		  <option value=3 '.select_helper($contract['SatisfactionScore'] == "3").'>3
		  <option value=4 '.select_helper($contract['SatisfactionScore'] == "4").'>4
		  <option value=5 '.select_helper($contract['SatisfactionScore'] == "5").'>5
		</select><br><br>		
		
		';
		echo '<input type="submit" name="Contract_action" value="Submit All Changes">';
		echo '</form>';
		
	}
			
	
	//List All Client
	$sql = '
	select *
	from Company
	order by CompanyID;
	
	';
	$result = mysqli_query($conn, $sql);
	
	echo '<h3>All clients in the system</h3>';
	
	if (mysqli_num_rows($result) > 0) {
		
		//Table Header
		echo '		
		<div style="overflow-x:auto;">
		  <table>
			<tr>
			  <th>Client ID</th>
			  <th>Client Name</th>
			  <th>Contact Name</th>
			  <th>Contact Number</th>
			  <th>Contact Email</th>
			  <th>Client Address</th>
			</tr>
		';
		
		while($row = mysqli_fetch_assoc($result)) {
						
			echo "<tr>";
			
			echo '<td>' .$row["CompanyID"]. '</td>';
			echo '<td>' .$row["CompanyName"]. '</td>';
			echo '<td>' .$row["ContactFirstName"].' '.$row["ContactMiddleInitial"].' '.$row["ContactLastName"]. '</td>';
			echo '<td>' .$row["ContactNumber"]. '</td>';
			echo '<td>' .$row["ContactEmail"]. '</td>';
			echo '<td>' .$row["Street"].', '.$row["City"].', '.$row["Province"].', '.$row["PostalCode"].'</td>';
			
			echo "</tr>";
			
		}
		echo "</table></div>";
	} else {
		echo "No clients in the system.";
	}	
	
	
	//List All Contract
	$sql = '
	select Employee.EmployeeID, FirstName, LastName, Contract.ContractID, LOB, Category, Service, ACV, InitialAmount, 
	DATE_FORMAT(StartDate,"%b-%d-%Y") as StartDate, DATE_FORMAT(FirstDelivered,"%b-%d-%Y") as FirstDelivered, 
	DATE_FORMAT(SecondDelivered,"%b-%d-%Y") as SecondDelivered, DATE_FORMAT(ThirdDelivered,"%b-%d-%Y") as ThirdDelivered, 
	DATE_FORMAT(FourthDelivered,"%b-%d-%Y") as FourthDelivered, SatisfactionScore
	
	from Contract, Employee, ResponsibleFor
	where Contract.ContractID = ResponsibleFor.ContractID
	and Employee.EmployeeID = ResponsibleFor.ManagerID
	
	order by ContractID;
	
	';
	$result = mysqli_query($conn, $sql);
	
	echo '<h3>All contracts in the system</h3>';
	
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
						
			echo "<tr>";
			
			echo '<td>' .$row["ContractID"]. '</td>';
			echo '<td> ID: ' .$row["EmployeeID"].' '.$row["FirstName"].' '.$row["LastName"]. '</td>';
			echo '<td>' .$row["Category"]. '</td>';
			echo '<td>' .$row["LOB"]. '</td>';
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
		echo "No contract in the system.";
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
		<form action="/admin.php" method = "post">
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
	
	//print.php redirect
	echo '<br><br>
		<form action="/print.php">
		  <input type="submit" value="Diagnostics Page">
		</form> 
	';
	
	//Log Out Button
	echo '<br><br>
		<form action="/login.php">
		  <input type="submit" value="Log Out">
		</form> 
	';
	mysqli_close($conn);
}
?>