<!-- Single Dynamic Modal: Update Academic Status & Account State -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1" aria-labelledby="modalUpdateStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content border-0 shadow">
            <form id="formUpdateStatus" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-heading" id="modalUpdateStatusLabel">
                        Ubah Status Sivitas: <span id="modalStatusUserName" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="modalStatusAkademik" class="form-label fw-semibold text-heading small">
                            Status Akademik / Kepegawaian <span class="text-danger">*</span>
                        </label>
                        <select id="modalStatusAkademik" name="status_akademik" class="form-select" required>
                            <option value="aktif">Aktif</option>
                            <option value="studi_lanjut">Studi Lanjut</option>
                            <option value="cuti">Cuti</option>
                            <option value="non_aktif">Non-Aktif</option>
                        </select>
                        <div class="form-text small">Perubahan status ini akan otomatis disinkronkan ke seluruh sistem terhubung (Knowledge Hub, dsb.) via Redis.</div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="modalStatusIsActive" name="is_active" value="1" />
                        <label class="form-check-label fw-semibold text-heading small" for="modalStatusIsActive">
                            Akun Dapat Mengakses Sistem SSO (Aktif)
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan & Siarkan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
