<?php
namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;

class UserFixtures extends Fixture
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function load(ObjectManager $manager): void
    {
        // Charger les données sauvegardées
        $usersData = require __DIR__ . '/user_fixtures.php';

        // Désactiver temporairement la contrainte auto-incrémentation
        $this->connection->executeStatement('PRAGMA foreign_keys = OFF;');

        // Insérer chaque utilisateur avec son ID
        foreach ($usersData as $userData) {
            $this->connection->insert('user', [
                'id' => $userData['id'],
                'email' => $userData['email'],
                'roles' => json_encode($userData['roles']),
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'is_activate' => $userData['isActivate'],
                'password' => $userData['password'],
            ]);
        }

        // Réactiver la contrainte
        $this->connection->executeStatement('PRAGMA foreign_keys = ON;');
    }
}
