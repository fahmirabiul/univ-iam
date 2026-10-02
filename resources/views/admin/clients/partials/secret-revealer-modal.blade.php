<!-- Modal Tampilkan Client ID & Secret Baru -->
@if(session('new_client_secret'))
<div class="modal fade show" id="modalSecretRevealer" tabindex="-1" aria-labelledby="modalSecretRevealerLabel" aria-modal="true" style="display: block; background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-label-primary border-bottom py-3">
                <h5 class="modal-title fw-bold text-primary" id="modalSecretRevealerLabel">
                    <i class="icon-base ti tabler-key me-2"></i>Kredensial OAuth2 Client: {{ session('new_client_name') }}
                </h5>
                <button type="button" class="btn-close" onclick="document.getElementById('modalSecretRevealer').style.display='none';" aria-label="Tutup"></button>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-warning border-0 d-flex align-items-start gap-2 mb-4" role="alert">
                    <i class="icon-base ti tabler-alert-triangle fs-5 text-warning flex-shrink-0 mt-1"></i>
                    <div class="small">
                        <strong>Penting:</strong> Salin dan simpan <strong>Client Secret</strong> ini sekarang di konfigurasi <code>.env</code> aplikasi klien Anda. Untuk keamanan, secret ini tidak akan ditampilkan kembali.
                    </div>
                </div>

                <!-- Client ID -->
                <div class="mb-3">
                    <label for="revealedClientId" class="form-label fw-semibold text-heading small">Client ID (UUID)</label>
                    <div class="input-group">
                        <input type="text" id="revealedClientId" class="form-control font-monospace small bg-light" value="{{ session('new_client_id') }}" readonly />
                        <button class="btn btn-outline-primary" type="button" onclick="navigator.clipboard.writeText('{{ session('new_client_id') }}'); this.innerHTML='<i class=\'icon-base ti tabler-check me-1\'></i>Tersalin!';" title="Salin Client ID">
                            <i class="icon-base ti tabler-copy me-1"></i>Salin
                        </button>
                    </div>
                </div>

                <!-- Client Secret -->
                <div class="mb-3">
                    <label for="revealedClientSecret" class="form-label fw-semibold text-heading small">Client Secret</label>
                    <div class="input-group">
                        <input type="text" id="revealedClientSecret" class="form-control font-monospace small bg-light text-danger fw-bold" value="{{ session('new_client_secret') }}" readonly />
                        <button class="btn btn-primary" type="button" onclick="navigator.clipboard.writeText('{{ session('new_client_secret') }}'); this.innerHTML='<i class=\'icon-base ti tabler-check me-1\'></i>Tersalin!';" title="Salin Client Secret">
                            <i class="icon-base ti tabler-copy me-1"></i>Salin Secret
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top pt-3">
                <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="document.getElementById('modalSecretRevealer').style.display='none';">
                    Saya Sudah Menyalinnya
                </button>
            </div>
        </div>
    </div>
</div>
@endif
