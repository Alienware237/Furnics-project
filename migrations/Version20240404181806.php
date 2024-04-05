<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240404181806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //php bin/console doctrine:migrations:execute --up "DoctrineMigrations\Version20240404181806"
        $this->addSql('CREATE TABLE IF NOT EXISTS `article` (article_id INT AUTO_INCREMENT NOT NULL, article_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, article_price DECIMAL(10, 2) NOT NULL, number_in_stock INT NOT NULL, article_category VARCHAR(255) NOT NULL, article_images LONGTEXT DEFAULT NULL, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(article_id))');
        $this->addSql('CREATE TABLE IF NOT EXISTS `user` (user_id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(100) DEFAULT NULL, first_name VARCHAR(100) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, salutation VARCHAR(6) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, house_number INT DEFAULT NULL, zip_code VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, role INT NOT NULL, cookie VARCHAR(255) DEFAULT NULL, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(user_id))');
        $this->addSql('CREATE TABLE IF NOT EXISTS review (review_id INT AUTO_INCREMENT NOT NULL, review_text VARCHAR(255) NOT NULL, rating INT, user_id INT, article_id INT, user_data VARCHAR(255) NOT NULL, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(review_id), FOREIGN KEY(user_id) REFERENCES user(user_id), FOREIGN KEY(article_id) REFERENCES article(article_id) ON UPDATE CASCADE ON DELETE CASCADE)');
        $this->addSql('CREATE TABLE IF NOT EXISTS `order` (`order_id` INT AUTO_INCREMENT NOT NULL PRIMARY KEY, total_amount FLOAT, user_id INT, created_at DATETIME, updated_at DATETIME, FOREIGN KEY(user_id) REFERENCES user(user_id) ON UPDATE CASCADE ON DELETE CASCADE)');
        $this->addSql('CREATE TABLE IF NOT EXISTS `order_item` (`order_item_id` INT AUTO_INCREMENT NOT NULL PRIMARY KEY, `order_id` INT, article_id INT, quantity INT, unit_price FLOAT, created_at DATETIME, updated_at DATETIME, FOREIGN KEY(article_id) REFERENCES article(article_id), FOREIGN KEY(`order_id`) REFERENCES `order`(`order_id`) ON UPDATE CASCADE ON DELETE CASCADE)');
        $this->addSql('CREATE TABLE IF NOT EXISTS `comment` (comment_id INT AUTO_INCREMENT NOT NULL, user_id INT, article_id INT, comment_text VARCHAR(255), user_data VARCHAR(255), created_at DATETIME, updated_at DATETIME, PRIMARY KEY(comment_id), FOREIGN KEY(user_id) REFERENCES user(user_id), FOREIGN KEY(article_id) REFERENCES article(article_id) ON UPDATE CASCADE ON DELETE CASCADE)');
        $this->addSql('CREATE TABLE IF NOT EXISTS `cart` (cart_id INT AUTO_INCREMENT NOT NULL, user_id INT, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(cart_id), FOREIGN KEY(user_id) REFERENCES user(user_id) ON UPDATE CASCADE ON DELETE CASCADE)');
        $this->addSql('CREATE TABLE IF NOT EXISTS `cart_item` (cart_item_id INT AUTO_INCREMENT NOT NULL, quantity INT, details_of_choise VARCHAR(255), cart_id INT, article_id INT, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(cart_item_id), FOREIGN KEY(article_id) REFERENCES article(article_id), FOREIGN KEY(cart_id) REFERENCES cart(cart_id) ON UPDATE CASCADE ON DELETE CASCADE)');
        $this->addSql('CREATE TABLE IF NOT EXISTS `payment` (payment_id INT AUTO_INCREMENT NOT NULL, amount INT, payment_art VARCHAR(255), user_id INT, `orderI_id` INT, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(payment_id), FOREIGN KEY(user_id) REFERENCES user(user_id), FOREIGN KEY(`order_id`) REFERENCES `order`(`order_id`))');
        // Add SQL commands to create other tables here

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        //php bin/console doctrine:migrations:execute --down "DoctrineMigrations\Version20240404181806"
        $this->addSql('DROP TABLE `order_item`');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE `cart_item`');
        $this->addSql('DROP TABLE `cart`');
        $this->addSql('DROP TABLE `review`');
        $this->addSql('DROP TABLE `article`');
        $this->addSql('DROP TABLE `user`');
    }
}
