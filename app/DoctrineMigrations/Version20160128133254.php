<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160128133254 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE queue_messages DROP FOREIGN KEY FK_1A8181A66C066AFE');
        $this->addSql('DROP INDEX idx_a2e31f516c066afe ON queue_messages');
        $this->addSql('CREATE INDEX IDX_1A8181A66C066AFE ON queue_messages (target_user_id)');
        $this->addSql('ALTER TABLE queue_messages ADD CONSTRAINT FK_1A8181A66C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE queue_messages DROP FOREIGN KEY FK_1A8181A66C066AFE');
        $this->addSql('DROP INDEX idx_1a8181a66c066afe ON queue_messages');
        $this->addSql('CREATE INDEX IDX_A2E31F516C066AFE ON queue_messages (target_user_id)');
        $this->addSql('ALTER TABLE queue_messages ADD CONSTRAINT FK_1A8181A66C066AFE FOREIGN KEY (target_user_id) REFERENCES users (id) ON DELETE CASCADE');
    }
}
