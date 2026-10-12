<?php
/**
 * Festivals Page
 * Azumi Movie Reviews
 */

$pageTitle = "Film Festivals - Azumi Movie Reviews";
require_once __DIR__ . '/includes/header.php';
?>

<main>
    <section class="festivals-page">
        <div class="container">
            <h1>Film Festivals</h1>
            <p>Informasi dan liputan mengenai festival film internasional dan independen Asia akan segera hadir. Nantikan update liputan kurasi dari kami!</p>
            
            <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div style="background-color: var(--bg-color); border-radius: 8px; padding: 20px; border-left: 4px solid var(--accent);">
                    <h4 style="font-family: var(--font-heading); font-size: 1.1rem; margin-bottom: 8px;">A Fan Fest 2026 (JF 2026)</h4>
                    <p style="text-align: left; font-size: 0.88rem; margin-bottom: 0;">Panggilan terbuka untuk film-film independen Asia tradisionil & VR. Berlangsung Musim Panas 2026.</p>
                </div>
                <div style="background-color: var(--bg-color); border-radius: 8px; padding: 20px; border-left: 4px solid var(--accent);">
                    <h4 style="font-family: var(--font-heading); font-size: 1.1rem; margin-bottom: 8px;">Bishkek International Film Festival</h4>
                    <p style="text-align: left; font-size: 0.88rem; margin-bottom: 0;">Perayaan sinema Asia Tengah dengan sorotan khusus sutradara muda generasi baru.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
