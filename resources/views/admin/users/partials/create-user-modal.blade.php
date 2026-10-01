<!-- Modal Tambah Sivitas Baru -->
<div class="modal fade" id="modalCreateUser" tabindex="-1" aria-labelledby="modalCreateUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.users.store') }}" method="POST">
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
                            <label for="createStatusAkademik" class="form-label fw-semibold text-heading small">Status Akademik</label>
                            <select id="createStatusAkademik" name="status_akademik" class="form-select">
                                <option value="aktif" {{ old('status_akademik') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="studi_lanjut" {{ old('status_akademik') === 'studi_lanjut' ? 'selected' : '' }}>Studi Lanjut</option>
                                <option value="cuti" {{ old('status_akademik') === 'cuti' ? 'selected' : '' }}>Cuti</option>
                                <option value="non_aktif" {{ old('status_akademik') === 'non_aktif' ? 'selected' : '' }}>Non-Aktif</option>
                            </select>
                        </div>

                        <!-- Section: Demografi Master Data -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary mb-1">2. Profil Master Demografi</h6>
                            <hr class="mt-1 mb-3" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="createNamaLengkap" class="form-label fw-semibold text-heading small">Nama Lengkap (dengan Gelar) <span class="text-danger">*</span></label>
                            <input type="text" id="createNamaLengkap" name="nama_lengkap" class="form-control" placeholder="Contoh: Dr. Budi Santoso, M.T." value="{{ old('nama_lengkap') }}" required />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="createNomorInduk" class="form-label fw-semibold text-heading small">Nomor Induk (NIP / NIDN / NIM)</label>
                            <input type="text" id="createNomorInduk" name="nomor_induk" class="form-control" placeholder="Contoh: 198501012010121001" value="{{ old('nomor_induk') }}" />
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="createFakultas" class="form-label fw-semibold text-heading small">Fakultas</label>
                            <input type="text" id="createFakultas" name="fakultas" class="form-control" placeholder="Contoh: Fakultas Teknik" value="{{ old('fakultas') }}" />
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="createProdi" class="form-label fw-semibold text-heading small">Program Studi</label>
                            <input type="text" id="createProdi" name="program_studi" class="form-control" placeholder="Contoh: Teknik Informatika" value="{{ old('program_studi') }}" />
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="createUnitKerja" class="form-label fw-semibold text-heading small">Unit Kerja (Khusus Karyawan)</label>
                            <input type="text" id="createUnitKerja" name="unit_kerja" class="form-control" placeholder="Contoh: Biro SDM" value="{{ old('unit_kerja') }}" />
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
