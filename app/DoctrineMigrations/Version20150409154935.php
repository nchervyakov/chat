<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150409154935 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E92B41B332');
        $this->addSql('DROP INDEX UNIQ_1483A5E92B41B332 ON users');
        $this->addSql('ALTER TABLE users ADD last_visited_date DATETIME DEFAULT NULL, DROP model_request_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE users ADD model_request_id INT DEFAULT NULL, DROP last_visited_date');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E92B41B332 FOREIGN KEY (model_request_id) REFERENCES model_requests (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E92B41B332 ON users (model_request_id)');
    }
}
