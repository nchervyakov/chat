<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150409132958 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF119EB6921');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF17975B7E7');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF119EB6921 FOREIGN KEY (client_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF17975B7E7 FOREIGN KEY (model_id) REFERENCES users (id) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F675F31B');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF17975B7E7');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF119EB6921');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF17975B7E7 FOREIGN KEY (model_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF119EB6921 FOREIGN KEY (client_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF119EB6921');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF17975B7E7');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF119EB6921 FOREIGN KEY (client_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF17975B7E7 FOREIGN KEY (model_id) REFERENCES users (id) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F675F31B');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');

        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF119EB6921');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF17975B7E7');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF119EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF17975B7E7 FOREIGN KEY (model_id) REFERENCES users (id)');
    }
}
