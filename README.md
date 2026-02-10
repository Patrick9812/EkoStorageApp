# 📦 EkoStorageApp - Warehouse Management System

A web application created in the Symfony framework, dedicated to warehouse management (receiving and issuing goods). The system supports multi-warehouse management and secure storage of personal data.

Project initial configuration data

1. Import the project into your project using the command ‘**git clone https://github.com/Patrick9812/EkoStorageApp.git && cd EkoStorageApp**’.
2. Paste the "**.env.local**" file sent in the email into the main project folder.
3. Add the doctrine/doctrine-fixtures-bundle --dev package using the command ‘**composer require doctrine/doctrine-fixtures-bundle --dev**’. It is not installed by default because it is not desired in a production environment.
4. Run the ‘**composer install**’ command.
5. In the console, run the command ‘**php bin/console tailwind:build**’. If you get a memory size error, follow these additional steps:
  * Delete the .exe file in the ‘**var/tailwind**’ folder.
  * Run the command ‘**php -d memory_limit=-1 bin/console tailwind:build**’
6. Create a database called ‘**eko_okna**’ in the MySQL administration panel.
7. Generate database tables using the console command ‘**php bin/console doctrine:schema:update --force**’.
8. Fill in the tables with the prepared data using the command ‘**php bin/console doctrine:fixtures:load**’, then select the ‘yes’ option.
9. Once you have finished adding data to the database, start the server with the command "**symfony server:start**".


**Login details after completing the above instructions:**

Administrative account:
login: admin123
hasło: zaq1@WSX

User account:
login: pracownik1
hasło: pracownik123
