<?php
namespace App\Application\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

#[AsCommand(
    name: 'app:save-db-to-fixtures',
    description: 'Export database data to a fixture file.',
)]
class SaveDatabaseToFixturesCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = new Filesystem();
        $userRepo = $this->entityManager->getRepository(User::class);
        $users = $userRepo->findAll();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'isActivate' => $user->isActivate(),
                'password' => $user->getPassword(), // Attention, le mot de passe est déjà hashé
            ];
        }

        $fixtureContent = '<?php' . "\n\n" . 'return ' . var_export($data, true) . ';';
        $filePath = 'src/DataFixtures/user_fixtures.php';

        try {
            $filesystem->dumpFile($filePath, $fixtureContent);
            $output->writeln("<info>✅ Données enregistrées dans $filePath</info>");
        } catch (IOExceptionInterface $exception) {
            $output->writeln("<error>❌ Erreur lors de la sauvegarde des fixtures : " . $exception->getMessage() . "</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

