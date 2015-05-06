<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150430162314 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE message_complaint (id INT AUTO_INCREMENT NOT NULL, message_id INT NOT NULL, model_id INT NOT NULL, resolved_by INT DEFAULT NULL, status VARCHAR(16) DEFAULT \'open\' NOT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, added_by_ip VARCHAR(45) NOT NULL, UNIQUE INDEX UNIQ_359E3483537A1329 (message_id), INDEX IDX_359E34837975B7E7 (model_id), INDEX IDX_359E348357EB21F9 (resolved_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE message_complaint ADD CONSTRAINT FK_359E3483537A1329 FOREIGN KEY (message_id) REFERENCES messages (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message_complaint ADD CONSTRAINT FK_359E34837975B7E7 FOREIGN KEY (model_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message_complaint ADD CONSTRAINT FK_359E348357EB21F9 FOREIGN KEY (resolved_by) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE messages ADD added_by_ip VARCHAR(45) NOT NULL, ADD notification_type VARCHAR(16) DEFAULT \'info\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE message_complaint');
        $this->addSql('ALTER TABLE messages DROP added_by_ip, DROP notification_type');
    }
}
