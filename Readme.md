## Instalation

- [ ] Step 1. Install Symfony CLI: 
- curl -sS https://get.symfony.com/cli/installer | bash


## Setting up the Project

- chmod 755 docker/bin/console
- chmod 755 docker/bin/composer
- chmod 755 docker/bin/phpcbf
- chmod 755 docker/bin/phpcs
- chmod 755 docker/bin/test

- [ ] Remove deprecated class
- Comment the import of the class "SensioFrameworkExtraBundle" in config/bundles.php


## Running the Application

- docker compose up -d
<!-- oder -->
- symfony server:start
<!-- Go in the App container with the  Command: -->
- docker exec -it furnics-project /bin/bash
<!-- Install Composer manually because Composer have not genarate the vendor file by starting the container with Dockerfile -->
- composer install
<!-- Install vim to edit the php-ini file -->
- apt-get install vim

<!-- Damit der Adminn Bilder uploaden kann ist es notwendig Directory Permissions und Directory Ownership zu geben -->

<!-- create the database schema by running -->
- php bin/console doctrine:schema:create
<!-- Open the browser on following link -->
- http://localhost:8094/index.php/index