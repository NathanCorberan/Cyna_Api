<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250528161014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE status status ENUM('cart', 'payed')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD subscription_type_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09B6596C08 FOREIGN KEY (subscription_type_id) REFERENCES subscription_type (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_52EA1F09B6596C08 ON order_item (subscription_type_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09B6596C08
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_52EA1F09B6596C08 ON order_item
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE order_item DROP subscription_type_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `order` CHANGE status status VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
