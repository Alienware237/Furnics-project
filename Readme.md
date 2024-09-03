## Setting up the Project

- [ ] Remove deprecated class
- Comment the import of the class "SensioFrameworkExtraBundle" in config/bundles.php


## Running the Application

### Starting Docker Containers

Adjust the port number if necessary based on the Docker configuration.

To start the application using Docker:
```
docker-compose up -d
```
### Alternative: Symfony Server

If you prefer to use Symfony's built-in server:
```
symfony server:start

````
### Accessing the Application Container

To access the Docker container for the application:
```
docker exec -it furnics-project /bin/bash
```
### Installing Composer Dependencies

Install Composer dependencies manually if the vendor directory is not generated:
```
composer install
```

### Installing Vim (Optional)

To install Vim for editing php.ini or other files:
```
apt-get install vim
```

### Setting Directory Permissions

Ensure directory permissions and ownership are correctly set to allow image uploads by the admin.

Create the Directory:
```
mkdir -p /var/www/html/public/uploads
```

Set the Correct Permissions:
```
chown -R www-data:www-data /var/www/html/public/uploads
chmod -R 775 /var/www/html/public/uploads
```


### Database Setup

Create the database:
```
php bin/console doctrine:database:create
```

Create the database schema:
```
php bin/console doctrine:schema:create
```

### Inserting Sample Articles

Insert sample articles into the database:
```
php bin/console app:create-article
```
Insert sample admin into the database:
```
php bin/console app:create-admin-user
```

### Accessing the Application

#### As User
an error may occur. Ignore it and simply continue with the next step

- Open your web browser and navigate to: http://localhost:8094/index.php/index

- If an error occurs, it will probably be due to the fact that a user cannot have an open session if they are not registered.
So please start by registering by browsing: http://localhost:8094/index.php/user/register


#### As Administrator
- Open your web browser and navigate to: http://localhost:8094/index.php/article-form

You will be redirected to the login page:

```
Email: admin@example.com
Password: adminpassword
```

To verify if the authentication and role-based access control are working correctly in the main environment, you should set the APP_ENV environment variable to main in the file .env as following :

```
APP_ENV=main
```


## Running Tests

### PHPUnit Tests

Run PHPUnit tests to ensure the application functions correctly:
```
docker exec -it furnics-project php bin/phpunit
```