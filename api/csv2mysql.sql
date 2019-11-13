LOAD DATA INFILE 'C:\\ProgramData\\MySQL\\MySQL Server 5.7\\Uploads\\nba-2018.csv'
INTO TABLE nba_2018
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 ROWS
(Round, @var1, Location, Home_Team, Away_Team, Result)
SET Date = STR_TO_DATE(@var1,'%d/%m/%Y %H:%i');
