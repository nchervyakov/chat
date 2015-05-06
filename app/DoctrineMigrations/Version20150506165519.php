<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Query;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150506165519 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $conn = $this->connection;

        // Add Admin to the "Admins" group.
        $res = $conn->query("SELECT id FROM users WHERE username_canonical LIKE 'admin'")->fetch(Query::HYDRATE_SINGLE_SCALAR);
        $adminId = (int)$res[0];

        $res = $conn->query("SELECT id FROM groups WHERE name LIKE 'Admins'")->fetch(Query::HYDRATE_SINGLE_SCALAR);
        $adminGroupId = (int)$res[0];

        $statement = $conn->prepare('SELECT COUNT(*) cnt FROM users_groups WHERE user_id = :user_id AND group_id = :group_id');
        $statement->execute([':group_id' => $adminGroupId, ':user_id' => $adminId]);
        $res = $statement->fetch(Query::HYDRATE_SINGLE_SCALAR);
        $count = (int) $res[0];

        if ($count == 0) {
            $this->addSql("INSERT INTO `users_groups` (`user_id`, `group_id`) VALUES (:user_id, :group_id)", [
                ':user_id' => $adminId,
                ':group_id' => $adminGroupId
            ]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
