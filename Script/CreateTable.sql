use scc353_1;

CREATE TABLE Employee(EmployeeID INT PRIMARY KEY AUTO_INCREMENT, HiredDate datetime not null, Insurance VARCHAR(255) not null, Department VARCHAR(255) not null, FirstName VARCHAR(255) not null, LastName VARCHAR(255) not null,
Role VARCHAR(255) not null,PWD VARCHAR(30) not null,ContractCat VARCHAR(255) default null);
ALTER TABLE Employee AUTO_INCREMENT=3000;

CREATE TABLE Company(CompanyID INT PRIMARY KEY AUTO_INCREMENT, CompanyName VARCHAR(255) not null, ContactFirstName VARCHAR(255), ContactLastName VARCHAR(255),ContactMiddleInitial VARCHAR(255),ContactNumber CHAR(10), ContactEmail VARCHAR(255),Street VARCHAR(255), City VARCHAR(255), Province CHAR(2),PostalCode CHAR(6), PWD VARCHAR(20) not null
check (Province = 'ON' or Province = 'QC' or
    Province = 'BC' or Province = 'AB' or 
    Province = 'MB' or Province = 'SK' or 
    Province = 'NS' or Province = 'NB' or 
    Province = 'NL' or Province = 'PE' or 
    Province = 'NT' or Province = 'YT' or 
    Province = 'NU' )
);
ALTER TABLE Company AUTO_INCREMENT=1000;

CREATE TABLE Contract(ContractID INT PRIMARY KEY AUTO_INCREMENT, Category VARCHAR(255)not null, Service VARCHAR(255) not null, ACV INT, StartDate datetime DEFAULT now(), FirstDelivered datetime default null, SecondDelivered datetime default null,ThirdDelivered datetime default null,FourthDelivered datetime default null, RecordDate datetime DEFAULT now(), SatisfactionScore INT default 0,
   CHECK (SatisfactionScore >= 0 AND SatisfactionScore < 11));
ALTER TABLE Contract AUTO_INCREMENT=2000;

CREATE TABLE LineOfBusiness(LOB VARCHAR(255) PRIMARY KEY);
CREATE TABLE Specialized(EmployeeID INT PRIMARY KEY, LOB VARCHAR(255) not null);
CREATE TABLE AssociatedWith(ContractID INT PRIMARY KEY, LOB VARCHAR(255) not null);
CREATE TABLE WorksOn(EmployeeID INT,ContractID INT,Task VARCHAR(255) not null, Hours INT default 0, primary key(EmployeeID, ContractID)); 
CREATE TABLE SignedBy(ContractID INT, CompanyID INT, PRIMARY KEY(ContractID, CompanyID));
CREATE TABLE ManagedBy(ManagerID INT,EmployeeID INT, PRIMARY KEY(ManagerID, EmployeeID));
CREATE TABLE ResponsibleFor(ManagerID INT, ContractID INT, PRIMARY KEY(ManagerID, ContractID));
