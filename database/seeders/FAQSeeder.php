<?php

namespace Database\Seeders;

use App\Models\FAQ;
use App\Models\Office;
use Illuminate\Database\Seeder;

class FAQSeeder extends Seeder
{
    public function run(): void
    {
        $generalFAQs = [
            [
                'question' => 'How do I create a support ticket?',
                'answer' => 'To create a support ticket, log in to your account and click on "Create New Ticket" from your dashboard. Fill in the required information including the subject, description, department, and priority level. You can also attach files if needed.',
                'sort_order' => 1,
            ],
            [
                'question' => 'How can I track the status of my ticket?',
                'answer' => 'You can track your ticket status by going to "My Tickets" in the navigation menu. There you will see all your tickets with their current status, priority, and last update. You can click on any ticket to view the full conversation and timeline.',
                'sort_order' => 2,
            ],
            [
                'question' => 'What information should I include in my support request?',
                'answer' => 'Please provide as much detail as possible including:
- A clear description of the issue
- Steps to reproduce the problem
- Screenshots or error messages if applicable
- Your operating system and browser information
- Any relevant files or documents

The more information you provide, the faster we can resolve your issue.',
                'sort_order' => 3,
            ],
            [
                'question' => 'How quickly will I receive a response?',
                'answer' => 'Response times vary based on the priority level of your ticket:
- High Priority: Within 2 hours during business hours
- Medium Priority: Within 8 hours during business hours
- Low Priority: Within 24 hours during business hours

Business hours are Monday through Friday, 9 AM to 5 PM.',
                'sort_order' => 4,
            ],
            [
                'question' => 'Can I reply to my existing ticket?',
                'answer' => 'Yes! You can add replies to your existing tickets. Simply go to "My Tickets", click on the ticket you want to update, and use the reply form at the bottom of the page. You can also attach additional files with your reply.',
                'sort_order' => 5,
            ],
        ];

        foreach ($generalFAQs as $faq) {
            FAQ::create($faq);
        }

        $itOffice = Office::where('name', 'IT Support')->first();
        if ($itOffice) {
            FAQ::create([
                'question' => 'I forgot my password. How can I reset it?',
                'answer' => 'You can reset your password by clicking the "Forgot Password" link on the login page. Enter your email address and we will send you a password reset link. If you continue to have issues, please create a support ticket with the IT Support department.',
                'office_id' => $itOffice->id,
                'sort_order' => 1,
            ]);

            FAQ::create([
                'question' => 'How do I connect to the company VPN?',
                'answer' => 'To connect to the company VPN:
1. Download the VPN client from the IT portal
2. Use your company credentials to log in
3. Select the appropriate server location
4. Click connect

If you need VPN access credentials or encounter connection issues, please create an IT Support ticket.',
                'office_id' => $itOffice->id,
                'sort_order' => 2,
            ]);
        }

        $hrOffice = Office::where('name', 'Human Resources')->first();
        if ($hrOffice) {
            FAQ::create([
                'question' => 'How do I request time off?',
                'answer' => 'Time off requests should be submitted through the HR portal at least 2 weeks in advance. For urgent requests or questions about your leave balance, please create an HR support ticket.',
                'office_id' => $hrOffice->id,
                'sort_order' => 1,
            ]);

            FAQ::create([
                'question' => 'Where can I find my employee handbook?',
                'answer' => 'The employee handbook is available in the HR portal under "Documents". If you cannot access it or need a printed copy, please create an HR support ticket.',
                'office_id' => $hrOffice->id,
                'sort_order' => 2,
            ]);
        }
    }
}
