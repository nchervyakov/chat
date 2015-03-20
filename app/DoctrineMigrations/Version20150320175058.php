<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150320175058 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE model_requests ('
            .'id INT AUTO_INCREMENT NOT NULL, '
            .'first_name VARCHAR(255) NOT NULL, '
            .'last_name VARCHAR(64) NOT NULL, '
            .'email VARCHAR(255) NOT NULL, '
            .'facebook_url VARCHAR(255) NOT NULL, '
            .'instagram_url VARCHAR(255) DEFAULT NULL, '
            .'message LONGTEXT NOT NULL, '
            .'date_added DATETIME DEFAULT NULL, '
            .'date_updated DATETIME DEFAULT NULL, '
            .'PRIMARY KEY(id)'
            .') DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB'
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE model_requests');
    }
}
