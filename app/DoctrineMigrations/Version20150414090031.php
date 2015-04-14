<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150414090031 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversations ADD stale_payment_info TINYINT(1) DEFAULT \'1\' NOT NULL');

        $this->addSql('DELETE FROM `coin_transactions`');
        $this->addSql('DELETE FROM `conversation_intervals`');
        $this->addSql('UPDATE `conversations` SET `recalculated` = \'0\', `stale_payment_info` = \'0\'');

        $this->addSql('ALTER TABLE conversation_intervals ADD startMessage_id INT DEFAULT NULL, ADD endMessage_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conversation_intervals ADD CONSTRAINT FK_AF64C079973CA22 FOREIGN KEY (startMessage_id) REFERENCES messages (id)');
        $this->addSql('ALTER TABLE conversation_intervals ADD CONSTRAINT FK_AF64C07C26B0DEC FOREIGN KEY (endMessage_id) REFERENCES messages (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AF64C079973CA22 ON conversation_intervals (startMessage_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AF64C07C26B0DEC ON conversation_intervals (endMessage_id)');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96E2903F44');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E9615BC2872');
        $this->addSql('DROP INDEX IDX_DB021E9615BC2872 ON messages');
        $this->addSql('DROP INDEX IDX_DB021E96E2903F44 ON messages');
        $this->addSql('ALTER TABLE messages DROP previous_interval_id, DROP following_interval_id');

        $this->addSql('ALTER TABLE coin_transactions CHANGE coins_amount coins_amount NUMERIC(18, 8) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE conversations CHANGE price price NUMERIC(18, 8) DEFAULT \'0\' NOT NULL, CHANGE model_earnings model_earnings NUMERIC(18, 8) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE conversation_intervals CHANGE price price NUMERIC(18, 8) DEFAULT \'0\' NOT NULL, CHANGE minute_rate minute_rate NUMERIC(10, 4) DEFAULT \'0\' NOT NULL, CHANGE model_share model_share NUMERIC(10, 4) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE coins coins NUMERIC(18, 8) DEFAULT \'0\' NOT NULL');

        $this->addSql('ALTER TABLE conversation_intervals DROP FOREIGN KEY FK_AF64C079973CA22');
        $this->addSql('ALTER TABLE conversation_intervals DROP FOREIGN KEY FK_AF64C07C26B0DEC');
        $this->addSql('DROP INDEX UNIQ_AF64C079973CA22 ON conversation_intervals');
        $this->addSql('DROP INDEX UNIQ_AF64C07C26B0DEC ON conversation_intervals');
        $this->addSql('ALTER TABLE conversation_intervals ADD start_message INT DEFAULT NULL, ADD end_message INT DEFAULT NULL, DROP startMessage_id, DROP endMessage_id');
        $this->addSql('ALTER TABLE conversation_intervals ADD CONSTRAINT FK_AF64C0746786CAC FOREIGN KEY (start_message) REFERENCES messages (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE conversation_intervals ADD CONSTRAINT FK_AF64C0723726196 FOREIGN KEY (end_message) REFERENCES messages (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AF64C0746786CAC ON conversation_intervals (start_message)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AF64C0723726196 ON conversation_intervals (end_message)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE coin_transactions CHANGE coins_amount coins_amount NUMERIC(10, 6) DEFAULT \'0.000000\' NOT NULL');
        $this->addSql('ALTER TABLE conversation_intervals CHANGE price price NUMERIC(10, 6) DEFAULT \'0.000000\' NOT NULL, CHANGE minute_rate minute_rate NUMERIC(10, 6) DEFAULT \'0.000000\' NOT NULL, CHANGE model_share model_share NUMERIC(10, 6) DEFAULT \'0.000000\' NOT NULL');
        $this->addSql('ALTER TABLE conversations CHANGE price price NUMERIC(10, 6) DEFAULT \'0.000000\' NOT NULL, CHANGE model_earnings model_earnings NUMERIC(10, 6) DEFAULT \'0.000000\' NOT NULL');
        $this->addSql('ALTER TABLE users CHANGE coins coins NUMERIC(10, 8) DEFAULT \'0.00000000\' NOT NULL');

        $this->addSql('DELETE FROM `coin_transactions`');
        $this->addSql('DELETE FROM `conversation_intervals`');
        $this->addSql('UPDATE `conversations` SET `recalculated` = \'0\', `stale_payment_info` = \'0\'');

        $this->addSql('ALTER TABLE conversation_intervals DROP FOREIGN KEY FK_AF64C0746786CAC');
        $this->addSql('ALTER TABLE conversation_intervals DROP FOREIGN KEY FK_AF64C0723726196');
        $this->addSql('DROP INDEX UNIQ_AF64C0746786CAC ON conversation_intervals');
        $this->addSql('DROP INDEX UNIQ_AF64C0723726196 ON conversation_intervals');
        $this->addSql('ALTER TABLE conversation_intervals ADD startMessage_id INT DEFAULT NULL, ADD endMessage_id INT DEFAULT NULL, DROP start_message, DROP end_message');
        $this->addSql('ALTER TABLE conversation_intervals ADD CONSTRAINT FK_AF64C079973CA22 FOREIGN KEY (startMessage_id) REFERENCES messages (id)');
        $this->addSql('ALTER TABLE conversation_intervals ADD CONSTRAINT FK_AF64C07C26B0DEC FOREIGN KEY (endMessage_id) REFERENCES messages (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AF64C079973CA22 ON conversation_intervals (startMessage_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AF64C07C26B0DEC ON conversation_intervals (endMessage_id)');

        $this->addSql('ALTER TABLE conversation_intervals DROP FOREIGN KEY FK_AF64C079973CA22');
        $this->addSql('ALTER TABLE conversation_intervals DROP FOREIGN KEY FK_AF64C07C26B0DEC');
        $this->addSql('DROP INDEX UNIQ_AF64C079973CA22 ON conversation_intervals');
        $this->addSql('DROP INDEX UNIQ_AF64C07C26B0DEC ON conversation_intervals');
        $this->addSql('ALTER TABLE conversation_intervals DROP startMessage_id, DROP endMessage_id');
        $this->addSql('ALTER TABLE messages ADD previous_interval_id INT DEFAULT NULL, ADD following_interval_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96E2903F44 FOREIGN KEY (previous_interval_id) REFERENCES conversation_intervals (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E9615BC2872 FOREIGN KEY (following_interval_id) REFERENCES conversation_intervals (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_DB021E9615BC2872 ON messages (following_interval_id)');
        $this->addSql('CREATE INDEX IDX_DB021E96E2903F44 ON messages (previous_interval_id)');

        $this->addSql('ALTER TABLE conversations DROP stale_payment_info');
    }
}
