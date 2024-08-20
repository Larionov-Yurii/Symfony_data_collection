Brief description about the project and how to start it

1. It is necessary to clone the project using the command: git clone https://github.com/Larionov-Yurii/Symfony_data_collection.git
2. Then go to the directory with the project (Symfony_data_collection) and run the command (composer install)
3. Configure the (.env) file
4. Run the command to create a database: php bin/console doctrine:database:create
5. Run migrations: php bin/console doctrine:migrations:migrate
6. To load data, we need to use the command (php bin/console app:update-exchange-rates) and also remember that in the file (services.yaml) we can configure from which resource we want to get data
7. To check the conversion of the amount, we can use Postman with the route (http://localhost:8000/api/convert?amount=100&from=USD&to=GBP)
8. Also, to check the validity of the data, we can use the same route but with other parameters (http://localhost:8000/api/convert?amount=100&from=USD&to=XYZ)
9. To check the tests, we need to configure (.env.test and phpunit.xml.dist) files
10. We need to create a separate database: php bin/console doctrine:database:create --env=test
11. Run migrations there: php bin/console doctrine:migrations:migrate --env=test
12. And we can check all tests at once using the command (./vendor/bin/phpunit) or each test separately using the commands
13. php bin/phpunit --testdox tests/Integration/Command/UpdateExchangeRatesCommandTest.php
14. php bin/phpunit --testdox tests/Unit/Controller/CurrencyControllerTest.php
15. php bin/phpunit --testdox tests/Unit/Service/ExchangeRateServiceTest.php
