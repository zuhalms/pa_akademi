<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fasilitas - SMART-BA UIN Palopo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --green-primary: #00A86B; --text-dark: #343a40; }
        body { font-family: 'Lato', sans-serif; color: var(--text-dark); }
        h1, h2, h3, h4, h5, h6, .navbar-brand { font-family: 'Montserrat', sans-serif; }
        .navbar { background-color: #212529; }
        .page-header {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('assets/uin-palopo-bg.jpg');
            background-size: cover; background-position: center;
            padding: 6rem 0; color: white; text-align: center;
        }
        .page-header h1 { font-weight: 800; }
        section { padding: 5rem 0; }
        .section-title { text-align: center; margin-bottom: 4rem; font-weight: 800; }
        .feature-card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; }
        .feature-card:hover { transform: translateY(-10px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .feature-icon { font-size: 3rem; color: var(--green-primary); }
        .footer { background-color: #212529; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">SMART-BA</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="profil_umum.php">Profil</a></li>
                    <li class="nav-item"><a class="nav-link active" href="fasilitas.php">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <header class="page-header">
        <div class="container" data-aos="fade-up">
            <h1>Fasilitas Unggulan</h1>
            <p class="lead">Dirancang untuk kemudahan dan efektivitas proses bimbingan Anda.</p>
        </div>
    </header>

    <section id="fasilitas" class="bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100"><div class="card feature-card text-center p-4"><div class="feature-icon mb-3 mx-auto"><i class="bi bi-journal-text"></i></div><h5 class="card-title">Logbook Digital</h5><p class="card-text text-muted">Catat setiap sesi bimbingan secara online. Baik dosen maupun mahasiswa dapat menambahkan catatan kapan saja.</p></div></div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200"><div class="card feature-card text-center p-4"><div class="feature-icon mb-3 mx-auto"><i class="bi bi-check2-circle"></i></div><h5 class="card-title">Pelacak Pencapaian</h5><p class="card-text text-muted">Visualisasikan kemajuan studi dari Seminar Proposal hingga Yudisium dengan checklist yang interaktif.</p></div></div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="300"><div class="card feature-card text-center p-4"><div class="feature-icon mb-3 mx-auto"><i class="bi bi-cloud-arrow-up"></i></div><h5 class="card-title">Unggah Dokumen</h5><p class="card-text text-muted">Bagikan file penting seperti draft KRS atau revisi skripsi dengan mudah langsung di dalam sistem.</p></div></div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400"><div class="card feature-card text-center p-4"><div class="feature-icon mb-3 mx-auto"><i class="bi bi-star-half"></i></div><h5 class="card-title">Evaluasi Dua Arah</h5><p class="card-text text-muted">Dosen dapat mengevaluasi soft skill mahasiswa, dan mahasiswa dapat memberi umpan balik kinerja dosen PA.</p></div></div>
            </div>
        </div>
    </section>

    <footer class="footer text-white text-center py-3">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y'); ?> SMART-BA | Inisiatif Smart & Green Campus UIN Palopo.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>