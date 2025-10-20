<?php

namespace Mortogo321\LaravelNotify\Providers;

use Illuminate\Support\Facades\Mail;
use Mortogo321\LaravelNotify\Exceptions\NotificationException;

class EmailProvider extends AbstractProvider
{
    protected string $name = 'email';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->validateConfig(['to']);
    }

    /**
     * Send notification via Email.
     *
     * @param string $message
     * @param array $options
     * @return mixed
     * @throws NotificationException
     */
    public function send(string $message, array $options = []): mixed
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Provider is disabled'];
        }

        try {
            $to = $options['to'] ?? $this->getConfig('to');
            $subject = $options['subject'] ?? $this->getConfig('subject', 'Laravel Notification');
            $from = $options['from'] ?? $this->getConfig('from');
            $fromName = $options['from_name'] ?? $this->getConfig('from_name', 'Laravel Notify');

            Mail::html($message, function ($mail) use ($to, $subject, $from, $fromName) {
                $mail->to($to);
                $mail->subject($subject);

                if ($from) {
                    $mail->from($from, $fromName);
                }
            });

            return [
                'success' => true,
                'message' => 'Email sent successfully',
            ];
        } catch (\Exception $e) {
            throw new NotificationException("Failed to send Email notification: {$e->getMessage()}");
        }
    }
}
