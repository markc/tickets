<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_.,!?()]+$/', // Only allow safe characters
            ],
            'content' => [
                'required',
                'string',
                'max:10000', // Limit content length
                'regex:/^[^<>]*$/', // Prevent HTML injection
            ],
            'office_id' => 'required|exists:offices,id',
            'ticket_priority_id' => 'required|exists:ticket_priorities,id',
            'attachments' => 'nullable|array|max:5', // Max 5 files
            'attachments.*' => [
                'nullable',
                File::types(['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'txt'])
                    ->max(10 * 1024), // 10MB max
            ],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'subject.regex' => 'The subject contains invalid characters.',
            'content.regex' => 'The content contains invalid characters.',
            'content.max' => 'The content is too long (maximum 10,000 characters).',
            'attachments.max' => 'You can upload a maximum of 5 files.',
            'attachments.*.max' => 'Each file must be no larger than 10MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'subject' => strip_tags($this->subject ?? ''),
            'content' => strip_tags($this->content ?? ''),
        ]);
    }
}
