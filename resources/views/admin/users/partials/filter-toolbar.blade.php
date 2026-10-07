<!-- Filter & Search Toolbar Card -->
<div class="card bg-white border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
            <!-- Search Keyword -->
            <div class="col-12 col-md-4">
                <label for="filterSearch" class="form-label fw-semibold text-heading small">Pencarian Sivitas</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="icon-base ti tabler-search"></i>
                    </span>
                    <input type="text" id="filterSearch" name="search" class="form-control border-start-0" placeholder="Cari nama, email, NIP/NIDN/NIM..." value="{{ $filters['search'] ?? '' }}" />
                </div>
            </div>

            <!-- Filter Role -->
            <div class="col-6 col-md-2">
                <label for="filterRole" class="form-label fw-semibold text-heading small">Peran</label>
                <select id="filterRole" name="role" class="form-select">
                    <option value="">Semua Peran</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ ($filters['role'] ?? '') === $r->name ? 'selected' : '' }}>
                            {{ ucwords(str_replace('_', ' ', $r->name)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Unit Kerja -->
            <div class="col-6 col-md-2">
                <label for="filterUnit" class="form-label fw-semibold text-heading small">Unit Kerja</label>
                <select id="filterUnit" name="unit" class="form-select">
                    <option value="">Semua Unit</option>
                    @foreach($workUnits as $uId => $uName)
                        <option value="{{ $uId }}" {{ ($filters['unit'] ?? '') == (string) $uId ? 'selected' : '' }}>
                            {{ $uName }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Admin Status -->
            <div class="col-6 col-md-2">
                <label for="filterIsAdmin" class="form-label fw-semibold text-heading small">Hak Admin</label>
                <select id="filterIsAdmin" name="is_admin" class="form-select">
                    <option value="">Semua</option>
                    <option value="1" {{ ($filters['is_admin'] ?? '') === '1' ? 'selected' : '' }}>Admin Unit</option>
                    <option value="0" {{ ($filters['is_admin'] ?? '') === '0' ? 'selected' : '' }}>Bukan Admin</option>
                </select>
            </div>

            <!-- Filter Status Akun -->
            <div class="col-6 col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="icon-base ti tabler-filter me-1"></i>Filter
                </button>
                @if(!empty($filters['search']) || !empty($filters['role']) || !empty($filters['unit']) || isset($filters['is_admin']) && $filters['is_admin'] !== '' || isset($filters['is_active']) && $filters['is_active'] !== '')
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="icon-base ti tabler-refresh"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>
