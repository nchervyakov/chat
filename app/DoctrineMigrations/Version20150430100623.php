<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Query;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150430100623 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $conn = $this->connection;

        $groupNames = [
            'Admins' => ['role' => 'ROLE_ADMIN'],
            'Models' => ['role' => 'ROLE_MODEL'],
            'Clients' => ['role' => 'ROLE_CLIENT'],
        ];

        foreach ($groupNames as $groupName => $data) {
            // Check if the groups exist
            $statement = $conn->prepare('SELECT COUNT(*) cnt FROM groups WHERE name LIKE :group_name');
            $statement->execute([':group_name' => $groupName]);
            $res = $statement->fetch(Query::HYDRATE_SINGLE_SCALAR);
            $count = (int) $res[0];

            if ($count == 0) {
                $this->addSql("INSERT INTO `groups` (`name`, `roles`, `date_added`, `date_updated`) VALUES "
                        . "(:name, :role,	'2015-03-27 10:24:49', NULL)", [
                    ':name' => $groupName,
                    ':role' => "a:1:{i:0;s:10:\"" . $data['role'] . "\";}"
                ]);
            }
        }

        // Check if the admin exists
        $res = $conn->query('SELECT COUNT(*) cnt FROM users WHERE username_canonical LIKE \'admin\'')->fetch(Query::HYDRATE_SINGLE_SCALAR);
        $adminCount = (int) $res[0];

        if ($adminCount == 0) {
            // Add admin user
            $this->addSql(
                "INSERT INTO `users` "
                    . "(`thumbnail_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, "
                    . "`salt`, `password`, `last_login`, `locked`, `expired`, `expires_at`, `confirmation_token`, "
                    . "`password_requested_at`, `roles`, `credentials_expired`, `credentials_expire_at`, `firstname`, `lastname`, "
                    . "`date_of_birth`, `facebook_id`, `vkontakte_id`, `twitter_id`, `google_id`, `github_id`, "
                    . "`date_added`, `date_updated`, `sort_order`, `created_at`, `updated_at`, `website`, `biography`, "
                    . "`gender`, `locale`, `timezone`, `phone`, `facebook_uid`, `facebook_name`, `facebook_data`, "
                    . "`twitter_uid`, `twitter_name`, `twitter_data`, `gplus_uid`, `gplus_name`, `gplus_data`, `token`, "
                    . "`two_step_code`, `facebook_url`, `instagram_url`, `instagram_id`, `activated`, `activation_token`, "
                    . "`model_notified`, `last_visited_date`, `coins`, `is_online`) "
                . "VALUES "
                    . "(NULL, 'admin', 'admin', 'admin@kukik.co', 'admin@kukik.co', 1, "
                    . "'iz55lk2rvzcokcskgo48co8kw4004o0', '$2y$13\$iz55lk2rvzcokcskgo48ceX1NtkwHAfE8XIYRM1LRLFJSTuevFQ36', "
                    . "'2015-04-28 10:31:38', 0, 0,	NULL, NULL,	NULL, 'a:1:{i:0;s:16:\"ROLE_SUPER_ADMIN\";}', "
                    . "0, NULL,	'', '', '2005-03-01 00:00:00', NULL, NULL, NULL, NULL, NULL, '2015-03-01 09:15:29',	"
                    . "'2015-04-28 10:43:04', 1, '2015-04-01 12:32:51', '2015-04-01 12:32:51', NULL, NULL, 'u', NULL, "
                    . "NULL, NULL, NULL, NULL, 'null', NULL, NULL, 'null', NULL, NULL, 'null', NULL, NULL, NULL, NULL, "
                    . "NULL, 1, NULL, 0, '2015-04-28 10:43:04', 0.00000000, 0)");
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
