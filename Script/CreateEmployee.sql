## Employee(EmployeeID INT PRIMARY KEY AUTO_INCREMENT, HiredDate datetime not null, Insurance VARCHAR(255) not null, Department VARCHAR(255) not null, FirstName VARCHAR(255) not null, LastName VARCHAR(255) not null,
## Role VARCHAR(255) not null,PWD VARCHAR(30) not null,ContractCat VARCHAR(255) default null);
## ALTER TABLE Employee AUTO_INCREMENT=3000;
use scc353_1;
insert into Employee(HiredDate,Insurance,Department, FirstName, LastName,Role ,PWD,ContractCat)
values('2011-07-30','Premium','Development','Harry','Potter','SalesAssociate','e00001','Premium'),
('2018-01-02','Sliver','Development','Ron','Weasley','SalesAssociate','e79890','Premium'),
('2017-09-08','Normal','Development','Hermione','Granger','SalesAssociate','e73942',''),
('2016-03-04','Premium','QA','Draco','Malfoy','Employee','e78936','Diamond'),
('2017-09-20','Sliver','QA','Parvati','Patil','Employee','e79283','Diamond'),
('2016-03-14','Normal','Development','Bill','Weasley','Employee','e78362','Diamond'),
('2015-10-23','Premium','Development','Fred','Weasley','Employee','e45265','Gold'),
('2014-06-19','Sliver','Development','Cho','Zhang','Employee','e56723','Gold'),
('2015-06-11','Normal','QA','Fleur','Delacour','Employee','e68243','Silver'),
('2017-11-14','Premium','Development','Ginny','Potter','Employee','e72323',''),
('2016-09-20','Sliver','Development','Albus','Dumbledore','Manager','e23586',''),
('2018-05-09','Normal','Development','Minerva','Mcgonagall','Manager','e82642',''),
('2014-12-30','Premium','QA','Severus','Snape','Manager','e27465','Silver'),
('2013-09-08','Sliver','Development','Gibleroy','Lockhart','Employee','e24663','Gold'),
('2012-03-01','Normal','QA','Cornelius','Fudge','Employee','e62846','Sliver'),
('2016-12-25','Premium','QA','Rubeus','Hagrid','Employee','e26354','Gold'),
('2015-07-15','Sliver','Networking','Peter','Pettigrew','Employee','e23423','Silver'),
('2018-04-21','Normal','Development','Sirius','Black','Admin','e28479',''),
('2014-09-30','Premium','Development','Oliver','Wood','Admin','e78723','Silver'),
('2015-03-14','Sliver','QA','Neville','Longbottom','Admin','e78253',''),
('2017-09-14','Sliver','QA','Hanna','Simth','Admin','e78973','');
