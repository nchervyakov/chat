<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160201114029 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE orders ADD payment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_E52FFDEE4C3A3BB ON orders (payment_id)');
        $this->addSql('ALTER TABLE orders ADD status VARCHAR(32) DEFAULT \'new\' NOT NULL, ADD date_added DATETIME DEFAULT NULL, ADD date_updated DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE payments ADD date_added DATETIME DEFAULT NULL, ADD date_updated DATETIME DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE orders DROP status, DROP date_added, DROP date_updated');
        $this->addSql('ALTER TABLE payments DROP date_added, DROP date_updated');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE4C3A3BB');
        $this->addSql('DROP INDEX IDX_E52FFDEE4C3A3BB ON orders');
        $this->addSql('ALTER TABLE orders DROP payment_id');
    }
}
