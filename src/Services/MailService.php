<?php

namespace okpt\furnics\project\Services;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private $mailer;
    private $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $transport = Transport::fromDsn('smtp://YOUR-ADDRESS:YOUR-PASSWORD@smtp.gmail.com:465');
        $this->mailer = new Mailer($transport);
        $this->twig = $twig;
    }

    public function sendEmail(string $to, string $subject, array $context): void
    {
        $htmlContent = $this->twig->render('mail/order-detail.html.twig', $context);

        $email = (new Email())
            ->from('YOUR-ADDRESS')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }

    public function sendEmailTest(string $to, string $subject, string $body): void
    {
        $htmlContent = $this->twig->render('mail/mailTest.html.twig', [
            'subject' => $subject,
            'body' => $body,
        ]);

        $email = (new Email())
            ->from('talomkevin2017@gmail.com')
            ->to($to)
            ->subject($subject)
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}
