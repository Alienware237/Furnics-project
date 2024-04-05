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

- [ ] Install required Package 
- composer update
- composer install
- php bin/console 


## Running the Application

- symfony server:start