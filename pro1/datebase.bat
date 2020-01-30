@echo off  
set dd=%date:~8,2%
set mm=%date:~5,2%
set yy=%date:~0,4%
set Tss=%TIME:~6,2%
set Tmm=%TIME:~3,2%
set Thh=%TIME:~0,2%
set Thh=%Thh: =0%
set "Ymd=%Thh%.%Tmm%.%Tss%"
set "Dates=%date:~0,4%%date:~5,2%%date:~8,2%"  
md "E:/hmk/%Dates%"
E:/UPUPW_AP5.4/MariaDB/bin/mysqldump --opt -u honmen572 --password=t4Qp0UC3WB2kTaLh h_b> E:/hmk/%Dates%/%Ymd%.sql  
@echo on