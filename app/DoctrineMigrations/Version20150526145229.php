<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150526145229 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE oauth_request (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, oauth_user_id VARCHAR(255) DEFAULT NULL, provider_name VARCHAR(32) NOT NULL, code LONGTEXT DEFAULT NULL, access_token VARCHAR(255) DEFAULT NULL, refresh_token VARCHAR(255) DEFAULT NULL, expires DATETIME DEFAULT NULL, auth_token_expires DATETIME DEFAULT NULL, refresh_token_expires DATETIME DEFAULT NULL, added_by_ip VARCHAR(45) NOT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_289120EAA76ED395 (user_id), INDEX token (token), INDEX oauth_user_id (oauth_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE oauth_request ADD CONSTRAINT FK_289120EAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE oauth_request CHANGE auth_token_expires access_token_expires DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE oauth_request ADD redirect_uri VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE queue_messages DROP FOREIGN KEY FK_A2E31F516C066AFE');
        $this->addSql('ALTER TABLE queue_messages ADD CONSTRAINT FK_1A8181A66C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE oauth_request DROP FOREIGN KEY FK_289120EAA76ED395');
        $this->addSql('ALTER TABLE oauth_request ADD CONSTRAINT FK_289120EAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE oauth_request ADD oauth_data LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('UPDATE oauth_request SET oauth_data = \'a:0:{}\' WHERE oauth_data LIKE \'\'');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE oauth_request DROP FOREIGN KEY FK_289120EAA76ED395');
        $this->addSql('ALTER TABLE oauth_request ADD CONSTRAINT FK_289120EAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');

        $this->addSql('ALTER TABLE queue_messages DROP FOREIGN KEY FK_1A8181A66C066AFE');
        $this->addSql('ALTER TABLE queue_messages ADD CONSTRAINT FK_A2E31F516C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id)');

        $this->addSql('DROP TABLE oauth_request');
    }
}
