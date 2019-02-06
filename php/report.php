<!DOCTYPE html>
<html>
<body>

<h2>Report Page</h2>
<button id="btnfun1" name="btnfun1" onClick='location.href="?button1=1"'>Number of employees with Premium Employee plan with working hours less than
60 hrs/month</button><br><br>

<button id="btnfun2" name="btnfun2" onClick='location.href="?button2=1"'>Number of Premium contracts delivered in more than 10 business days having
more than 35 employees with “Silver Employee Plan” </button><br><br>


<button id="btnfun3" name="btnfun3" onClick='location.href="?button3=1"'>Make a report to compare the delivery schedule of "First deliverable" of all type
of contracts (Premium/Diamond etc.) in each month of year 2017. </button><br><br>

<button id="btnfun4" name="btnfun4" onClick='location.href="?button4=1"'>Give a list of clients who have the highest number of contracts in each line of business. </button><br><br>

<button id="btnfun5" name="btnfun5" onClick='location.href="?button5=1"'>Give the details of the contracts recorded within the last 10 days in all categories by sales
associate.  </button><br><br>

<button id="btnfun6" name="btnfun6" onClick='location.href="?button6=1"'>Fetch all the details of the employees from the “Quebec” province.   </button><br><br>

<button id="btnfun7" name="btnfun7" onClick='location.href="?button7=1"'>Give a list of all the contracts in the “Gold” category.   </button><br><br>

<button id="btnfun8" name="btnfun8" onClick='location.href="?button8=1"'>Generate one report for each category that indicates the clients whose contracts have the
highest satisfaction scores in that category, grouped by the cities of clients.   </button><br><br>

<button id="btnfun9" name="btnfun9" onClick='location.href="?button9=1"'>Delivery Schedules Comparison</button><br><br>

<button id="btnfun10" name="btnfun10" onClick='location.href="?button10=1"'>Delivery Schedules analysis</button><br><br>
</body>


<?php

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

function fun1(){
	
	 $sql = "SELECT * FROM scc353_1.Contract";
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);
}

?>
<?php
    if($_GET['button1'])
	{
	 $sql = "select count( distinct Employee.EmployeeID)  as NumberOfEmployees from Employee, (select EmployeeID,sum(Hours) AS Hours from WorksOn group by EmployeeID) AS M
     Where Employee. EmployeeID= M.EmployeeID
     And Insurance = 'Premium' and
    ( M.Hours/((datediff(now(), Employee.HiredDate))/30)) <60;

";
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}
	 
    if($_GET['button2']){
	 $sql = "select count(ContractID) as NumberOfContracts from( select Contract.ContractID   from Employee, WorksOn, Contract
            WHERE Employee.Insurance = 'Silver' and Employee.EmployeeID = WorksOn.EmployeeID
            And WorksOn.ContractID = Contract.ContractID and Contract.Category='Premium'
            And datediff(Contract.ThirdDelivered, Contract.StartDate )>10
            GROUP BY Contract.ContractID
            HAVING count(WorksOn.EmployeeID) >35) as m;

";
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}

	 if($_GET['button3']){
	 $sql = "select Contract.ContractID, CompanyName, DATE_ADD(StartDate, INTERVAL FirstDelivery DAY) as ScheduledFirstDelivery, Category, month(FirstDelivered) as        
            Month 
            From Contract, SignedBy, Company, DeliverySchedule
            Where Contract.ContractID = SignedBy.ContractID and 
            SignedBy.CompanyID = Company.CompanyID and
            Year(FirstDelivered) = 2017 and
            Contract.Category = DeliverySchedule.Type
            Order by ScheduledFirstDelivery;

";
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}

     if($_GET['button4']){
	 $sql = "select LOB,CompanyName, MAX(NumberOfContracts) AS NumberOfContracts FROM (Select LOB, CompanyName, count(AssociatedWith.ContractID) as NumberOfContracts from AssociatedWith, SignedBy,   Company
             Where AssociatedWith.ContractID=SignedBy. ContractID and SignedBy.CompanyID = Company.CompanyID   
             GROUP BY LOB, CompanyName) AS M

             GROUP BY LOB;

";
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}
	 
	 if($_GET['button5']){
	 $sql = "select ContractID, Category, Service, ACV, StartDate, FirstDelivered, SecondDelivered, ThirdDelivered, FourthDelivered  ,SatisfactionScore from Contract
             WHERE( DAYOFMONTH(NOW( ))- DAYOFMONTH(Contract.StartDate))<=10
             And MONTH(NOW()) = MONTH(Contract.StartDate)
             AND YEAR(NOW()) = YEAR(Contract.StartDate);


";
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}

	  if($_GET['button6']){
	 $sql = "select FirstName, LastName, Department, Role, Insurance, Province from Employee
             WHERE province = 'Quebec';


";
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}
	 
	 if($_GET['button7']){
	 $sql = "select ContractID, Category, Service, ACV, StartDate, FirstDelivered, SecondDelivered, ThirdDelivered, FourthDelivered  ,SatisfactionScore from Contract where Category ='Gold';


";
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}
	 
	  if($_GET['button8']){
	 $sql = "select City, Category, CompanyName, MAX(SatisfactionScore) as HighestSatisfactionScore from Contract, SignedBy, Company 
            WHERE Contract.ContractID = SignedBy.ContractID and SignedBy.CompanyID = Company.CompanyID
            GROUP BY City, Category;
     
";
     $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}
    if($_GET['button9']){
	 $sql = 'select DeliveySchedule.Category, DeliveySchedule.CompanyName, DATE_FORMAT(StartDate,"%Y-%m-%d") as StartDate,
	 DATE_FORMAT(ScheduledFirstDelivery,"%Y-%m-%d") as ScheduledFirstDelivery, DATE_FORMAT(FirstDelivered,"%Y-%m-%d") as FirstDelivered,FirstDeliveryStatus,
     DATE_FORMAT(ScheduledSecondDelivery,"%Y-%m-%d") as ScheduledSecondDelivery,DATE_FORMAT(SecondDelivered,"%Y-%m-%d") as SecondDelivered,SecondDeliveryStatus,
	 DATE_FORMAT(ScheduledThirdDelivery,"%Y-%m-%d") as ScheduledThirdDelivery,DATE_FORMAT(ThirdDelivered,"%Y-%m-%d") as ThirdDelivered,ThirdDeliveryStatus,
     DATE_FORMAT(ScheduledFourthDelivery,"%Y-%m-%d") as ScheduledFourthDelivery,DATE_FORMAT(FourthDelivered,"%Y-%m-%d") as FourthDelivered,FourthDeliveryStatus from DeliveySchedule,
 
       (select Category,CompanyName, DATE_ADD(StartDate, INTERVAL FirstDelivery DAY) as ScheduledFirstDelivery, 
       DATE_ADD(StartDate, INTERVAL SecondDelivery DAY) as ScheduledSecondDelivery,
	   DATE_ADD(StartDate, INTERVAL ThirdDelivery DAY) as ScheduledThirdDelivery,
       DATE_ADD(StartDate, INTERVAL FourthDelivery DAY) as ScheduledFourthDelivery
       From Contract, SignedBy, Company, DeliverySchedule
            Where Contract.ContractID = SignedBy.ContractID and 
            SignedBy.CompanyID = Company.CompanyID and
            Contract.Category = DeliverySchedule.Type
            GROUP BY Category, CompanyName) as m
            where DeliveySchedule.CompanyName = m.CompanyName and DeliveySchedule.Category = m.Category;
            

';
	 $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}
	 
	 if($_GET['button10']){
	 $sql = "
select 'NeverDelayed_Premium' as Category, count(CompanyName) as Number from DeliveySchedule where Category = 'Premium' 
and FirstDeliveryStatus	='on-time' and SecondDeliveryStatus='on-time' and  ThirdDeliveryStatus='on-time' 
union all
select 'OnTimeButEverDelayed_Premium' as Category , count(CompanyName) as Number from DeliveySchedule where Category = 'Premium' 
and (FirstDeliveryStatus	='Delayed' or SecondDeliveryStatus='Delayed') and  ThirdDeliveryStatus='on-time' 
union all
select 'TotallyDelayed_Premium' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Premium' 
and FirstDeliveryStatus	='Delayed' and SecondDeliveryStatus='Delayed' and  ThirdDeliveryStatus='Delayed' 
union all
select 'NeverDelayed_Diamond' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Dimond' 
and FirstDeliveryStatus	='on-time' and SecondDeliveryStatus='on-time' and  ThirdDeliveryStatus='on-time' 
union all
select 'OnTimeButEverDelayed_Diamond' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Diamond' 
and (FirstDeliveryStatus	='Delayed' or SecondDeliveryStatus='Delayed') and  ThirdDeliveryStatus='on-time' 
union all
select 'TotallyDelayed_Diamond' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Diamond' 
and FirstDeliveryStatus	='Delayed' and SecondDeliveryStatus='Delayed' and  ThirdDeliveryStatus='Delayed' 
union all
select 'NeverDelayed_Gold' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Gold' 
and FirstDeliveryStatus	='on-time' and SecondDeliveryStatus='on-time' and  ThirdDeliveryStatus='on-time' 
union all
select'OnTimeButEverDelayed_Gold' as Category, count(CompanyName) as Number from DeliveySchedule where Category = 'Gold' 
and (FirstDeliveryStatus	='Delayed' or SecondDeliveryStatus='Delayed') and  ThirdDeliveryStatus='on-time' 
union all
select 'TotallyDelayed_Gold' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Gold' 
and FirstDeliveryStatus	='Delayed' and SecondDeliveryStatus='Delayed' and  ThirdDeliveryStatus='Delayed' 
union all
select 'NeverDelayed_Silver' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Silver' 
and FirstDeliveryStatus	='on-time' and SecondDeliveryStatus='on-time' and  ThirdDeliveryStatus='on-time' and FourthDeliveryStatus = 'on-time'
union all
select 'OnTimeButEverDelayed_Silver' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Silver' 
and (FirstDeliveryStatus	='Delayed' or SecondDeliveryStatus='Delayed' or  ThirdDeliveryStatus='Delayed') and FourthDeliveryStatus = 'on-time'
union all
select 'TotallyDelayed_Silver' as Category,count(CompanyName) as Number from DeliveySchedule where Category = 'Silver' 
and FirstDeliveryStatus	='Delayed' and SecondDeliveryStatus='Delayed' and  ThirdDeliveryStatus='Delayed' and FourthDeliveryStatus = 'Delayed'
union all
select 'OnTimeIntotal' as Category ,count(CompanyName) as Number from DeliveySchedule where ThirdDeliveryStatus='on-time' and Category != 'Silver' or FourthDeliveryStatus = 'on-time'
union all 
select 'DelayedIntotal' as Category ,count(CompanyName) as Number from DeliveySchedule where ThirdDeliveryStatus='Delayed' and Category != 'Silver' or FourthDeliveryStatus = 'Delayed';



     
";
     $row_array = mysqli_query($conn, $sql);
	 display_data($row_array);}
	 
?>
</html>