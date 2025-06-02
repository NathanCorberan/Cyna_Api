<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250602204253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category_language (id INT AUTO_INCREMENT NOT NULL, category_id_id INT DEFAULT NULL, code VARCHAR(5) NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_63A1F5CE9777D11E (category_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE category_language ADD CONSTRAINT FK_63A1F5CE9777D11E FOREIGN KEY (category_id_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE status status ENUM('cart', 'payed')
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE category_language DROP FOREIGN KEY FK_63A1F5CE9777D11E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category_language
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE status status VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
