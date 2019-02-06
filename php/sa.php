<?php

function sql_single ($conn, $sql){
	$result = mysqli_query($conn, $sql);
	$row = mysqli_fetch_array($result);
	return $row;
}

function postSet ($name, $value){
	if ($_POST[$name] == $value){
		return 'selected';
	}
}

session_start();
echo '<title>Sales Associate Portal</title>
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
if ($_SESSION["Role"] != "SalesAssociate"){
	echo '<h1>You are accessing this page illegally. </h1>';
	echo '
		<form action="/login.php">
		  <input type="submit" value="Redirect to Log In">
		</form> 
	';
}

else{
	

	
	echo '
	<script>

	//Javascript Post Function
	//Adapted from https://stackoverflow.com/questions/133925/javascript-post-request-like-a-form-submit
	function post(path, method) {
		method = method || "post"; // Set method to post by default if not specified.

		var x = {
			/*CompanyName:document.getElementById("CompanyName").value,
			ContactFirstName:document.getElementById("ContactFirstName").value,
			ContactMiddleInitial:document.getElementById("ContactMiddleInitial").value,
			ContactLastName:document.getElementById("ContactLastName").value,
			ContactNumber:document.getElementById("ContactNumber").value,
			ContactEmail:document.getElementById("ContactEmail").value,
			Street:document.getElementById("Street").value,
			Province:document.getElementById("Province").value,*/
			Mode:document.getElementById("Mode").value //Need Comma to end //Operation Mode
		}; 
		
		if (document.getElementById("CompanyID")){
			x.CompanyID = document.getElementById("CompanyID").value;
		}
		
		if (document.getElementById("CompanyName")){
			x.CompanyName = document.getElementById("CompanyName").value;
		}
		if (document.getElementById("PWD")){
			x.PWD = document.getElementById("PWD").value;
		}
		if (document.getElementById("ContactFirstName")){
			x.ContactFirstName = document.getElementById("ContactFirstName").value;
		}
		if (document.getElementById("ContactMiddleInitial")){
			x.ContactMiddleInitial = document.getElementById("ContactMiddleInitial").value;
		}
		if (document.getElementById("ContactLastName")){
			x.ContactLastName = document.getElementById("ContactLastName").value;
		}
		if (document.getElementById("ContactNumber")){
			x.ContactNumber = document.getElementById("ContactNumber").value;
		}
		if (document.getElementById("ContactEmail")){
			x.ContactEmail = document.getElementById("ContactEmail").value;
		}
		if (document.getElementById("Street")){
			x.Street = document.getElementById("Street").value;
		}
		if (document.getElementById("Province")){
			x.Province = document.getElementById("Province").value;
		}
		if (document.getElementById("City")){
			x.City = document.getElementById("City").value;
		}
		if (document.getElementById("PostalCode")){
			x.PostalCode = document.getElementById("PostalCode").value;
		}
		if (document.getElementById("ContractID")){
			x.ContractID = document.getElementById("ContractID").value;
		}
		if (document.getElementById("Category")){
			x.Category = document.getElementById("Category").value;
		}
		if (document.getElementById("Service")){
			x.Service = document.getElementById("Service").value;
		}
		if (document.getElementById("ACV")){
			x.ACV = document.getElementById("ACV").value;
		}
		if (document.getElementById("InitialAmount")){
			x.InitialAmount = document.getElementById("InitialAmount").value;
		}
		if (document.getElementById("LOB")){
			x.LOB = document.getElementById("LOB").value;
		}
		if (document.getElementById("ManagerID")){
			x.ManagerID = document.getElementById("ManagerID").value;
		}

		
		var form = document.createElement("form");
		form.setAttribute("method", method);
		form.setAttribute("action", path);

		for(var key in x) {
			if(x.hasOwnProperty(key)) {
				var hiddenField = document.createElement("input");
				hiddenField.setAttribute("type", "hidden");
				hiddenField.setAttribute("name", key);
				hiddenField.setAttribute("value", x[key]);

				form.appendChild(hiddenField);
			}
		}

		document.body.appendChild(form);
		form.submit();
	}
	</script>
	';
	
	
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
	
    echo '<h1>Welcome to the Sales Associate Portal</h1>';
	
	if (mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			echo "<h3>".$row["FirstName"]." ".$row["LastName"]."</h3>";
			echo '<h3>Employee ID: '.$id.'</h3><br>';
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

	//Performing Client DB Insert and show result if needed
	
	if (($_POST["Mode_submit"] == "newContract_newClient") or ($_POST["Mode_submit"] == "newClient")){
		
		// CompanyID Defined
		if ($_POST["CompanyID_submit"] != ''){
			$sql = '
			insert into Company (CompanyID,	CompanyName, ContactFirstName, ContactLastName, ContactMiddleInitial, 
			ContactNumber, ContactEmail, Street, City, Province, PostalCode, PWD) values
			("'.$_POST["CompanyID_submit"].'", "'.$_POST["CompanyName_submit"].'", "'.$_POST["ContactFirstName_submit"].'", "'.$_POST["ContactLastName_submit"].'", 
			"'.$_POST["ContactMiddleInitial_submit"].'", "'.$_POST["ContactNumber_submit"].'", "'.$_POST["ContactEmail_submit"].'", "'.$_POST["Street_submit"].'", 
			"'.$_POST["City_submit"].'", "'.$_POST["Province_submit"].'", "'.$_POST["PostalCode_submit"].'", "'.$_POST["PWD_submit"].'")
			';
		}
		// Default CompanyID		
		else {
			$sql = '
			insert into Company (CompanyName, ContactFirstName, ContactLastName, ContactMiddleInitial, 
			ContactNumber, ContactEmail, Street, City, Province, PostalCode, PWD) values
			("'.$_POST["CompanyName_submit"].'", "'.$_POST["ContactFirstName_submit"].'", "'.$_POST["ContactLastName_submit"].'", 
			"'.$_POST["ContactMiddleInitial_submit"].'", "'.$_POST["ContactNumber_submit"].'", "'.$_POST["ContactEmail_submit"].'", "'.$_POST["Street_submit"].'", 
			"'.$_POST["City_submit"].'", "'.$_POST["Province_submit"].'", "'.$_POST["PostalCode_submit"].'", "'.$_POST["PWD_submit"].'")
			';
		}
		

		//Check Company Insert Result
		if (mysqli_query($conn, $sql)) {
			$autoCompanyID = mysqli_insert_id($conn);
			echo '<h4 style="color:red;">New client account created successfully. </h4>';
			if ($autoCompanyID != ''){
				echo '<h4 style="color:red;">Auto allocated client ID is: '.$autoCompanyID.'</h4>';
				$_POST['CompanyID_submit'] = $autoCompanyID;
			}
		} else {
			echo '<h4 style="color:red;">Failed to create new client: ' . mysqli_error($conn). "</h4>";
		}
		
	}
	
	//Performing Contract DB Insert and show result if needed
	if (($_POST["Mode_submit"] == "newContract_newClient") or ($_POST["Mode_submit"] == "newContract_existingClient")){
	
		//Check CompanyID Existence

		$sql = '
		select *
		from Company
		where CompanyID = '.$_POST["CompanyID_submit"].';
		';
		
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) == 0) {
			echo '<h4 style="color:magenta;">Submitted Client ID does not exist. </h4>';
		} else {
			
			//Defined Contract ID
			if ($_POST["ContractID_submit"] != ''){
				
				$sql = '
				insert into Contract (ContractID, Category, Service, ACV, InitialAmount)
				values ("'.$_POST['ContractID_submit'].'", "'.$_POST['Category_submit'].'", "'.$_POST['Service_submit'].'",
				"'.$_POST['ACV_submit'].'", "'.$_POST['InitialAmount_submit'].'")
				';

			} //Default Contract ID
			else {
				$sql = '
				insert into Contract (Category, Service, ACV, InitialAmount)
				values ("'.$_POST['Category_submit'].'", "'.$_POST['Service_submit'].'",
				"'.$_POST['ACV_submit'].'", "'.$_POST['InitialAmount_submit'].'")
				';
			}
			
			//Check Contract Insert Result
			if (mysqli_query($conn, $sql)) {
				$last_id = mysqli_insert_id($conn);
				echo '<h4 style="color:magenta;">New contract created successfully. </h4>';
				if ($last_id != ''){
					echo '<h4 style="color:magenta;">Auto allocated contract ID is: '.$last_id.'</h4>';
					$_POST['ContractID_submit'] = $last_id;
				}
				
				//Require Change SignedBy, ResponsibleFor, AssociatedWith 
				//Now $_POST['CompanyID_submit'] is real companyID in any case
				//and $_POST['ContractID_submit'] is real contractID also
				
				$sql = '
				insert into SignedBy (ContractID, CompanyID)
				values ('.$_POST['ContractID_submit'].', '.$_POST['CompanyID_submit'].');
				';
				if (!mysqli_query($conn, $sql)){
					echo '<h4 style="color:magenta;">Failed to create SignedBy relation </h4>';
				}

				$sql = '
				insert into ResponsibleFor (ManagerID, ContractID)
				values ('.$_POST['ManagerID_submit'].', '.$_POST['ContractID_submit'].');
				';
				if (!mysqli_query($conn, $sql)){
					echo '<h4 style="color:magenta;">Failed to create ResponsibleFor relation </h4>';
				}

				$sql = '
				insert into AssociatedWith (ContractID, LOB)
				values ('.$_POST['ContractID_submit'].', "'.$_POST['LOB_submit'].'");
				';			
				if (!mysqli_query($conn, $sql)){
					echo '<h4 style="color:magenta;">Failed to create AssociatedWith relation </h4>';
				}
				
			} else {
				echo '<h4 style="color:magenta;">Failed to create new contract: '. mysqli_error($conn). "</h4>";
			}				
			
		}

		
		
		
	}
	
	
	//Display All Existing Client
	
	echo '<button class="collapsible">Click to check existing clients information here</button>
			<div class="content">';
	
							//echo '<h3>Existing Clients</h3>';
	
	$sql = "SELECT CompanyID, CompanyName, ContactFirstName, ContactMiddleInitial, ContactLastName, ContactNumber, ContactEmail, Street, City, Province, PostalCode 
	FROM scc353_1.Company;";
	$result = mysqli_query($conn, $sql);
	
	if (mysqli_num_rows($result) > 0) {
		
		//Table Header
		echo '		
		<div style="overflow-x:auto;">
		  <table>
			<tr>
			  <th>Client ID</th>
			  <th>Company Name</th>
			  <th>Contact Name</th>
			  <th>Contact Number</th>
			  <th>Contact Email</th>
			  <th>Street Address</th>
			  <th>City</th>
			  <th>Province</th>
			  <th>Postal Code</th>
			</tr>
		';
		
		while($row = mysqli_fetch_assoc($result)) {
			
			echo "<tr>";
			
			echo '<td>' .$row["CompanyID"]. '</td>';
			echo '<td>' .$row["CompanyName"]. '</td>';
			echo '<td>' .$row["ContactFirstName"].' '.$row["ContactMiddleInitial"].' '.$row["ContactLastName"]. '</td>';
			echo '<td>' .$row["ContactNumber"]. '</td>';
			echo '<td>' .$row["ContactEmail"]. '</td>'; 
			echo '<td>' .$row["Street"]. '</td>';
			echo '<td>' .$row["City"]. '</td>';
			echo '<td>' .$row["Province"]. '</td>';
			echo '<td>' .$row["PostalCode"]. '</td>';
			
			echo "</tr>";
			
		}
		echo "</table></div>";
	} else {
		echo "No client information is available";
	}

	echo '</div>';
	


	
	//Set Default Working Mode
	if (!isset($_POST["Mode"])){
		$_POST["Mode"] = "newContract_newClient";
	}
	
	//Set Default Province Data
	if (!isset($_POST["Province"])){
		$_POST["Province"] = "AB";
	}
	
	//Set Default Line of Business Data
	if (!isset($_POST["LOB"])){
		$_POST["LOB"] = "Cloud Services";
	}
	
	//Diasgnotics
	//var_dump ($_POST);
	
	echo '<h4>Please choose your task</h4>';
	
	//Contract & Client Creation Form
	echo ' <form action="/sa.php" method="post"> <br>';
	
	echo '
	
	<select id="Mode" name="Mode_submit" onchange="'."post('/sa.php')".'">
	  <option value="newContract_newClient" '.postSet("Mode", "newContract_newClient").'>Create New Contract From New Client
	  <option value="newContract_existingClient" '.postSet("Mode", "newContract_existingClient").'>Create New Contract From Existing Client
	  <option value="newClient" '.postSet("Mode", "newClient").'>Create New Client Account
	</select>
	
	';
	

	
	
	if (($_POST["Mode"] == "newContract_newClient") or ($_POST["Mode"] == "newClient")){
		
		echo '
		<br>
		<h4>Client Info</h4>
		<br>
		Client ID: 
		<input type="number" id="CompanyID" name="CompanyID_submit" placeholder="Optional" value="'.$_POST['CompanyID'].'"><br><br>
		Company Name: 
		<input type="text" id="CompanyName" name="CompanyName_submit" value="'.$_POST['CompanyName'].'" required><br><br>
		Account Password: 
		<input type="password" id="PWD" name="PWD_submit" value="'.$_POST['PWD'].'" required><br><br>
		Contact Person First Name: 
		<input type="text" id="ContactFirstName" name="ContactFirstName_submit" value="'.$_POST['ContactFirstName'].'"><br><br>
		Contact Person Middle Initial: 
		<input type="text" id="ContactMiddleInitial" name="ContactMiddleInitial_submit" value="'.$_POST['ContactMiddleInitial'].'"><br><br>
		Contact Person Last Name: 
		<input type="text" id="ContactLastName" name="ContactLastName_submit" value="'.$_POST['ContactLastName'].'"><br><br>
		Contact Phone Number: 
		<input type="number" id="ContactNumber" name="ContactNumber_submit" value="'.$_POST['ContactNumber'].'"><br><br>
		Contact Email: 
		<input type="text" id="ContactEmail" name="ContactEmail_submit" value="'.$_POST['ContactEmail'].'"><br><br>
		Street Address: 
		<input type="text" id="Street" name="Street_submit" value="'.$_POST['Street'].'"><br><br>
		
		Province or Territory: 
		<select id="Province" name="Province_submit" onchange="'."post('/sa.php')".'">
		  <option value="AB" '.postSet("Province", "AB").'>AB
		  <option value="BC" '.postSet("Province", "BC").'>BC
		  <option value="MB" '.postSet("Province", "MB").'>MB
		  <option value="NB" '.postSet("Province", "NB").'>NB
		  <option value="NL" '.postSet("Province", "NL").'>NL
		  <option value="NS" '.postSet("Province", "NS").'>NS
		  <option value="NT" '.postSet("Province", "NT").'>NT
		  <option value="NU" '.postSet("Province", "NU").'>NU
		  <option value="ON" '.postSet("Province", "ON").'>ON
		  <option value="PE" '.postSet("Province", "PE").'>PE
		  <option value="QC" '.postSet("Province", "QC").'>QC
		  <option value="SK" '.postSet("Province", "SK").'>SK
		  <option value="YT" '.postSet("Province", "YT").'>YT
		</select>
		
		
		City: 
		<select id="City" name="City_submit">';
		
		//Load City Data
		$sql = 'SELECT City 
		FROM AddressData
		WHERE Province = "'.$_POST["Province"].'"
		ORDER BY City;';
		$result = mysqli_query($conn, $sql);
		
		echo (mysqli_error($conn));
		
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				echo '<option value="'.$row["City"].'" ';
				if ($row["City"] == $_POST["City"]){
					echo ' selected ';
				}
				echo '>'.$row["City"];
			}
		}else{
			echo '<option value="">'.(mysqli_error($conn));
		}
		echo '</select> <br><br>';
		
		//Postal Code
		echo '
		Postal Code: 
		<input type="text" id="PostalCode" name="PostalCode_submit" value="'.$_POST['PostalCode'].'"><br><br>
		
		';
	}
	
	//Missing CompanyID selection for existing Client
	if ($_POST["Mode"] == "newContract_existingClient"){
		echo '
		<br>
		<h4>Client Info</h4>
		<br>
		Client ID: 
		<input type="number" id="CompanyID" name="CompanyID_submit" placeholder="Required" value="'.$_POST['CompanyID'].'" required><br><br>
		';
	}
	
	if (($_POST["Mode"] == "newContract_newClient") or ($_POST["Mode"] == "newContract_existingClient")){
		
		echo '
		<h4>Contract Info</h4>
		
		<br>
		Contract ID: 
		<input type="number" id="ContractID" name="ContractID_submit" placeholder="Optional" value="'.$_POST['ContractID'].'"><br><br>
		
		Category: 
		<select id="Category" name="Category_submit">
		  <option value="Premium" '.postSet("Category", "Premium").'>Premium
		  <option value="Diamond" '.postSet("Category", "Diamond").'>Diamond
		  <option value="Gold" '.postSet("Category", "Gold").'>Gold
		  <option value="Silver" '.postSet("Category", "Silver").'>Silver
		</select><br><br>
		
		Service: 
		<select id="Service" name="Service_submit">
		  <option value="Cloud" '.postSet("Service", "Cloud").'>Cloud
		  <option value="OnPremises" '.postSet("Service", "OnPremises").'>OnPremises
		</select><br><br>
		
		Annual Contract Value (ACV): $
		<input type="number" id="ACV" name="ACV_submit" value="'.$_POST['ACV'].'" required><br><br>
		
		Initial Amount: $
		<input type="number" id="InitialAmount" name="InitialAmount_submit" value="'.$_POST['InitialAmount'].'" required><br><br>
		
		Line of Business: 
		<select id="LOB" name="LOB_submit" onchange="'."post('/sa.php')".'">
		';
		
		$sql = 'SELECT LOB
		FROM LineOfBusiness';
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				echo '<option value="'.$row["LOB"].'" ';
				if ($row["LOB"] == $_POST["LOB"]){
					echo ' selected ';
				}
				echo '>'.$row["LOB"];
			}
		}else{
			echo '<option value="">'.(mysqli_error($conn));
		}
		
		echo '</select> <br><br>';
		
		//Manager Selection
		echo 'Manager Assigned: 
		<select id="ManagerID" name="ManagerID_submit">';
		
		$sql = 'SELECT Employee.EmployeeID, FirstName, LastName
		FROM Specialized, Employee
		WHERE Employee.EmployeeID = Specialized. EmployeeID
		AND LOB = "'.$_POST["LOB"].'";';
		$result = mysqli_query($conn, $sql);
		
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				echo '<option value="'.$row["EmployeeID"].'" ';
				if ($row["EmployeeID"] == $_POST["ManagerID"]){
					echo ' selected ';
				}
				echo '>ID: '.$row["EmployeeID"].' '.$row["FirstName"].' '.$row["LastName"];
			}
		}else{
			echo '<option value="">No manager is available for this line of business currently';
		}
		
		echo '</select> <br><br>';
		
	}
	
	echo '
		<input type="submit" value="Submit">
		<button type="reset" value="Clear">Reset</button>
		</form>
	';
	
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
		<form action="/sa.php" method = "post">
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