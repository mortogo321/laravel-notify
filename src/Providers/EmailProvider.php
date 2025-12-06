<?php

declare(strict_types=1);

namespace Mortogo321\LaravelNotify\Providers;

use Illuminate\Support\Facades\Mail;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;
use Throwable;

class EmailProvider extends AbstractProvider
{
    protected string $name = 'email';

    /**
     * Create a new Email provider instance.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['to']);
    }

    /**
     * Send notification via Email.
     *
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message?: string, status_code?: int, response?: mixed}
     *
     * @throws NotificationException
     */
    public function send(string $message, array $options = []): array
    {
        if (! $this->isEnabled()) {
            return $this->disabledResponse();
        }

        try {
            $to = $options['to'] ?? $this->getConfig('to');
            $subject = $options['subject'] ?? $this->getConfig('subject', 'Laravel Notification');
            $from = $options['from'] ?? $this->getConfig('from');
            $fromName = $options['from_name'] ?? $this->getConfig('from_name', 'Laravel Notify');
            $cc = $options['cc'] ?? $this->getConfig('cc');
            $bcc = $options['bcc'] ?? $this->getConfig('bcc');

            Mail::html($message, function ($mail) use ($to, $subject, $from, $fromName, $cc, $bcc) {
                $mail->to($this->parseRecipients($to));
                $mail->subject($subject);

                if ($from) {
                    $mail->from($from, $fromName);
                }

                if ($cc) {
                    $mail->cc($this->parseRecipients($cc));
                }

                if ($bcc) {
                    $mail->bcc($this->parseRecipients($bcc));
                }
            });

            return [
                'success' => true,
                'message' => 'Email sent successfully',
            ];
        } catch (Throwable $e) {
            throw NotificationException::sendFailed($this->name, $e->getMessage(), $e);
        }
    }

    /**
     * Parse recipients from string or array.
     *
     * @param  string|array<int, string>|null  $recipients
     * @return array<int, string>
     */
    protected function parseRecipients(string|array|null $recipients): array
    {
        if (is_null($recipients)) {
            return [];
        }

        if (is_array($recipients)) {
            return $recipients;
        }

        return array_map('trim', explode(',', $recipients));
    }

    /**
     * Get the configured recipient email.
     */
    public function getTo(): ?string
    {
        $to = $this->getConfig('to');

        return is_array($to) ? implode(', ', $to) : $to;
    }

    /**
     * Get the configured sender email.
     */
    public function getFrom(): ?string
    {
        return $this->getConfig('from');
    }

    /**
     * Get the configured sender name.
     */
    public function getFromName(): string
    {
        return $this->getConfig('from_name', 'Laravel Notify');
    }

    /**
     * Get the configured subject.
     */
    public function getSubject(): string
    {
        return $this->getConfig('subject', 'Laravel Notification');
    }

    /**
     * Get the configured CC recipients.
     */
    public function getCc(): ?string
    {
        $cc = $this->getConfig('cc');

        return is_array($cc) ? implode(', ', $cc) : $cc;
    }

    /**
     * Get the configured BCC recipients.
     */
    public function getBcc(): ?string
    {
        $bcc = $this->getConfig('bcc');

        return is_array($bcc) ? implode(', ', $bcc) : $bcc;
    }

    /**
     * Validate an email address.
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate multiple email addresses.
     *
     * @param  array<int, string>  $emails
     * @return array{valid: array<int, string>, invalid: array<int, string>}
     */
    public function validateEmails(array $emails): array
    {
        $valid = [];
        $invalid = [];

        foreach ($emails as $email) {
            $email = trim($email);

            if ($this->validateEmail($email)) {
                $valid[] = $email;
            } else {
                $invalid[] = $email;
            }
        }

        return [
            'valid' => $valid,
            'invalid' => $invalid,
        ];
    }
}
