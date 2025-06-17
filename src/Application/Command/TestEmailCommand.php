<?php
// src/Command/TestEmailCommand.php
namespace App\Application\Command;

use App\Service\MailerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test:email',
    description: 'Test de l’envoi d’un email de confirmation abonnement.'
)]
class TestEmailCommand extends Command
{
    public function __construct(private MailerService $mailerService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Adresse email de destination');
        $this->addArgument('prenom', InputArgument::OPTIONAL, 'Prénom de l’utilisateur', 'Utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $prenom = $input->getArgument('prenom');

        $this->mailerService->sendSubscriptionConfirmation($email, $prenom);
        $output->writeln("📧 Email envoyé à $email");

        return Command::SUCCESS;
    }
}
