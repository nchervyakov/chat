<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150317153131 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE conversations (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, model_id INT DEFAULT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, last_message_date DATETIME DEFAULT NULL, INDEX IDX_C2521BF119EB6921 (client_id), INDEX IDX_C2521BF17975B7E7 (model_id), UNIQUE INDEX UNIQ_C2521BF119EB69217975B7E7 (client_id, model_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation_intervals (id INT AUTO_INCREMENT NOT NULL, conversation_id INT DEFAULT NULL, previous_interval_id INT DEFAULT NULL, status VARCHAR(16) NOT NULL, seconds INT DEFAULT 0 NOT NULL, price NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, minute_rate NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, model_share NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, model_earnings NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, last_message_date DATETIME DEFAULT NULL, INDEX IDX_AF64C079AC0396 (conversation_id), UNIQUE INDEX UNIQ_AF64C07E2903F44 (previous_interval_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE conversation_interval_packs (id INT AUTO_INCREMENT NOT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emoticons (id INT AUTO_INCREMENT NOT NULL, symbol VARCHAR(32) NOT NULL, aliases LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', icon VARCHAR(255) NOT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_F06D39705E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, conversation_id INT DEFAULT NULL, interval_id INT DEFAULT NULL, author_id INT DEFAULT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, content LONGTEXT DEFAULT NULL, deleted_by_user TINYINT(1) DEFAULT \'0\' NOT NULL, discriminator VARCHAR(32) NOT NULL, INDEX IDX_DB021E969AC0396 (conversation_id), INDEX IDX_DB021E96505A342E (interval_id), INDEX IDX_DB021E96F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, thumbnail_id INT DEFAULT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, locked TINYINT(1) NOT NULL, expired TINYINT(1) NOT NULL, expires_at DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', credentials_expired TINYINT(1) NOT NULL, credentials_expire_at DATETIME DEFAULT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, gender VARCHAR(255) DEFAULT NULL, date_of_birth DATE DEFAULT NULL, facebook_id VARCHAR(64) DEFAULT NULL, vkontakte_id VARCHAR(64) DEFAULT NULL, twitter_id VARCHAR(64) DEFAULT NULL, google_id VARCHAR(64) DEFAULT NULL, github_id VARCHAR(64) DEFAULT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, sort_order BIGINT DEFAULT \'0\', UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_1483A5E9FDFF2E92 (thumbnail_id), INDEX date_of_birth (date_of_birth), INDEX sort_order (sort_order), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users_groups (user_id INT NOT NULL, group_id INT NOT NULL, INDEX IDX_FF8AB7E0A76ED395 (user_id), INDEX IDX_FF8AB7E0FE54D947 (group_id), PRIMARY KEY(user_id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_photos (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, file_name VARCHAR(255) DEFAULT \'\' NOT NULL, title VARCHAR(255) DEFAULT \'\' NOT NULL, date_added DATETIME DEFAULT NULL, date_updated DATETIME DEFAULT NULL, INDEX IDX_6D24FBE47E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF119EB6921 FOREIGN KEY (client_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF17975B7E7 FOREIGN KEY (model_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE conversation_intervals ADD CONSTRAINT FK_AF64C079AC0396 FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_intervals ADD CONSTRAINT FK_AF64C07E2903F44 FOREIGN KEY (previous_interval_id) REFERENCES conversation_intervals (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E969AC0396 FOREIGN KEY (conversation_id) REFERENCES conversations (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96505A342E FOREIGN KEY (interval_id) REFERENCES conversation_intervals (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E9FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES user_photos (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE users_groups ADD CONSTRAINT FK_FF8AB7E0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users_groups ADD CONSTRAINT FK_FF8AB7E0FE54D947 FOREIGN KEY (group_id) REFERENCES groups (id)');
        $this->addSql('ALTER TABLE user_photos ADD CONSTRAINT FK_6D24FBE47E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE conversation_intervals DROP FOREIGN KEY FK_AF64C079AC0396');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E969AC0396');
        $this->addSql('ALTER TABLE conversation_intervals DROP FOREIGN KEY FK_AF64C07E2903F44');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96505A342E');
        $this->addSql('ALTER TABLE users_groups DROP FOREIGN KEY FK_FF8AB7E0FE54D947');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF119EB6921');
        $this->addSql('ALTER TABLE conversations DROP FOREIGN KEY FK_C2521BF17975B7E7');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F675F31B');
        $this->addSql('ALTER TABLE users_groups DROP FOREIGN KEY FK_FF8AB7E0A76ED395');
        $this->addSql('ALTER TABLE user_photos DROP FOREIGN KEY FK_6D24FBE47E3C61F9');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E9FDFF2E92');
        $this->addSql('DROP TABLE conversations');
        $this->addSql('DROP TABLE conversation_intervals');
        $this->addSql('DROP TABLE conversation_interval_packs');
        $this->addSql('DROP TABLE emoticons');
        $this->addSql('DROP TABLE groups');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE users_groups');
        $this->addSql('DROP TABLE user_photos');
    }
}
