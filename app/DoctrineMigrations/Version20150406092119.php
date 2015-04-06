<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150406092119 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users ADD model_notified TINYINT(1) DEFAULT \'0\' NOT NULL');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E99BE8FD98 ON users (facebook_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E989588C72 ON users (vkontakte_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9C63E6FFF ON users (twitter_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E976F5C865 ON users (google_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9D4327649 ON users (github_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E99C19920F ON users (instagram_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_1483A5E99BE8FD98 ON users');
        $this->addSql('DROP INDEX UNIQ_1483A5E989588C72 ON users');
        $this->addSql('DROP INDEX UNIQ_1483A5E9C63E6FFF ON users');
        $this->addSql('DROP INDEX UNIQ_1483A5E976F5C865 ON users');
        $this->addSql('DROP INDEX UNIQ_1483A5E9D4327649 ON users');
        $this->addSql('DROP INDEX UNIQ_1483A5E99C19920F ON users');

        $this->addSql('ALTER TABLE users DROP model_notified');
    }
}
