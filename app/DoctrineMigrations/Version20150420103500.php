<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150420103500 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversations ADD client_unseen_messages INT DEFAULT 0 NOT NULL, ADD model_unseen_messages INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE messages ADD is_seen_by_client TINYINT(1) DEFAULT \'0\' NOT NULL, ADD is_seen_by_model TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('UPDATE `messages` SET `is_seen_by_client` = \'1\', `is_seen_by_model` = \'1\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversations DROP client_unseen_messages, DROP model_unseen_messages');
        $this->addSql('ALTER TABLE messages DROP is_seen_by_client, DROP is_seen_by_model');
    }
}
