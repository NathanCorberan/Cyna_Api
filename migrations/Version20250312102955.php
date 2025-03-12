<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250312102955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carousel (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, image_link CLOB NOT NULL, panel_order SMALLINT NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_PANEL_ORDER ON carousel (panel_order)');
        $this->addSql('CREATE TABLE carousel_langage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, carousel_id INTEGER NOT NULL, code VARCHAR(2) NOT NULL, title VARCHAR(50) DEFAULT NULL, description CLOB DEFAULT NULL, CONSTRAINT FK_AEFF3F08C1CE5B98 FOREIGN KEY (carousel_id) REFERENCES carousel (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_AEFF3F08C1CE5B98 ON carousel_langage (carousel_id)');
        $this->addSql('CREATE TABLE homepage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, image_link CLOB DEFAULT NULL)');
        $this->addSql('CREATE TABLE homepage_langage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, homepage_id INTEGER NOT NULL, title VARCHAR(50) NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_D5418E00571EDDA FOREIGN KEY (homepage_id) REFERENCES homepage (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D5418E00571EDDA ON homepage_langage (homepage_id)');
        $this->addSql('CREATE TABLE order_product (order_id INTEGER NOT NULL, product_id INTEGER NOT NULL, PRIMARY KEY(order_id, product_id), CONSTRAINT FK_2530ADE68D9F6D38 FOREIGN KEY (order_id) REFERENCES "order" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2530ADE64584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2530ADE68D9F6D38 ON order_product (order_id)');
        $this->addSql('CREATE INDEX IDX_2530ADE64584665A ON order_product (product_id)');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category_id INTEGER NOT NULL, available_stock INTEGER DEFAULT NULL, creation_date VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES "category" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        $this->addSql('CREATE TABLE product_image (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, image_link CLOB NOT NULL, name VARCHAR(50) NOT NULL, CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_64617F034584665A ON product_image (product_id)');
        $this->addSql('CREATE TABLE product_langage (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, code VARCHAR(2) NOT NULL, name VARCHAR(50) NOT NULL, description CLOB NOT NULL, CONSTRAINT FK_1D89F1964584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1D89F1964584665A ON product_langage (product_id)');
        $this->addSql('CREATE TABLE subscription (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, subscription_type_id INTEGER NOT NULL, start_date VARCHAR(50) NOT NULL, end_date VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A3C664D3B6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A3C664D3A76ED395 ON subscription (user_id)');
        $this->addSql('CREATE INDEX IDX_A3C664D3B6596C08 ON subscription (subscription_type_id)');
        $this->addSql('CREATE TABLE subscription_type (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, type VARCHAR(100) NOT NULL, price NUMERIC(15, 2) NOT NULL, CONSTRAINT FK_BBE247374584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_BBE247374584665A ON subscription_type (product_id)');
        $this->addSql('CREATE TABLE user_address (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, address VARCHAR(100) NOT NULL, city VARCHAR(50) NOT NULL, country VARCHAR(50) NOT NULL, CONSTRAINT FK_5543718BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5543718BA76ED395 ON user_address (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE carousel');
        $this->addSql('DROP TABLE carousel_langage');
        $this->addSql('DROP TABLE homepage');
        $this->addSql('DROP TABLE homepage_langage');
        $this->addSql('DROP TABLE order_product');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_image');
        $this->addSql('DROP TABLE product_langage');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE subscription_type');
        $this->addSql('DROP TABLE user_address');
    }
}
