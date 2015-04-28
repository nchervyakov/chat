<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150427085753 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE queue_messages (id INT AUTO_INCREMENT NOT NULL, target_user_id INT NOT NULL, name VARCHAR(64) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', date_added DATETIME DEFAULT NULL, INDEX IDX_A2E31F516C066AFE (target_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE queue_messages ADD CONSTRAINT FK_A2E31F516C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users ADD is_online TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users DROP is_online');
        $this->addSql('DROP TABLE queue_messages');
    }
}
