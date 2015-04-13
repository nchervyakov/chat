<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150413085040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversations ADD recalculated TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE conversation_intervals DROP last_message_date');
        $this->addSql('ALTER TABLE conversations CHANGE price price NUMERIC(10, 6) DEFAULT \'0\' NOT NULL, CHANGE model_earnings model_earnings NUMERIC(10, 6) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE conversation_intervals CHANGE price price NUMERIC(10, 6) DEFAULT \'0\' NOT NULL, CHANGE minute_rate minute_rate NUMERIC(10, 6) DEFAULT \'0\' NOT NULL, CHANGE model_share model_share NUMERIC(10, 6) DEFAULT \'0\' NOT NULL, CHANGE model_earnings model_earnings NUMERIC(10, 6) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE conversations CHANGE recalculated recalculated TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversations CHANGE recalculated recalculated TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE conversation_intervals CHANGE price price NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE minute_rate minute_rate NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE model_share model_share NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE model_earnings model_earnings NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE conversations CHANGE price price NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, CHANGE model_earnings model_earnings NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL');
        $this->addSql('ALTER TABLE conversation_intervals ADD last_message_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE conversations DROP recalculated');
    }
}
