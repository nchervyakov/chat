<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150410151931 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users ADD coins NUMERIC(10, 8) DEFAULT \'0\' NOT NULL');
        $this->addSql('DROP TABLE conversation_interval_packs');

        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96505A342E');
        $this->addSql('DROP INDEX IDX_DB021E96505A342E ON messages');
        $this->addSql('ALTER TABLE messages ADD previous_interval_id INT DEFAULT NULL, CHANGE interval_id following_interval_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E9615BC2872 FOREIGN KEY (following_interval_id) REFERENCES conversation_intervals (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96E2903F44 FOREIGN KEY (previous_interval_id) REFERENCES conversation_intervals (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DB021E9615BC2872 ON messages (following_interval_id)');
        $this->addSql('CREATE INDEX IDX_DB021E96E2903F44 ON messages (previous_interval_id)');

        $this->addSql('DELETE FROM `conversation_intervals`;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E9615BC2872');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96E2903F44');
        $this->addSql('DROP INDEX IDX_DB021E9615BC2872 ON messages');
        $this->addSql('DROP INDEX IDX_DB021E96E2903F44 ON messages');
        $this->addSql('ALTER TABLE messages ADD interval_id INT DEFAULT NULL, DROP following_interval_id, DROP previous_interval_id');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96505A342E FOREIGN KEY (interval_id) REFERENCES conversation_intervals (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DB021E96505A342E ON messages (interval_id)');

        $this->addSql('CREATE TABLE conversation_interval_packs (id INT AUTO_INCREMENT NOT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users DROP coins');
    }
}
