<?php

namespace Wallabag\CoreBundle\Mailer;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * Custom mailer for TwoFactorBundle email.
 * It adds a custom template to the email so user won't get a lonely authentication code but a complete email.
 */
class AuthCodeMailer implements AuthCodeMailerInterface
{
    /**
     * Mailer.
     *
     * @var MailerInterface
     */
    private $mailer;

    /**
     * Twig to render the html's email.
     *
     * @var Environment
     */
    private $twig;

    /**
     * Sender email address.
     *
     * @var string
     */
    private $senderEmail;

    /**
     * Sender name.
     *
     * @var string
     */
    private $senderName;

    /**
     * Support URL to report any bugs.
     *
     * @var string
     */
    private $supportUrl;

    /**
     * Url for the wallabag instance (only used for image in the HTML email template).
     *
     * @var string
     */
    private $wallabagUrl;

    /**
     * @param string $senderEmail
     * @param string $senderName
     * @param string $supportUrl  wallabag support url
     * @param string $wallabagUrl wallabag instance url
     */
    public function __construct(MailerInterface $mailer, Environment $twig, $senderEmail, $senderName, $supportUrl, $wallabagUrl)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
        $this->supportUrl = $supportUrl;
        $this->wallabagUrl = $wallabagUrl;
    }

    /**
     * Send the auth code to the user via email.
     */
    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $template = $this->twig->load('@WallabagCore/TwoFactor/email_auth_code.html.twig');

        $subject = $template->renderBlock('subject', []);
        $bodyHtml = $template->renderBlock('body_html', [
            'user' => $user->getName(),
            'code' => $user->getEmailAuthCode(),
            'support_url' => $this->supportUrl,
            'wallabag_url' => $this->wallabagUrl,
        ]);
        $bodyText = $template->renderBlock('body_text', [
            'user' => $user->getName(),
            'code' => $user->getEmailAuthCode(),
            'support_url' => $this->supportUrl,
        ]);

        $email = (new Email())
            ->from(new Address($this->senderEmail, $this->senderName ?? $this->senderEmail))
            ->to($user->getEmailAuthRecipient())
            ->subject($subject)
            ->text($bodyText)
            ->html($bodyHtml);

        $this->mailer->send($email);
    }
}
