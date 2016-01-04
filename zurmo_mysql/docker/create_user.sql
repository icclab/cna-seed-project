create user 'zurmo' identified by 'ZURMO_PASSWORD';
grant all privileges on *.* to zurmo;

create user 'zurmo_test' identified by 'zurmo_test';
grant all privileges on *.* to zurmo_test;

create user 'zurmo_temp' identified by 'zurmo_temp';
grant all privileges on *.* to zurmo_temp;
