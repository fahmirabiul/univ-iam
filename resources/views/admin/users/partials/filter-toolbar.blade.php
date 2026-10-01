<!-- Filter & Search Toolbar Card -->
<div class="card bg-white border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
            <!-- Search Keyword -->
            <div class="col-12 col-md-5">
                <label for="filterSearch" class="form-label fw-semibold text-heading small">Pencarian Sivitas</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="icon-base ti tabler-search"></i>
                    </span>
                    <input type="text" id="filterSearch" name="search" class="form-control border-start-0" placeholder="Cari nama, email, NIP, NIM, atau fakultas..." value="{{ $filters['search'] ?? '' }}" />
                </div>
            </div>

            <!-- Filter Role -->
            <div class="col-6 col-md-3">
                <label for="filterRole" class="form-label fw-semibold text-heading small">Filter Peran Global</label>
                <select id="filterRole" name="role" class="form-select">
                    <option value="">Semua Peran</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ ($filters['role'] ?? '') === $r->name ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $r->name)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div class="col-6 col-md-2">
                <label for="filterStatus" class="form-label fw-semibold text-heading small">Status Akademik</label>
                <select id="filterStatus" name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ ($filters['status'] ?? '') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="studi_lanjut" {{ ($filters['status'] ?? '') === 'studi_lanjut' ? 'selected' : '' }}>Studi Lanjut</option>
                    <option value="cuti" {{ ($filters['status'] ?? '') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                    <option value="non_aktif" {{ ($filters['status'] ?? '') === 'non_aktif' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="icon-base ti tabler-filter me-1"></i>Filter
                </button>
                @if(!empty($filters['search']) || !empty($filters['role']) || !empty($filters['status']))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="icon-base ti tabler-refresh"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
