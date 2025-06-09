<?php

namespace Database\Factories;

use App\Models\FAQ;
use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FAQ>
 */
class FAQFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FAQ::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questions = [
            'How to reset your password?',
            'What are the system requirements?',
            'How to contact support?',
            'How to change your email address?',
            'What browsers are supported?',
            'How to update your profile information?',
            'How to enable two-factor authentication?',
            'What file types are supported for uploads?',
            'How to cancel your subscription?',
            'How to export your data?',
        ];

        $answers = [
            'To reset your password, click on the "Forgot Password" link on the login page and follow the instructions sent to your email.',
            'The system requires PHP 8.3+, MySQL 8.0+, Node.js 18+, and at least 4GB RAM for optimal performance.',
            'You can contact our support team through the ticket system, email at support@company.com, or phone at (555) 123-4567.',
            'To change your email address, go to your profile settings and update the email field. You will need to verify the new email address.',
            'We support the latest versions of Chrome, Firefox, Safari, and Edge. Internet Explorer is not supported.',
            'Navigate to your profile page and click the "Edit" button to update your information such as name, phone number, and preferences.',
            'Go to your security settings and enable two-factor authentication using an authenticator app like Google Authenticator or Authy.',
            'We support JPG, PNG, PDF, DOC, DOCX, and TXT files up to 10MB in size. Other file types may be rejected.',
            'To cancel your subscription, go to your account settings and click "Cancel Subscription". This will take effect at the end of your billing period.',
            'You can export your data by going to account settings and clicking "Export Data". A download link will be sent to your email.',
        ];

        $index = array_rand($questions);

        return [
            'question' => $questions[$index],
            'answer' => $answers[$index],
            'office_id' => $this->faker->randomElement([null, Office::factory()]),
            'sort_order' => $this->faker->numberBetween(1, 100),
            'is_published' => $this->faker->boolean(80), // 80% chance of being published
        ];
    }

    /**
     * Indicate that the FAQ is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    /**
     * Indicate that the FAQ is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Indicate that the FAQ belongs to a specific office.
     */
    public function forOffice(Office $office): static
    {
        return $this->state(fn (array $attributes) => [
            'office_id' => $office->id,
        ]);
    }

    /**
     * Indicate that the FAQ is global (no office).
     */
    public function global(): static
    {
        return $this->state(fn (array $attributes) => [
            'office_id' => null,
        ]);
    }
}
