<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150327130243 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("UPDATE users SET gender = 'm' WHERE user_gender = 'male'");
        $this->addSql("UPDATE users SET gender = 'f' WHERE user_gender = 'female'");
        $this->addSql("UPDATE users SET gender = 'u' WHERE gender NOT IN ('f', 'm')");

        $this->addSql('ALTER TABLE users DROP user_gender');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users ADD user_gender VARCHAR(255) DEFAULT NULL');

        $this->addSql("UPDATE users SET user_gender = NULL WHERE gender = 'u'");
        $this->addSql("UPDATE users SET user_gender = 'female' WHERE gender = 'f'");
        $this->addSql("UPDATE users SET user_gender = 'male' WHERE gender = 'm'");
    }
}
