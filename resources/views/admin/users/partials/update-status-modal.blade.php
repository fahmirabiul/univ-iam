<!-- Modal: Update Status Akun & Hak Admin -->
<div class="modal fade" id="modalUpdateStatus" tabindex="-1" aria-labelledby="modalUpdateStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content border-0 shadow">
            <form id="formUpdateStatus" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-heading" id="modalUpdateStatusLabel">
                        Ubah Status: <span id="modalStatusUserName" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="modalStatusIsActive" name="is_active" value="1" />
                        <label class="form-check-label fw-semibold text-heading small" for="modalStatusIsActive">
                            Akun Aktif (Dapat Login SSO)
                        </label>
                        <div class="form-text small">Jika dinonaktifkan, pengguna tidak dapat login ke SSO.</div>
                    </div>

                    <div class="form-check form-switch mt-3" id="modalStatusIsAdminContainer">
                        <input class="form-check-input" type="checkbox" id="modalStatusIsAdmin" name="is_admin" value="1" />
                        <label class="form-check-label fw-semibold text-heading small" for="modalStatusIsAdmin">
                            Hak Admin Unit Kerja
                        </label>
                        <div class="form-text small">Memberikan wewenang admin pada sistem unit terkait (misal LPPM -> Knowledge Hub).</div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
