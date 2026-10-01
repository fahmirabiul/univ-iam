<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nomor_induk' => ['nullable', 'string', 'max:50', 'unique:user_profiles,nomor_induk'],
            'unit_kerja' => ['nullable', 'string', 'max:255'],
            'fakultas' => ['nullable', 'string', 'max:255'],
            'program_studi' => ['nullable', 'string', 'max:255'],
            'status_akademik' => ['nullable', 'string', 'in:aktif,cuti,studi_lanjut,non_aktif'],
            'is_active' => ['sometimes', 'boolean'],
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
            'email' => 'Alamat Email',
            'password' => 'Kata Sandi',
            'role' => 'Peran Pengguna',
            'nama_lengkap' => 'Nama Lengkap',
            'nomor_induk' => 'Nomor Induk (NIP/NIDN/NIM)',
            'unit_kerja' => 'Unit Kerja',
            'fakultas' => 'Fakultas',
            'program_studi' => 'Program Studi',
            'status_akademik' => 'Status Akademik',
            'is_active' => 'Status Akun Aktif',
        ];
    }
}
