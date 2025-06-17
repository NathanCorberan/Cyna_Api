<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\Text\HtmlPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Twig\Environment;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {}

    public function sendInvoiceEmail(string $to, array $invoiceData): void
    {
        $html = $this->twig->render('emails/invoice.html.twig', $invoiceData);

        $email = (new Email())
            ->from('no-reply@corberan.fr')
            ->to($to)
            ->subject('Votre facture')
            ->html($html);

        $this->mailer->send($email);
    }
}
