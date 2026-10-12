<?php
/**
 * Admin Dashboard - Submissions Management
 * Azumi Movie Reviews
 */

// Normalize request path if accessed as /admin without trailing slash
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if ($requestPath && substr($requestPath, -1) !== '/' && basename($requestPath) === 'admin') {
    $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: ' . $requestPath . '/' . $queryString, true, 301);
    exit;
}

$pageTitle = "Dashboard Pengajuan Film - Azumi Movie Reviews";
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

// 1. Fetch Summary Counts
$countStmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM film_submissions
");
$stats = $countStmt->fetch() ?: ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];

$upcomingCount = (int) $pdo->query("SELECT COUNT(*) FROM upcoming_films")->fetchColumn();

// 2. Filter & Search Query
$filterStatus = $_GET['status'] ?? 'all';
$searchQuery = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM film_submissions WHERE 1=1";
$params = [];

if (in_array($filterStatus, ['pending', 'approved', 'rejected'], true)) {
    $sql .= " AND status = :status";
    $params[':status'] = $filterStatus;
}

if (!empty($searchQuery)) {
    $sql .= " AND (title LIKE :q1 OR author_name LIKE :q2 OR content LIKE :q3)";
    $term = '%' . $searchQuery . '%';
    $params[':q1'] = $term;
    $params[':q2'] = $term;
    $params[':q3'] = $term;
}

$sql .= " ORDER BY submitted_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$submissions = $stmt->fetchAll();
?>

<main class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 22px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h1 style="font-family: var(--font-heading); font-size: 1.5rem; color: var(--dark); margin-bottom: 4px;">
                Dashboard Moderasi & Publikasi
            </h1>
            <p style="color: var(--lighter-grey); font-size: 0.85rem;">
                Tinjau pengajuan ulasan film dari pengunjung, kurasi teks, dan terbitkan ulasan ke publik.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="account.php" class="btn-submit" style="padding: 9px 16px; font-size: 0.82rem; text-decoration: none; background: #333333;" title="Kelola Akun dan Ganti Password">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Pengaturan Akun / Ganti Password
            </a>
            <a href="upcoming.php" class="btn-submit" style="padding: 9px 16px; font-size: 0.82rem; text-decoration: none;">
                Kelola Film Upcoming (<?= $upcomingCount ?>) &rarr;
            </a>
        </div>
    </div>

    <!-- 1. Stats Grid Cards -->
    <div class="stats-grid">
        <a href="index.php?status=pending" style="text-decoration: none;">
            <div class="stat-card pending">
                <span class="stat-label">Menunggu Moderasi</span>
                <span class="stat-number" style="color: #E65100;"><?= (int)$stats['pending'] ?></span>
            </div>
        </a>
        <a href="index.php?status=approved" style="text-decoration: none;">
            <div class="stat-card approved">
                <span class="stat-label">Diterbitkan (Approved)</span>
                <span class="stat-number" style="color: #2E7D32;"><?= (int)$stats['approved'] ?></span>
            </div>
        </a>
        <a href="index.php?status=rejected" style="text-decoration: none;">
            <div class="stat-card rejected">
                <span class="stat-label">Ditolak (Rejected)</span>
                <span class="stat-number" style="color: #C62828;"><?= (int)$stats['rejected'] ?></span>
            </div>
        </a>
        <a href="upcoming.php" style="text-decoration: none;">
            <div class="stat-card" style="border-top-color: #5C6BC0;">
                <span class="stat-label">Film Upcoming</span>
                <span class="stat-number" style="color: #3949AB;"><?= $upcomingCount ?></span>
            </div>
        </a>
        <a href="index.php" style="text-decoration: none;">
            <div class="stat-card total">
                <span class="stat-label">Total Pengajuan</span>
                <span class="stat-number"><?= (int)$stats['total'] ?></span>
            </div>
        </a>
    </div>

    <!-- 2. Filter & Search Controls -->
    <div class="filter-card">
        <div class="status-filter-pills">
            <a href="index.php<?= !empty($searchQuery) ? '?q=' . urlencode($searchQuery) : '' ?>" 
               class="filter-pill <?= $filterStatus === 'all' ? 'active' : '' ?>">
                Semua (<?= (int)$stats['total'] ?>)
            </a>
            <a href="index.php?status=pending<?= !empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '' ?>" 
               class="filter-pill <?= $filterStatus === 'pending' ? 'active' : '' ?>">
                Pending (<?= (int)$stats['pending'] ?>)
            </a>
            <a href="index.php?status=approved<?= !empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '' ?>" 
               class="filter-pill <?= $filterStatus === 'approved' ? 'active' : '' ?>">
                Approved (<?= (int)$stats['approved'] ?>)
            </a>
            <a href="index.php?status=rejected<?= !empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '' ?>" 
               class="filter-pill <?= $filterStatus === 'rejected' ? 'active' : '' ?>">
                Rejected (<?= (int)$stats['rejected'] ?>)
            </a>
        </div>

        <form method="GET" action="index.php" class="search-form">
            <?php if ($filterStatus !== 'all'): ?>
                <input type="hidden" name="status" value="<?= e($filterStatus) ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Cari judul atau pengirim...">
            <button type="submit" class="btn-filter">Cari</button>
            <?php if (!empty($searchQuery) || $filterStatus !== 'all'): ?>
                <a href="index.php" class="filter-pill" style="padding: 7px 10px; font-size: 0.75rem;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- 3. Submissions Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Poster</th>
                        <th>Judul Film & Penulis</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Status</th>
                        <th>Catatan Admin</th>
                        <th style="text-align: right; min-width: 220px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($submissions)): ?>
                        <?php foreach ($submissions as $row): ?>
                            <tr>
                                <td>
                                    <img src="../<?= e(get_poster_url($row['image_path'])) ?>" 
                                         alt="<?= e($row['title']) ?>" 
                                         class="table-poster-thumb">
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--dark); font-size: 0.95rem; margin-bottom: 2px;">
                                        <a href="submission-view.php?id=<?= $row['id'] ?>" style="color: inherit; text-decoration: none;">
                                            <?= e($row['title']) ?>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--lighter-grey);">
                                        Pengirim: <strong><?= e($row['author_name'] ?: 'Anonim') ?></strong> 
                                        &bull; Paragraf: <?= count_paragraphs($row['content']) ?>
                                    </div>
                                </td>
                                <td style="white-space: nowrap; font-size: 0.85rem;">
                                    <?= format_date($row['submitted_at']) ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= e($row['status']) ?>">
                                        <?= strtoupper(e($row['status'])) ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--secondary-text); max-width: 180px;">
                                    <?= !empty($row['admin_notes']) ? e(mb_strimwidth($row['admin_notes'], 0, 45, '...')) : '<span style="color:var(--lighter-grey);">-</span>' ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <!-- View Details -->
                                        <a href="submission-view.php?id=<?= $row['id'] ?>" class="btn-action btn-view" title="Baca seluruh ulasan">
                                            Detail
                                        </a>

                                        <!-- Edit content -->
                                        <a href="submission-edit.php?id=<?= $row['id'] ?>" class="btn-action btn-edit" title="Sunting ulasan & catatan">
                                            Edit
                                        </a>

                                        <!-- Fast Approve Button -->
                                        <?php if ($row['status'] !== 'approved'): ?>
                                            <form action="submission-action.php" method="POST" style="display: inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn-action btn-approve" title="Setujui dan terbitkan ke website">
                                                    Approve
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Fast Reject Button -->
                                        <?php if ($row['status'] !== 'rejected'): ?>
                                            <form action="submission-action.php" method="POST" style="display: inline;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn-action btn-reject" title="Tolak ulasan ini">
                                                    Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <!-- Delete Button with Confirmation -->
                                        <button type="button" class="btn-action btn-delete" 
                                                onclick="confirmDelete(<?= $row['id'] ?>, '<?= e(addslashes($row['title'])) ?>')">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 45px; color: var(--lighter-grey);">
                                Tidak ada pengajuan ulasan yang ditemukan dengan kriteria filter saat ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Confirmation Modal for Delete -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <h3 class="modal-title">Konfirmasi Hapus Ulasan</h3>
        <p style="font-size: 0.9rem; color: var(--secondary-text); margin-bottom: 12px;">
            Apakah Anda yakin ingin menghapus ulasan: <strong id="deleteItemTitle"></strong>?
        </p>
        <p style="font-size: 0.8rem; color: #C62828;">
            Tindakan ini permanen dan akan menghapus data ulasan dari database beserta file poster terkait.
        </p>

        <form action="submission-action.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteItemId" value="">

            <div class="modal-actions">
                <button type="button" class="btn-action btn-view" onclick="closeDeleteModal()">Batal</button>
                <button type="submit" class="btn-action btn-delete" style="padding: 8px 16px;">Ya, Hapus Permanen</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    document.getElementById("deleteItemId").value = id;
    document.getElementById("deleteItemTitle").textContent = title;
    document.getElementById("deleteModal").classList.add("active");
}

function closeDeleteModal() {
    document.getElementById("deleteModal").classList.remove("active");
}

// Close modal when clicking background overlay
document.getElementById("deleteModal").addEventListener("click", function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
