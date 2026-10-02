<!-- Modal Tambah OAuth2 Client Baru -->
<div class="modal fade" id="modalCreateClient" tabindex="-1" aria-labelledby="modalCreateClientLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-heading" id="modalCreateClientLabel">
                        <i class="icon-base ti tabler-apps me-2 text-primary"></i>Daftarkan Aplikasi Klien SSO Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="clientName" class="form-label fw-semibold text-heading small">
                            Nama Aplikasi Klien <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="clientName" name="name" class="form-control" placeholder="Contoh: Knowledge Hub, SIAKAD Mobile, E-Library" value="{{ old('name') }}" required />
                        <div class="form-text small">Nama resmi aplikasi yang akan ditampilkan di halaman persetujuan otorisasi pengguna.</div>
                    </div>

                    <div class="mb-3">
                        <label for="clientRedirect" class="form-label fw-semibold text-heading small">
                            URL Callback / Redirect URI <span class="text-danger">*</span>
                        </label>
                        <textarea id="clientRedirect" name="redirect" class="form-control" rows="3" placeholder="http://localhost:8001/callback, https://oauth.pstmn.io/v1/callback" required>{{ old('redirect') }}</textarea>
                        <div class="form-text small">URL tujuan pengembalian kode otorisasi setelah pengguna menyetujui login. Pisahkan dengan koma atau baris baru jika ada lebih dari 1 URL.</div>
                    </div>

                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" id="clientConfidential" name="confidential" value="1" checked />
                        <label class="form-check-label fw-semibold text-heading small" for="clientConfidential">
                            Aplikasi Rahasia (Memiliki Client Secret Server-Side)
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Daftarkan Klien</button>
                </div>
            </form>
        </div>
    </div>
</div>
