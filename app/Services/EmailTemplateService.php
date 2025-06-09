<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class EmailTemplateService
{
    /**
     * Get template variables for a ticket
     */
    public function getTicketVariables(Ticket $ticket, ?User $agent = null, ?TicketReply $reply = null): array
    {
        $variables = [
            'customer_name' => $ticket->creator->name,
            'customer_email' => $ticket->creator->email,
            'ticket_id' => substr($ticket->uuid, 0, 8),
            'ticket_subject' => $ticket->subject,
            'ticket_content' => $ticket->content,
            'ticket_status' => $ticket->status->name,
            'ticket_priority' => $ticket->priority->name,
            'ticket_url' => route('tickets.show', $ticket->uuid),
            'office_name' => $ticket->office->name,
            'company_name' => config('app.name', 'Company'),
            'current_date' => now()->format('F j, Y'),
            'current_time' => now()->format('g:i A'),
        ];

        // Add agent information if available
        if ($agent) {
            $variables['agent_name'] = $agent->name;
            $variables['agent_email'] = $agent->email;
        } elseif ($ticket->assignedTo) {
            $variables['agent_name'] = $ticket->assignedTo->name;
            $variables['agent_email'] = $ticket->assignedTo->email;
        } else {
            $variables['agent_name'] = 'Support Team';
            $variables['agent_email'] = config('mail.from.address', 'support@example.com');
        }

        // Add reply information if available
        if ($reply) {
            $variables['reply_content'] = $reply->content;
            $variables['reply_author'] = $reply->user->name;
        }

        return $variables;
    }

    /**
     * Process a template with given variables
     */
    public function processTemplate(EmailTemplate $template, array $variables): array
    {
        return [
            'subject' => $template->processSubject($variables),
            'content' => $template->processContent($variables),
            'type' => $template->type,
        ];
    }

    /**
     * Get the appropriate template for a ticket event
     */
    public function getTemplateForEvent(string $eventName, string $language = 'en'): ?EmailTemplate
    {
        // First try to find a default template
        $template = EmailTemplate::findDefaultTemplate($eventName, $language);

        // If no default found, try to find any active template with that name
        if (! $template) {
            $template = EmailTemplate::active()
                ->where('name', $eventName)
                ->language($language)
                ->first();
        }

        // If still no template found, try English as fallback
        if (! $template && $language !== 'en') {
            $template = $this->getTemplateForEvent($eventName, 'en');
        }

        return $template;
    }

    /**
     * Process ticket created email
     */
    public function processTicketCreatedEmail(Ticket $ticket, bool $isCustomer = false): ?array
    {
        $templateName = $isCustomer ? 'ticket_created_customer' : 'ticket_created_agent';
        $template = $this->getTemplateForEvent($templateName);

        if (! $template) {
            // Fallback to generic ticket_created template
            $template = $this->getTemplateForEvent('ticket_created');
        }

        if (! $template) {
            Log::warning("No email template found for event: {$templateName}");

            return null;
        }

        $variables = $this->getTicketVariables($ticket);

        return $this->processTemplate($template, $variables);
    }

    /**
     * Process ticket reply email
     */
    public function processTicketReplyEmail(Ticket $ticket, TicketReply $reply, bool $isCustomer = false): ?array
    {
        $templateName = $isCustomer ? 'ticket_reply_customer' : 'ticket_reply_agent';
        $template = $this->getTemplateForEvent($templateName);

        if (! $template) {
            // Fallback to generic ticket_reply template
            $template = $this->getTemplateForEvent('ticket_reply');
        }

        if (! $template) {
            Log::warning("No email template found for event: {$templateName}");

            return null;
        }

        $variables = $this->getTicketVariables($ticket, $reply->user, $reply);

        return $this->processTemplate($template, $variables);
    }

    /**
     * Create default system templates
     */
    public function createDefaultTemplates(): void
    {
        $defaultTemplates = [
            [
                'name' => 'ticket_created',
                'subject' => '[Ticket #{{ticket_id}}] {{ticket_subject}}',
                'content' => $this->getDefaultTicketCreatedContent(),
                'category' => 'ticket',
                'description' => 'Default template for ticket creation notifications',
                'is_default' => true,
                'variables' => array_keys(EmailTemplate::getAvailableVariables()),
            ],
            [
                'name' => 'ticket_reply',
                'subject' => '[Ticket #{{ticket_id}}] New Reply: {{ticket_subject}}',
                'content' => $this->getDefaultTicketReplyContent(),
                'category' => 'ticket',
                'description' => 'Default template for ticket reply notifications',
                'is_default' => true,
                'variables' => array_keys(EmailTemplate::getAvailableVariables()),
            ],
            [
                'name' => 'ticket_status_changed',
                'subject' => '[Ticket #{{ticket_id}}] Status Updated: {{ticket_subject}}',
                'content' => $this->getDefaultStatusChangedContent(),
                'category' => 'ticket',
                'description' => 'Template for ticket status change notifications',
                'is_default' => true,
                'variables' => array_keys(EmailTemplate::getAvailableVariables()),
            ],
            [
                'name' => 'ticket_assigned',
                'subject' => '[Ticket #{{ticket_id}}] Assigned: {{ticket_subject}}',
                'content' => $this->getDefaultAssignedContent(),
                'category' => 'ticket',
                'description' => 'Template for ticket assignment notifications',
                'is_default' => true,
                'variables' => array_keys(EmailTemplate::getAvailableVariables()),
            ],
        ];

        foreach ($defaultTemplates as $templateData) {
            EmailTemplate::updateOrCreate(
                ['name' => $templateData['name'], 'language' => 'en'],
                $templateData
            );
        }
    }

    /**
     * Get default ticket created content
     */
    private function getDefaultTicketCreatedContent(): string
    {
        return <<<'MARKDOWN'
# New Support Ticket Created

Hello {{customer_name}},

A new support ticket has been created and assigned number **{{ticket_id}}**.

**Subject:** {{ticket_subject}}  
**Priority:** {{ticket_priority}}  
**Department:** {{office_name}}  
**Assigned to:** {{agent_name}}  

**Description:**
{{ticket_content}}

[View Ticket]({{ticket_url}})

You can reply directly to this email to add comments to your ticket. Our support team will respond as soon as possible.

Thanks,  
{{company_name}} Support Team
MARKDOWN;
    }

    /**
     * Get default ticket reply content
     */
    private function getDefaultTicketReplyContent(): string
    {
        return <<<'MARKDOWN'
# New Reply on Ticket #{{ticket_id}}

Hello {{customer_name}},

{{reply_author}} has replied to your support ticket **{{ticket_id}}**.

**Subject:** {{ticket_subject}}  
**Status:** {{ticket_status}}  

**Reply:**
{{reply_content}}

[View Ticket]({{ticket_url}})

You can reply directly to this email to continue the conversation.

Thanks,  
{{company_name}} Support Team
MARKDOWN;
    }

    /**
     * Get default status changed content
     */
    private function getDefaultStatusChangedContent(): string
    {
        return <<<'MARKDOWN'
# Ticket Status Updated

Hello {{customer_name}},

The status of your support ticket **{{ticket_id}}** has been updated.

**Subject:** {{ticket_subject}}  
**New Status:** {{ticket_status}}  
**Priority:** {{ticket_priority}}  

[View Ticket]({{ticket_url}})

If you have any questions about this update, please reply to this email.

Thanks,  
{{company_name}} Support Team
MARKDOWN;
    }

    /**
     * Get default assigned content
     */
    private function getDefaultAssignedContent(): string
    {
        return <<<'MARKDOWN'
# Ticket Assigned

Hello {{customer_name}},

Your support ticket **{{ticket_id}}** has been assigned to {{agent_name}}.

**Subject:** {{ticket_subject}}  
**Status:** {{ticket_status}}  
**Priority:** {{ticket_priority}}  
**Assigned Agent:** {{agent_name}}  

[View Ticket]({{ticket_url}})

Your assigned agent will be in touch shortly to help resolve your issue.

Thanks,  
{{company_name}} Support Team
MARKDOWN;
    }

    /**
     * Validate template content for security
     */
    public function validateTemplateContent(string $content): array
    {
        $errors = [];

        // Check for potentially dangerous content
        $dangerousPatterns = [
            '/<script/i' => 'Script tags are not allowed',
            '/<iframe/i' => 'Iframe tags are not allowed',
            '/javascript:/i' => 'JavaScript URLs are not allowed',
            '/on\w+\s*=/i' => 'Event handlers are not allowed',
        ];

        foreach ($dangerousPatterns as $pattern => $message) {
            if (preg_match($pattern, $content)) {
                $errors[] = $message;
            }
        }

        return $errors;
    }

    /**
     * Get template usage statistics
     */
    public function getTemplateUsageStats(EmailTemplate $template): array
    {
        // This would typically track usage in a separate table
        // For now, return basic stats
        return [
            'total_sent' => 0, // Would be tracked in email_logs table
            'last_used' => null,
            'success_rate' => 100,
        ];
    }
}
