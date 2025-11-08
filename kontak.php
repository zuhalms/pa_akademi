<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - SMART-BA UIN Palopo</title>
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
                    <li class="nav-item"><a class="nav-link" href="fasilitas.php">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link active" href="kontak.php">Kontak</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <header class="page-header">
        <div class="container" data-aos="fade-up">
            <h1>Hubungi Kami</h1>
            <p class="lead">Kami siap membantu. Kirimkan pertanyaan atau masukan Anda.</p>
        </div>
    </header>

    <section id="kontak">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8" data-aos="fade-up">
                    <div class="card p-4 p-md-5 shadow-sm">
                        <form>
                            <div class="mb-3"><input type="text" class="form-control" placeholder="Masukkan nama" required></div>
                            <div class="mb-3"><input type="email" class="form-control" placeholder="Masukkan email" required></div>
                            <div class="mb-3"><textarea class="form-control" rows="5" placeholder="Tulis pesan Anda..." required></textarea></div>
                            <div class="text-center"><button type="submit" class="btn btn-primary btn-lg" style="background-color: var(--green-primary); border-color: var(--green-primary);">Kirim Pesan</button></div>
                        </form>
                    </div>
                </div>
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