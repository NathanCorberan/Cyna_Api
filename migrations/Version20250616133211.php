<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616133211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE carousel (id INT AUTO_INCREMENT NOT NULL, image_link LONGTEXT NOT NULL, panel_order SMALLINT NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_PANEL_ORDER (panel_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE carousel_langage (id INT AUTO_INCREMENT NOT NULL, carousel_id INT NOT NULL, code VARCHAR(2) NOT NULL, title VARCHAR(50) DEFAULT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_AEFF3F08C1CE5B98 (carousel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, creation_date VARCHAR(50) NOT NULL, category_order INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category_image (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(50) DEFAULT NULL, image_link LONGTEXT NOT NULL, INDEX IDX_2D0E4B1612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category_language (id INT AUTO_INCREMENT NOT NULL, category_id_id INT DEFAULT NULL, code VARCHAR(5) NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_63A1F5CE9777D11E (category_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE homepage (id INT AUTO_INCREMENT NOT NULL, image_link LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE homepage_langage (id INT AUTO_INCREMENT NOT NULL, homepage_id INT NOT NULL, title VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_D5418E00571EDDA (homepage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, order_date VARCHAR(50) NOT NULL, total_amount NUMERIC(10, 2) NOT NULL, webhook_processed TINYINT(1) NOT NULL, status ENUM('cart', 'payed'), cart_token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_F5299398DDCC33 (cart_token), INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, product_id INT NOT NULL, subscription_type_id INT DEFAULT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 2) NOT NULL, total_price NUMERIC(10, 2) NOT NULL, INDEX IDX_52EA1F098D9F6D38 (order_id), INDEX IDX_52EA1F094584665A (product_id), INDEX IDX_52EA1F09B6596C08 (subscription_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, available_stock INT DEFAULT NULL, creation_date VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, stripe_product_id VARCHAR(255) DEFAULT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_image (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, image_link LONGTEXT NOT NULL, name VARCHAR(50) NOT NULL, INDEX IDX_64617F034584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_langage (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, code VARCHAR(2) NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_1D89F1964584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, subscription_type_id INT NOT NULL, start_date VARCHAR(50) NOT NULL, end_date VARCHAR(50) DEFAULT NULL, status VARCHAR(50) NOT NULL, INDEX IDX_A3C664D3A76ED395 (user_id), INDEX IDX_A3C664D3B6596C08 (subscription_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE subscription_type (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, type VARCHAR(100) NOT NULL, price NUMERIC(15, 2) NOT NULL, stripe_price_id VARCHAR(255) DEFAULT NULL, INDEX IDX_BBE247374584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(50) NOT NULL, is_activate TINYINT(1) NOT NULL, stripe_customer_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, address VARCHAR(100) NOT NULL, city VARCHAR(50) NOT NULL, country VARCHAR(50) NOT NULL, INDEX IDX_5543718BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE carousel_langage ADD CONSTRAINT FK_AEFF3F08C1CE5B98 FOREIGN KEY (carousel_id) REFERENCES carousel (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_image ADD CONSTRAINT FK_2D0E4B1612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_language ADD CONSTRAINT FK_63A1F5CE9777D11E FOREIGN KEY (category_id_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE homepage_langage ADD CONSTRAINT FK_D5418E00571EDDA FOREIGN KEY (homepage_id) REFERENCES homepage (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F098D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F094584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09B6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_langage ADD CONSTRAINT FK_1D89F1964584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3B6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription_type ADD CONSTRAINT FK_BBE247374584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_address ADD CONSTRAINT FK_5543718BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE carousel_langage DROP FOREIGN KEY FK_AEFF3F08C1CE5B98
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_image DROP FOREIGN KEY FK_2D0E4B1612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_language DROP FOREIGN KEY FK_63A1F5CE9777D11E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE homepage_langage DROP FOREIGN KEY FK_D5418E00571EDDA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F098D9F6D38
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F094584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09B6596C08
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_langage DROP FOREIGN KEY FK_1D89F1964584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3B6596C08
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE subscription_type DROP FOREIGN KEY FK_BBE247374584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_address DROP FOREIGN KEY FK_5543718BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE carousel
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE carousel_langage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category_image
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category_language
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE homepage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE homepage_langage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `order`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE order_item
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_image
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_langage
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refresh_tokens
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE subscription
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE subscription_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_address
        SQL);
    }
}
