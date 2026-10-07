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
                            <label for="createRole" class="form-label fw-semibold text-heading small">Peran Sivitas <span class="text-danger">*</span></label>
                            <select id="createRole" name="role" class="form-select" required>
                                <option value="" disabled selected>Pilih Peran Sivitas</option>
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
                                <h6 class="fw-bold text-primary mb-1">2. Penempatan & Data Demografi</h6>
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
                                    <select id="createProgramStudi" name="study_program_id" class="form-select">
                                        <option value="" disabled selected>Pilih Program Studi</option>
                                        @foreach($studyPrograms as $facultyName => $programs)
                                            <optgroup label="{{ $facultyName }}">
                                                @foreach($programs as $pId => $pName)
                                                    <option value="{{ $pId }}" data-faculty="{{ $facultyName }}" {{ old('study_program_id') == $pId ? 'selected' : '' }}>
                                                        {{ $pName }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="createFakultas" class="form-label fw-semibold text-heading small">Fakultas Terkait</label>
                                    <input type="text" id="createFakultas" class="form-control bg-light" placeholder="Terisi otomatis sesuai Prodi" readonly />
                                </div>
                            </div>
                        </div>

                        <!-- Sub-Section: Unit Kerja (Khusus Karyawan) -->
                        <div id="sectionUnitKerja" class="col-12 d-none">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="createUnitKerja" class="form-label fw-semibold text-heading small">
                                        Unit Kerja <span class="text-danger">*</span>
                                    </label>
                                    <select id="createUnitKerja" name="work_unit_id" class="form-select">
                                        <option value="" disabled selected>Pilih Unit Kerja</option>
                                        @foreach($workUnits as $uId => $uName)
                                            <option value="{{ $uId }}" {{ old('work_unit_id') == $uId ? 'selected' : '' }}>
                                                {{ $uName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 d-flex align-items-center mt-md-4 pt-md-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="createIsAdmin" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }} />
                                        <label class="form-check-label fw-semibold text-heading small" for="createIsAdmin">
                                            Tetapkan sebagai Admin di Unit Kerja ini
                                        </label>
                                        <div class="form-text small">Memberikan kewenangan admin pada sistem yang terhubung dengan unit ini (misal LPPM -> Knowledge Hub).</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Nomor Induk (Opsional / Manual Override) -->
                        <div id="sectionNomorInduk" class="col-12 col-md-6 d-none">
                            <label for="createNomorInduk" class="form-label fw-semibold text-heading small">
                                Nomor Induk (NIM / NIDN / NIP)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-primary"><i class="icon-base ti tabler-id"></i></span>
                                <input type="text" id="createNomorInduk" name="nomor_induk" class="form-control bg-light" placeholder="Dibuat otomatis jika dikosongkan" value="{{ old('nomor_induk') }}" />
                            </div>
                            <div class="form-text text-success small"><i class="icon-base ti tabler-sparkles me-1"></i>Otomatis dihitung jika dibiarkan kosong.</div>
                        </div>

                        <div class="col-12 col-md-6 d-flex align-items-center mt-md-4 pt-md-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="createIsActive" name="is_active" value="1" checked />
                                <label class="form-check-label fw-semibold text-heading small" for="createIsActive">
                                    Akun Aktif (Dapat Login SSO)
                                </label>
                            </div>
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
        const sectionAkademik = document.getElementById('sectionAkademik');
        const sectionUnitKerja = document.getElementById('sectionUnitKerja');
        const sectionNomorInduk = document.getElementById('sectionNomorInduk');
        const badgeRoleHint = document.getElementById('badgeRoleHint');

        function updateFormSections() {
            const role = roleSelect.value;
            if (!role) return;

            badgeRoleHint.textContent = 'Konfigurasi untuk: ' + role.toUpperCase();
            sectionNomorInduk.classList.remove('d-none');

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
        }

        function updateFacultyFromProdi() {
            const selectedOpt = prodiSelect.options[prodiSelect.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.faculty) {
                fakultasInput.value = selectedOpt.dataset.faculty;
            } else {
                fakultasInput.value = '';
            }
        }

        if (roleSelect) roleSelect.addEventListener('change', updateFormSections);
        if (prodiSelect) prodiSelect.addEventListener('change', updateFacultyFromProdi);

        if (roleSelect && roleSelect.value) {
            updateFormSections();
        }
    });
</script>
