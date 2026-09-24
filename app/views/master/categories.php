<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-tags text-primary me-2"></i>Ticket Categories Master</h4>
            <p class="text-muted fs-7 mb-0">Manage query & mail categories (Hold, Release, Penalty, Checklist, Grid, etc.)</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#categoryModal">
                <i class="fas fa-plus-circle me-1"></i>New Category
            </button>
        </div>
    </div>

    <!-- Categories Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 datatable">
                    <thead class="table-light align-middle text-nowrap">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Category Name</th>
                            <th>Category Slug</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th style="width: 90px;" class="text-end text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $idx => $cat): ?>
                        <tr>
                            <td class="text-muted fs-7"><?= $idx + 1 ?></td>
                            <td class="fw-bold text-dark text-nowrap">
                                <i class="fas fa-tag me-2 text-primary"></i><?= htmlspecialchars($cat['category_name']) ?>
                            </td>
                            <td class="font-monospace fs-8 text-secondary text-nowrap"><?= htmlspecialchars($cat['category_slug']) ?></td>
                            <td class="text-nowrap">
                                <span class="badge bg-<?= $cat['status'] === 'Active' ? 'success' : 'secondary' ?>">
                                    <?= $cat['status'] ?>
                                </span>
                            </td>
                            <td class="fs-8 text-muted text-nowrap"><?= date('d M Y', strtotime($cat['created_at'])) ?></td>
                            <td class="text-end text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary btn-edit-cat"
                                            data-id="<?= $cat['id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['category_name'], ENT_QUOTES) ?>"
                                            data-status="<?= $cat['status'] ?>"
                                            title="Edit Category">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="<?= base_url('master/categories') ?>" method="POST" class="d-inline mb-0" onsubmit="return confirm('Delete category <?= htmlspecialchars($cat['category_name'], ENT_QUOTES) ?>?');">
                                        <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add / Edit Category -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="catModalTitle"><i class="fas fa-plus-circle me-2"></i>New Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('master/categories') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                <input type="hidden" name="id" id="cat_id" value="0">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="category_name" id="cat_name" class="form-control" placeholder="e.g. Hold, Release, Penalty, Checklist, Grid" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Status</label>
                        <select name="status" id="cat_status" class="form-select">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4"><i class="fas fa-save me-1"></i>Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-edit-cat').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('catModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Category';
            document.getElementById('cat_id').value = this.dataset.id;
            document.getElementById('cat_name').value = this.dataset.name;
            document.getElementById('cat_status').value = this.dataset.status;

            var modal = new bootstrap.Modal(document.getElementById('categoryModal'));
            modal.show();
        });
    });
});
</script>
