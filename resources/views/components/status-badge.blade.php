@props(['status' => null])

@php
    $normalized = strtolower((string) $status);
    $config = match ($normalized) {
        'aktif' => ['class' => 'bg-label-success', 'label' => 'Aktif', 'icon' => 'tabler-circle-check'],
        'studi_lanjut' => ['class' => 'bg-label-info', 'label' => 'Studi Lanjut', 'icon' => 'tabler-school'],
        'cuti' => ['class' => 'bg-label-warning', 'label' => 'Cuti', 'icon' => 'tabler-clock-pause'],
        'non_aktif' => ['class' => 'bg-label-danger', 'label' => 'Non-Aktif', 'icon' => 'tabler-circle-x'],
        default => ['class' => 'bg-label-secondary', 'label' => $status ? ucwords(str_replace('_', ' ', $status)) : 'Tidak Diketahui', 'icon' => 'tabler-help-circle'],
    };
@endphp

<span class="badge {{ $config['class'] }} d-inline-flex align-items-center gap-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">
    <i class="icon-base ti {{ $config['icon'] }}" style="font-size: 0.85rem;"></i>
    <span>{{ $config['label'] }}</span>
</span>
