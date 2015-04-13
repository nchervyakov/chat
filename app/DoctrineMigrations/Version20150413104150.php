<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150413104150 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversations ADD client_agree_to_pay TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('CREATE TABLE coin_transactions (id INT AUTO_INCREMENT NOT NULL, source_id INT DEFAULT NULL, target_id INT DEFAULT NULL, coins_amount NUMERIC(10, 6) DEFAULT \'0\' NOT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, discriminator VARCHAR(32) NOT NULL, INDEX IDX_92B6A1C8953C1C61 (source_id), INDEX IDX_92B6A1C8158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE coin_transactions ADD CONSTRAINT FK_92B6A1C8953C1C61 FOREIGN KEY (source_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE coin_transactions ADD CONSTRAINT FK_92B6A1C8158E0B66 FOREIGN KEY (target_id) REFERENCES users (id) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE coin_transactions');
        $this->addSql('ALTER TABLE conversations DROP client_agree_to_pay');
    }
}
