<!-- Dynamic Modal: Manage Administrative Roles -->
<div class="modal fade" id="modalManageRoles" tabindex="-1" aria-labelledby="modalManageRolesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content border-0 shadow">
            <form id="formManageRoles" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title fw-bold text-heading" id="modalManageRolesLabel">
                        <i class="icon-base ti tabler-user-shield me-2 text-primary"></i>Kelola Peran Admin: <span id="modalRolesUserName" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <div class="small text-muted mb-1">Identitas Sivitas (Peran Utama):</div>
                        <div id="modalRolesCivitasBadge" class="d-inline-block"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-heading small mb-2">
                            Pemberian Hak Akses & Peran Administratif:
                        </label>
                        <div class="d-flex flex-column gap-2 p-3 bg-light rounded-3 border">
                            @foreach($adminRoles as $ar)
                                <div class="form-check">
                                    <input class="form-check-input admin-role-checkbox"
                                           type="checkbox"
                                           name="admin_roles[]"
                                           value="{{ $ar->name }}"
                                           id="manageRole_{{ $ar->name }}" />
                                    <label class="form-check-label fw-medium small" for="manageRole_{{ $ar->name }}">
                                        {{ ucwords(str_replace('_', ' ', $ar->name)) }}
                                        <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $ar->description }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="form-text small mt-2">
                            Centang untuk memberikan peran admin, atau hilangkan centang untuk mencabut hak akses tanpa memengaruhi status kepegawaian.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Hak Akses</button>
                </div>
            </form>
        </div>
    </div>
</div>
