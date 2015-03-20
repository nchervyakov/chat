<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150320142311 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE INDEX first_name ON users (first_name)');
        $this->addSql('CREATE INDEX last_name ON users (last_name)');
        $this->addSql('ALTER TABLE user_photos DROP FOREIGN KEY FK_6D24FBE47E3C61F9');
        $this->addSql('ALTER TABLE user_photos ADD CONSTRAINT FK_6D24FBE47E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_photos DROP FOREIGN KEY FK_6D24FBE47E3C61F9');
        $this->addSql('ALTER TABLE user_photos ADD CONSTRAINT FK_6D24FBE47E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX first_name ON users');
        $this->addSql('DROP INDEX last_name ON users');
    }
}
