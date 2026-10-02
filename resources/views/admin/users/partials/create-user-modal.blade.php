<!-- Modal Tambah Sivitas Baru -->
<div class="modal fade" id="modalCreateUser" tabindex="-1" aria-labelledby="modalCreateUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.users.store') }}" method="POST" id="formCreateUser">
                @csrf
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-heading" id="modalCreateUserLabel">
                        <i class="icon-base ti tabler-user-plus me-2 text-primary"></i>Tambah Sivitas Akademika Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <!-- Section: Data Kredensial -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-1">1. Kredensial Akun SSO</h6>
                            <hr class="mt-1 mb-3" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="createEmail" class="form-label fw-semibold text-heading small">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" id="createEmail" name="email" class="form-control" placeholder="nama@univ.ac.id" value="{{ old('email') }}" required />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="createPassword" class="form-label fw-semibold text-heading small">Kata Sandi Awal <span class="text-danger">*</span></label>
                            <input type="password" id="createPassword" name="password" class="form-control" placeholder="Minimal 8 karakter" required />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="createRole" class="form-label fw-semibold text-heading small">Peran Global <span class="text-danger">*</span></label>
                            <select id="createRole" name="role" class="form-select" required>
                                <option value="" disabled selected>Pilih Peran Global</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}" {{ old('role') === $r->name ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $r->name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="createNamaLengkap" class="form-label fw-semibold text-heading small">Nama Lengkap (dengan Gelar) <span class="text-danger">*</span></label>
                            <input type="text" id="createNamaLengkap" name="nama_lengkap" class="form-control" placeholder="Contoh: Dr. Budi Santoso, M.T." value="{{ old('nama_lengkap') }}" required />
                        </div>

                        <!-- Section: Demografi Master Data -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-primary mb-1">2. Profil Master Demografi</h6>
                                <span class="badge bg-label-info small" id="badgeRoleHint">Pilih peran terlebih dahulu</span>
                            </div>
                            <hr class="mt-1 mb-3" />
                        </div>

                        <!-- Sub-Section: Akademik (Khusus Dosen & Mahasiswa) -->
                        <div id="sectionAkademik" class="col-12 d-none">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="createProgramStudi" class="form-label fw-semibold text-heading small">
                                        Program Studi <span class="text-danger">*</span>
                                    </label>
                                    <select id="createProgramStudi" name="program_studi" class="form-select">
                                        <option value="" disabled selected>Pilih Program Studi</option>
                                        @foreach($studyPrograms as $facultyName => $programs)
                                            <optgroup label="{{ $facultyName }}">
                                                @foreach($programs as $key => $name)
                                                    <option value="{{ $key }}" data-faculty="{{ $facultyName }}" {{ old('program_studi') === $key ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="createFakultas" class="form-label fw-semibold text-heading small">Fakultas Terkait</label>
                                    <input type="text" id="createFakultas" name="fakultas" class="form-control bg-light" placeholder="Terisi otomatis sesuai Prodi" readonly />
                                </div>
                            </div>
                        </div>

                        <!-- Sub-Section: Unit Kerja (Khusus Karyawan & Admin) -->
                        <div id="sectionUnitKerja" class="col-12 d-none">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="createUnitKerja" class="form-label fw-semibold text-heading small">
                                        Unit Kerja <span class="text-danger">*</span>
                                    </label>
                                    <select id="createUnitKerja" name="unit_kerja" class="form-select">
                                        <option value="" disabled selected>Pilih Unit Kerja</option>
                                        @foreach($workUnits as $key => $name)
                                            <option value="{{ $key }}" {{ old('unit_kerja') === $key ? 'selected' : '' }}>
                                                {{ $key }} ({{ $name }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Status Akademik / Kepegawaian (Disesuaikan per role) -->
                        <div id="sectionStatusAkademik" class="col-12 col-md-6 d-none">
                            <label for="createStatusAkademik" class="form-label fw-semibold text-heading small">
                                Status Keaktifan / Akademik <span class="text-danger">*</span>
                            </label>
                            <select id="createStatusAkademik" name="status_akademik" class="form-select">
                                <option value="aktif" selected>Aktif</option>
                            </select>
                            <div class="form-text small" id="hintStatusAkademik">Pengguna tetap dapat login SSO dengan status apapun.</div>
                        </div>

                        <!-- Nomor Induk (Auto-Generated) -->
                        <div id="sectionNomorInduk" class="col-12 col-md-6 d-none">
                            <label for="createNomorInduk" class="form-label fw-semibold text-heading small">
                                Nomor Induk (NIM / NIDN / NIPK)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary"><i class="icon-base ti tabler-id"></i></span>
                                <input type="text" id="createNomorInduk" name="nomor_induk" class="form-control bg-light" placeholder="Dibuat otomatis oleh sistem" readonly />
                            </div>
                            <div class="form-text text-success small"><i class="icon-base ti tabler-sparkles me-1"></i>Otomatis dihitung berdasarkan algoritma kode prodi/unit.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Data Sivitas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('createRole');
        const prodiSelect = document.getElementById('createProgramStudi');
        const fakultasInput = document.getElementById('createFakultas');
        const unitSelect = document.getElementById('createUnitKerja');
        const statusSelect = document.getElementById('createStatusAkademik');
        const nomorIndukInput = document.getElementById('createNomorInduk');

        const sectionAkademik = document.getElementById('sectionAkademik');
        const sectionUnitKerja = document.getElementById('sectionUnitKerja');
        const sectionStatus = document.getElementById('sectionStatusAkademik');
        const sectionNomorInduk = document.getElementById('sectionNomorInduk');
        const badgeRoleHint = document.getElementById('badgeRoleHint');

        const statusOptions = @json($statusOptions ?? []);

        function updateFormSections() {
            const role = roleSelect.value;
            if (!role) return;

            badgeRoleHint.textContent = 'Konfigurasi untuk: ' + role.toUpperCase();
            sectionStatus.classList.remove('d-none');
            sectionNomorInduk.classList.remove('d-none');

            // Populate status options for role
            statusSelect.innerHTML = '';
            const opts = statusOptions[role] || { aktif: 'Aktif' };
            for (const [val, label] of Object.entries(opts)) {
                const optEl = document.createElement('option');
                optEl.value = val;
                optEl.textContent = label;
                if (val === 'aktif') optEl.selected = true;
                statusSelect.appendChild(optEl);
            }

            if (role === 'dosen' || role === 'mahasiswa') {
                sectionAkademik.classList.remove('d-none');
                sectionUnitKerja.classList.add('d-none');
                prodiSelect.required = true;
                unitSelect.required = false;
                unitSelect.value = '';
            } else {
                sectionAkademik.classList.add('d-none');
                sectionUnitKerja.classList.remove('d-none');
                prodiSelect.required = false;
                unitSelect.required = true;
                prodiSelect.value = '';
                fakultasInput.value = '';
            }

            updateNomorIndukPreview();
        }

        function updateFacultyFromProdi() {
            const selectedOpt = prodiSelect.options[prodiSelect.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.faculty) {
                fakultasInput.value = selectedOpt.dataset.faculty;
            } else {
                fakultasInput.value = '';
            }
            updateNomorIndukPreview();
        }

        function updateNomorIndukPreview() {
            const role = roleSelect.value;
            const year = new Date().getFullYear();

            if (role === 'mahasiswa') {
                const prodi = prodiSelect.value;
                const nimCodes = { 'Informatika': '1101', 'Sistem Informasi': '1102', 'Teknik Elektro': '1103', 'Manajemen': '1201', 'Akuntansi': '1202', 'Ilmu Komunikasi': '1301', 'Desain Komunikasi Visual': '1302' };
                const code = nimCodes[prodi] || '1000';
                const prefix = String(year).slice(-2) + code;
                nomorIndukInput.value = prefix + 'XXXX (Otomatis)';
            } else if (role === 'dosen') {
                const prodi = prodiSelect.value;
                const dsnCodes = { 'Informatika': '101', 'Sistem Informasi': '102', 'Teknik Elektro': '103', 'Manajemen': '201', 'Akuntansi': '202', 'Ilmu Komunikasi': '301', 'Desain Komunikasi Visual': '302' };
                const code = dsnCodes[prodi] || '100';
                nomorIndukInput.value = year + code + '1XXXX (Otomatis)';
            } else if (role) {
                const unit = unitSelect.value;
                const unitCodes = { 'Biro SDM': '501', 'Pusat TIK': '502', 'Biro Akademik': '503', 'Biro Keuangan': '504', 'LPPM': '505', 'Rektorat': '506' };
                const code = unitCodes[unit] || '500';
                nomorIndukInput.value = year + code + 'XXXX (Otomatis)';
            }
        }

        if (roleSelect) roleSelect.addEventListener('change', updateFormSections);
        if (prodiSelect) prodiSelect.addEventListener('change', updateFacultyFromProdi);
        if (unitSelect) unitSelect.addEventListener('change', updateNomorIndukPreview);

        if (roleSelect && roleSelect.value) {
            updateFormSections();
        }
    });
</script>
