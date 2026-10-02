<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreOAuthClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole('super_admin', 'admin_sdm') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'redirect' => ['required', 'string'],
            'confidential' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Aplikasi Klien',
            'redirect' => 'URL Callback (Redirect URI)',
            'confidential' => 'Tipe Klien Rahasia (Confidential)',
        ];
    }

    /**
     * Parse redirect URIs into an array of sanitized URLs.
     *
     * @return list<string>
     */
    public function getRedirectUris(): array
    {
        $raw = (string) $this->input('redirect', '');
        $parts = preg_split('/[\r\n,]+/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $parts)));
    }
}
