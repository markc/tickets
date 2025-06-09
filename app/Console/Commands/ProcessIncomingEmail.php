<?php

namespace App\Console\Commands;

use App\Services\EmailTicketService;
use Exception;
use eXorus\PhpMimeMailParser\Parser;
use Illuminate\Console\Command;

class ProcessIncomingEmail extends Command
{
    protected $signature = 'ticket:process-email';

    protected $description = 'Process an incoming email from STDIN and create a ticket or reply';

    public function __construct(private EmailTicketService $emailTicketService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $parser = new Parser;
            $parser->setStream(fopen('php://stdin', 'r'));

            $emailData = $this->extractEmailData($parser);

            if (! $emailData) {
                $this->error('Failed to parse email data');

                return 1;
            }

            if (! $this->isValidSender($emailData['from_email'])) {
                $this->info("Ignoring email from invalid sender: {$emailData['from_email']}");

                return 0;
            }

            $ticketUuid = $this->extractTicketUuid($emailData['recipient']);

            if ($ticketUuid) {
                $this->info("Processing as reply to ticket: {$ticketUuid}");
                $this->emailTicketService->createReplyFromEmail(
                    $ticketUuid,
                    $emailData['from_email'],
                    $emailData['from_name'],
                    $emailData['subject'],
                    $emailData['content'],
                    $emailData['attachments']
                );
            } else {
                $this->info("Processing as new ticket from: {$emailData['from_email']}");
                $this->emailTicketService->createTicketFromEmail(
                    $emailData['from_email'],
                    $emailData['from_name'],
                    $emailData['subject'],
                    $emailData['content'],
                    $emailData['attachments']
                );
            }

            $this->info('Email processed successfully');

            return 0;

        } catch (Exception $e) {
            $this->error('Failed to process email: '.$e->getMessage());
            $this->error('Stack trace: '.$e->getTraceAsString());

            return 1;
        }
    }

    private function extractEmailData(Parser $parser): ?array
    {
        try {
            $fromHeader = $parser->getHeader('from');
            $toHeader = $parser->getHeader('to');
            $subject = $parser->getHeader('subject') ?: 'No Subject';

            if (! $fromHeader || ! $toHeader) {
                return null;
            }

            preg_match('/^(.*?)\s*<(.+?)>$/', $fromHeader, $fromMatches);
            if ($fromMatches) {
                $fromName = trim($fromMatches[1], '"');
                $fromEmail = $fromMatches[2];
            } else {
                $fromEmail = $fromHeader;
                $fromName = $fromHeader;
            }

            $textContent = $parser->getMessageBody('text');
            $htmlContent = $parser->getMessageBody('html');

            $content = $this->cleanEmailContent($textContent ?: $this->htmlToText($htmlContent));

            if (empty($content)) {
                $content = 'No content available.';
            }

            $attachments = $this->extractAttachments($parser);

            return [
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'recipient' => $toHeader,
                'subject' => $subject,
                'content' => $content,
                'attachments' => $attachments,
            ];

        } catch (Exception $e) {
            $this->error('Failed to extract email data: '.$e->getMessage());

            return null;
        }
    }

    private function extractAttachments(Parser $parser): array
    {
        $attachments = [];

        try {
            $attachmentData = $parser->getAttachments();

            if (is_array($attachmentData)) {
                foreach ($attachmentData as $attachment) {
                    if (isset($attachment['filename']) && isset($attachment['content'])) {
                        $attachments[] = [
                            'filename' => $attachment['filename'],
                            'content' => $attachment['content'],
                            'mime_type' => $attachment['content-type'] ?? 'application/octet-stream',
                            'size' => strlen($attachment['content']),
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            $this->warn('Failed to extract attachments: '.$e->getMessage());
        }

        return $attachments;
    }

    private function extractTicketUuid(string $recipient): ?string
    {
        if (preg_match('/support\+([a-f0-9\-]{36})@/i', $recipient, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isValidSender(string $email): bool
    {
        if (empty($email)) {
            return false;
        }

        $blockedDomains = [
            'noreply',
            'no-reply',
            'mailer-daemon',
            'postmaster',
            'bounce',
        ];

        foreach ($blockedDomains as $blocked) {
            if (stripos($email, $blocked) !== false) {
                return false;
            }
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function cleanEmailContent(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        $lines = explode("\n", $content);
        $cleanedLines = [];
        $foundQuoteLine = false;

        foreach ($lines as $line) {
            $line = rtrim($line);

            if ($this->isQuotedReplyLine($line)) {
                $foundQuoteLine = true;
                break;
            }

            if (! empty($line) || ! $foundQuoteLine) {
                $cleanedLines[] = $line;
            }
        }

        $cleaned = implode("\n", $cleanedLines);
        $cleaned = trim($cleaned);

        return $cleaned ?: 'No content available.';
    }

    private function isQuotedReplyLine(string $line): bool
    {
        $quotedPatterns = [
            '/^On .* wrote:/',
            '/^>/',
            '/^From:.*/',
            '/^Sent:.*/',
            '/^To:.*/',
            '/^Subject:.*/',
            '/^Date:.*/',
            '/^\s*----+\s*Original Message\s*----+/',
            '/^\s*_{10,}/',
        ];

        foreach ($quotedPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    private function htmlToText(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = preg_replace('/<\/p>/i', "\n\n", $html);
        $html = preg_replace('/<\/div>/i', "\n", $html);

        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
