<?php
/**
 * Admin Account Settings & Change Password
 * Azumi Movie Reviews
 */

$pageTitle = "Pengaturan Akun & Ganti Password - Azumi Movie Reviews";
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = getDbConnection();
$adminId = $_SESSION['admin_id'] ?? null;

if (!$adminId) {
    header('Location: login.php');
    exit;
}

// Fetch current admin data from database
$stmt = $pdo->prepare("SELECT id, username, full_name, created_at, password_hash FROM admins WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    logout_admin();
    set_flash_message('danger', 'Akun tidak ditemukan. Silakan login kembali.');
    header('Location: login.php');
    exit;
}

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. CSRF Protection
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Sesi keamanan tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.";
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $oldPassword = $_POST['old_password'] ?? $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? $_POST['confirm_new_password'] ?? '';

        if (empty($fullName)) {
            $fullName = $admin['full_name'];
        }

        // 2. Validate Old Password
        if (empty($oldPassword)) {
            $errors[] = "Password lama wajib diisi.";
        } elseif (!password_verify($oldPassword, $admin['password_hash'])) {
            $errors[] = "Password lama yang Anda masukkan tidak sesuai.";
        }

        // 3. Validate New Password
        if (empty($newPassword)) {
            $errors[] = "Password baru wajib diisi.";
        } elseif (mb_strlen($newPassword) < 6) {
            $errors[] = "Password baru minimal harus terdiri dari 6 karakter.";
        }

        // 4. Validate Confirmation
        if ($newPassword !== $confirmPassword) {
            $errors[] = "Konfirmasi password baru tidak cocok.";
        }

        // 5. If Valid, update DB securely with password_hash()
        if (empty($errors)) {
            try {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("
                    UPDATE admins 
                    SET password_hash = :hash, full_name = :full_name 
                    WHERE id = :id
                ");
                $upd->execute([
                    ':hash'      => $newHash,
                    ':full_name' => $fullName,
                    ':id'        => $adminId
                ]);

                // Update session info
                $_SESSION['admin_name'] = $fullName;
                
                // Refresh local record
                $admin['full_name'] = $fullName;
                $admin['password_hash'] = $newHash;

                set_flash_message('success', 'Password dan informasi akun berhasil diperbarui! Password baru sudah tersimpan di database dan dapat digunakan untuk login berikutnya.');
                header('Location: account.php');
                exit;
            } catch (Exception $e) {
                $errors[] = "Terjadi kesalahan sistem saat memperbarui password: " . $e->getMessage();
            }
        }
    }
}
?>

<main class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 22px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h1 style="font-family: var(--font-heading); font-size: 1.5rem; color: var(--dark); margin-bottom: 4px;">
                Pengaturan Akun & Ganti Password
            </h1>
            <p style="color: var(--lighter-grey); font-size: 0.85rem;">
                Kelola kredensial akun administrator Anda secara aman. Kata sandi dienkripsi dengan standar industri (bcrypt).
            </p>
        </div>
        <div>
            <a href="index.php" class="btn-action btn-view" style="padding: 8px 14px; text-decoration: none; font-size: 0.85rem;">
                &larr; Kembali ke Moderasi
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert-box alert-danger" style="margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="account-grid">
        <!-- Left: Account Profile Info -->
        <div class="table-card" style="padding: 24px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--dark); margin-bottom: 16px; border-bottom: 2px solid var(--accent); padding-bottom: 8px;">
                Informasi Akun
            </h3>
            
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: var(--dark); color: var(--white); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem;">
                    <?= strtoupper(substr($admin['username'], 0, 1)) ?>
                </div>
                <div>
                    <strong style="color: var(--dark); font-size: 1rem; display: block;">
                        <?= e($admin['full_name']) ?>
                    </strong>
                    <span style="font-size: 0.82rem; color: var(--lighter-grey);">
                        @<?= e($admin['username']) ?>
                    </span>
                </div>
            </div>

            <div style="font-size: 0.85rem; line-height: 1.8; color: var(--secondary-text);">
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--light-grey);">
                    <span style="color: var(--lighter-grey);">Role:</span>
                    <span class="admin-badge-role">ADMINISTRATOR</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--light-grey);">
                    <span style="color: var(--lighter-grey);">Username:</span>
                    <strong><?= e($admin['username']) ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--light-grey);">
                    <span style="color: var(--lighter-grey);">Terdaftar:</span>
                    <span><?= format_date($admin['created_at']) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0;">
                    <span style="color: var(--lighter-grey);">Keamanan:</span>
                    <span style="color: #2E7D32; font-weight: 600;">Enkripsi Aktif</span>
                </div>
            </div>

            <div style="margin-top: 20px; padding: 12px; background: #FFFDE7; border-left: 3px solid #FBC02D; font-size: 0.78rem; color: #5D4037; border-radius: 3px;">
                <strong>Catatan Keamanan:</strong><br>
                Kata sandi disimpan menggunakan fungsi hash <code>password_hash()</code> dan tidak dapat dibaca oleh siapa pun. Gunakan kombinasi password yang kuat.
            </div>
        </div>

        <!-- Right: Change Password & Settings Form -->
        <div class="table-card" style="padding: 24px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--dark); margin-bottom: 8px;">
                Form Ganti Password
            </h3>
            <p style="color: var(--lighter-grey); font-size: 0.82rem; margin-bottom: 20px;">
                Silakan isi password lama untuk verifikasi, lalu tentukan password baru yang diinginkan.
            </p>

            <form action="account.php" method="POST">
                <?= csrf_field() ?>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="full_name" style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; color: var(--dark);">
                        Nama Lengkap / Redaksi
                    </label>
                    <input type="text" name="full_name" id="full_name" class="form-control" 
                           value="<?= e($admin['full_name']) ?>" required style="width: 100%; padding: 10px; border: 1px solid var(--light-grey); border-radius: 4px;">
                </div>

                <div style="margin: 22px 0 16px; border-top: 1px solid var(--light-grey); padding-top: 16px;">
                    <span style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: var(--accent);">
                        Verifikasi & Password Baru
                    </span>
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="old_password" style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; color: var(--dark);">
                        Password Lama <span style="color: var(--accent);">*</span>
                    </label>
                    <input type="password" name="old_password" id="old_password" class="form-control" 
                           required autocomplete="current-password" placeholder="Masukkan password lama saat ini"
                           style="width: 100%; padding: 10px; border: 1px solid var(--light-grey); border-radius: 4px;">
                </div>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label for="new_password" style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; color: var(--dark);">
                        Password Baru <span style="color: var(--accent);">*</span>
                    </label>
                    <input type="password" name="new_password" id="new_password" class="form-control" 
                           required minlength="6" autocomplete="new-password" placeholder="Minimal 6 karakter"
                           style="width: 100%; padding: 10px; border: 1px solid var(--light-grey); border-radius: 4px;">
                    <small style="color: var(--lighter-grey); font-size: 0.78rem; display: block; margin-top: 4px;">
                        Panjang minimum: 6 karakter. Dianjurkan kombinasi huruf dan angka.
                    </small>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label for="confirm_password" style="display: block; font-weight: 600; font-size: 0.85rem; margin-bottom: 6px; color: var(--dark);">
                        Konfirmasi Password Baru <span style="color: var(--accent);">*</span>
                    </label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" 
                           required minlength="6" autocomplete="new-password" placeholder="Ulangi password baru"
                           style="width: 100%; padding: 10px; border: 1px solid var(--light-grey); border-radius: 4px;">
                </div>

                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <button type="submit" class="btn-submit" style="padding: 10px 24px; font-size: 0.85rem; letter-spacing: 0.5px;">
                        SIMPAN PERUBAHAN & GANTI PASSWORD
                    </button>
                    <a href="index.php" style="color: var(--lighter-grey); font-size: 0.85rem; text-decoration: none;">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
