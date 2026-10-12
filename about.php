<?php
/**
 * About Us Page
 * Azumi Movie Reviews
 */

$pageTitle = "About Us - Azumi Movie Reviews";
require_once __DIR__ . '/includes/header.php';
?>

<main>
    <div class="container">
        <section class="about-hero">
            <span class="tagline">THE STORY BEHIND THE SCREEN</span>
            <h1>About Azumi Movie Reviews</h1>
        </section>

        <div class="about-card">
            <div class="author-box">
                <img src="assets/images/writer.png" alt="Writer Portrait" class="author-photo">
                <div class="author-details">
                    <span class="author-name">Halo, Saya Penulis di Balik Azumi</span>
                    <p class="author-bio">Seorang pencinta sinema tegar yang mendedikasikan waktu untuk mengeksplorasi estetika, emosi, dan kedalaman narasi yang ditawarkan oleh industri film Asia.</p>
                    <div class="author-tags">
                        <span class="tag-pill">Slice of Life Fan</span>
                        <span class="tag-pill">Drama Enthusiast</span>
                    </div>
                </div>
            </div>

            <p class="story-text">Kecintaan saya pada sinema Asia bermula dari kekaguman terhadap bagaimana para sutradara Timur mampu menangkap detail-detail kecil dalam kehidupan sehari-hari menjadi sebuah mahakarya yang emosional. Dari ritme lambat film-film <em>slice-of-life</em> Jepang yang menenangkan hingga melodrama mendalam dari berbagai penjuru Asia.</p>
            <p class="story-text">Melalui <strong>Azumi Movie Reviews</strong>, saya ingin menyediakan wadah apresiasi yang jujur, rapi, dan mendalam, berfokus penuh pada eksplorasi sinema Asia statis yang sarat makna tanpa diselimuti oleh gangguan tautan komersial.</p>
        </div>

        <section class="about-editorial-grid">
            <div class="editorial-section">
                <h3>Siapa Kami</h3>
                <p>Azumi Movie Reviews didirikan pada tahun 2023 dengan visi untuk menjadi sumber terpercaya bagi para pecinta film yang mencari ulasan mendalam dan perspektif editorial yang unik. Berawal dari komunitas kecil penggemar film independen, kami berkembang menjadi platform yang dikenal karena analisis kritis dan gaya penulisan yang elegan.</p>
            </div>
            <div class="editorial-section">
                <h3>Tujuan Kami</h3>
                <p>Tujuan utama kami adalah memberikan ulasan film yang jujur, komprehensif, dan menggugah pikiran. Kami berupaya untuk menyoroti tidak hanya film-film populer, tetapi juga karya-karya sinema independen, internasional, dan eksperimental yang sering luput dari perhatian media mainstream. Kami percaya bahwa setiap film memiliki cerita untuk diceritakan, dan setiap cerita layak untuk dieksplorasi.</p>
            </div>
            <div class="editorial-section">
                <h3>Fokus Ulasan</h3>
                <p>Kami mendedikasikan ruang utama kami untuk eksplorasi sinema alternatif Asia. Mulai dari karya-karya pelopor gerakan gelombang baru (New Wave), karya visual puitis dari sutradara kontemporer, hingga sinema slice-of-life yang tenang dan mendalam. Fokus utama kami adalah menghadirkan analisis yang melampaui sekadar sinopsis, melainkan menggali keindahan visual dan kedalaman narasi.</p>
            </div>
        </section>

        <!-- ===== Contact Section ===== -->
        <section class="about-contact">
            <div class="about-contact-head">
                <span class="tagline">GET IN TOUCH</span>
                <h2>Hubungi Kami</h2>
                <p>Punya pertanyaan, masukan, atau ingin bekerja sama dengan Azumi Movie Reviews? Sapa kami lewat salah satu kanal di bawah.</p>
            </div>

            <div class="about-contact-grid">
                <a href="mailto:azumimov.rev2420@gmail.com" class="about-contact-card">
                    <span class="about-contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M3 7l9 6 9-6"/>
                        </svg>
                    </span>
                    <span class="about-contact-text">
                        <span class="label">Email</span>
                        <span class="value">azumimov.rev2420@gmail.com</span>
                    </span>
                </a>

                <a href="https://www.instagram.com/azumireview_inn" target="_blank" rel="noopener noreferrer" class="about-contact-card">
                    <span class="about-contact-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="5"/>
                            <circle cx="12" cy="12" r="4"/>
                            <circle cx="17.5" cy="6.5" r="0.9" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="about-contact-text">
                        <span class="label">Instagram</span>
                        <span class="value">@azumireview_inn</span>
                    </span>
                </a>
            </div>
        </section>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>