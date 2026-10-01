@props(['role' => 'user'])

@php
    $normalized = strtolower((string) $role);
    $config = match ($normalized) {
        'super_admin' => ['class' => 'bg-label-primary', 'label' => 'Super Admin', 'icon' => 'tabler-shield-check'],
        'admin_sdm' => ['class' => 'bg-label-primary', 'label' => 'Admin SDM', 'icon' => 'tabler-user-cog'],
        'dosen' => ['class' => 'bg-label-info', 'label' => 'Dosen', 'icon' => 'tabler-certificate'],
        'mahasiswa' => ['class' => 'bg-label-success', 'label' => 'Mahasiswa', 'icon' => 'tabler-user'],
        'karyawan' => ['class' => 'bg-label-warning', 'label' => 'Karyawan', 'icon' => 'tabler-briefcase'],
        default => ['class' => 'bg-label-secondary', 'label' => ucwords(str_replace('_', ' ', $role)), 'icon' => 'tabler-user-circle'],
    };
@endphp

<span class="badge {{ $config['class'] }} d-inline-flex align-items-center gap-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
    <i class="icon-base ti {{ $config['icon'] }}" style="font-size: 0.85rem;"></i>
    <span>{{ $config['label'] }}</span>
</span>
