<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMART-BA - Smart & Green Campus UIN Palopo</title>
    
    <link rel="icon" href="assets/logo_uin.png" type="image/png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --green-primary: #00A86B; 
            --green-dark: #008F5A;
            --green-light: #2ddb90;
            --text-dark: #343a40;
            --text-light: #6c757d;
        }
        html { scroll-behavior: smooth; }
        body { font-family: 'Lato', sans-serif; color: var(--text-dark); }
        h1, h2, h3, h4, h5, h6, .navbar-brand { font-family: 'Montserrat', sans-serif; }
        
        .hero-section { 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            color: white; 
            background: linear-gradient(-45deg, var(--green-primary), var(--green-dark), #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            position: relative;
            overflow: hidden;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .hero-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            text-align: center;
            opacity: 0.1;
        }

        .hero-watermark img {
            width: 350px;
        }

        .hero-watermark .watermark-text {
            display: block;
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .hero-text {
            position: relative;
            z-index: 1;
        }

        .navbar { background-color: transparent; transition: background-color 0.4s ease-out; }
        .navbar.navbar-scrolled {
            background-color: var(--green-dark);
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        }

        .hero-text h1 { font-size: 4.2rem; font-weight: 800; text-shadow: 2px 2px 8px rgba(0,0,0,0.4); }
        .hero-text .lead { font-size: 1.25rem; margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto; }
        .btn-gabung { background-color: #fff; color: var(--green-primary); border: none; padding: 14px 35px; font-weight: 700; font-family: 'Montserrat', sans-serif; border-radius: 50px; transition: all 0.2s; }
        .btn-gabung:hover { background-color: #f0f0f0; transform: translateY(-3px) scale(1.05); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
        .pulse-animation { animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        section { padding: 5rem 0; }
        .section-title { text-align: center; margin-bottom: 4rem; font-weight: 800; }
        
        .feature-card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; height: 100%; }
        .feature-card:hover { transform: translateY(-10px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }
        .feature-icon { font-size: 3rem; color: var(--green-primary); transition: all 0.3s ease; }
        .feature-card:hover .feature-icon { transform: scale(1.2); color: var(--green-dark); }

        .footer { background-color: #212529; }
        .brand-title { line-height: 1; }
        .brand-subtitle { font-size: 0.7rem; font-weight: 400; font-family: 'Lato', sans-serif; letter-spacing: 0.5px; }

        /* ### CSS BARU: Untuk Bagian Kontak ### */
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        .contact-item .icon {
            font-size: 1.5rem;
            color: var(--green-primary);
            margin-right: 1rem;
            width: 30px;
        }
        .contact-item .info h5 {
            margin-bottom: 0.25rem;
            font-size: 1.1rem;
            font-weight: 700;
        }
        .contact-item .info p {
            margin-bottom: 0;
            color: var(--text-light);
        }
        .social-icons a {
            font-size: 1.8rem;
            color: var(--green-primary);
            margin-right: 1rem;
            transition: color 0.2s;
        }
        .social-icons a:hover {
            color: var(--green-dark);
        }
        .map-container {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/logo_uin.png" alt="Logo UIN Palopo" style="height: 40px;" class="me-3">
                <div>
                    <span class="brand-title d-block">SMART-BA Fakultas Syariah</span>
                    <small class="brand-subtitle d-block">Universitas Islam Negeri Kota Palopo</small>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#beranda">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#profil">Profil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fasilitas">Fasilitas</a></li>
                    <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main id="beranda" class="hero-section">
        <div class="hero-watermark">
            <img src="assets/logo_uin.png" alt="Logo Latar Belakang">
            <span class="watermark-text">Universitas Islam Negeri Palopo</span>
        </div>

        <div class="container text-center">
            <div class="hero-text" data-aos="fade-up">
                <p class="mb-2"><strong>Smart & Green Campus Initiative</strong></p>
                <h1><span id="typed-element"></span></h1>
                <p class="lead">Platform bimbingan akademik digital untuk mendukung efisiensi dan keberlanjutan proses studi di Universitas Islam Negeri Palopo.</p>
                <a href="login.php" class="btn btn-light btn-lg btn-gabung pulse-animation">Masuk ke Sistem</a>
            </div>
        </div>
    </main>

    <section id="profil">
        <div class="container">
            <h2 class="section-title"><i class="bi bi-info-circle-fill me-3"></i>Tentang SMART-BA</h2>
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <img src="assets/dashboard-dosen-mockup.png" class="img-fluid rounded shadow-lg" alt="Dashboard SMART-BA">
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="100">
                    <h3>Inovasi Digital untuk Kampus Cerdas & Hijau</h3>
                    <p class="text-muted">SMART-BA (Smart Bimbingan Akademik) adalah wujud komitmen UIN Palopo menuju Smart & Green Campus. Dengan mendigitalkan proses bimbingan, kami mengurangi penggunaan kertas dan meningkatkan efisiensi interaksi antara dosen dan mahasiswa.</p>
                    <p class="text-muted">Platform ini menyediakan data terpusat, memudahkan monitoring, dan memastikan setiap mahasiswa mendapatkan bimbingan yang terarah dan terdokumentasi dengan baik, kapan saja dan di mana saja.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="fasilitas" class="bg-light">
        <div class="container">
            <h2 class="section-title"><i class="bi bi-star-fill me-3"></i>Fasilitas Unggulan</h2>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="100"><div class="card feature-card text-center p-4"><div class="feature-icon mb-3 mx-auto"><i class="bi bi-journal-text"></i></div><h5 class="card-title">Logbook Digital</h5><p class="card-text text-muted">Catat setiap sesi bimbingan secara online. Baik dosen maupun mahasiswa dapat menambahkan catatan kapan saja.</p></div></div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="200"><div class="card feature-card text-center p-4"><div class="feature-icon mb-3 mx-auto"><i class="bi bi-check2-circle"></i></div><h5 class="card-title">Pelacak Pencapaian</h5><p class="card-text text-muted">Visualisasikan kemajuan studi dari Seminar Proposal hingga Yudisium dengan checklist yang interaktif.</p></div></div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="300"><div class="card feature-card text-center p-4"><div class="feature-icon mb-3 mx-auto"><i class="bi bi-cloud-arrow-up"></i></div><h5 class="card-title">Unggah Dokumen</h5><p class="card-text text-muted">Bagikan file penting seperti draft KRS atau revisi skripsi dengan mudah langsung di dalam sistem.</p></div></div>
                <div class="col-md-6 col-lg-3 d-flex align-items-stretch" data-aos="fade-up" data-aos-delay="400"><div class="card feature-card text-center p-4"><div class="feature-icon mb-3 mx-auto"><i class="bi bi-star-half"></i></div><h5 class="card-title">Evaluasi Dua Arah</h5><p class="card-text text-muted">Dosen dapat mengevaluasi soft skill mahasiswa, dan mahasiswa dapat memberi umpan balik kinerja dosen PA.</p></div></div>
            </div>
        </div>
    </section>

    <section id="kontak">
        <div class="container">
            <h2 class="section-title"><i class="bi bi-headset me-3"></i>Tetap Terhubung Bersama Kami</h2>
            <div class="row g-5">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="contact-item">
                        <div class="icon"><i class="bi bi-telephone-fill"></i></div>
                        <div class="info">
                            <h5>Pusat Aduan</h5>
                            <p>+62821-93362277</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="info">
                            <h5>Alamat</h5>
                            <p>Jalan Agatis, Kelurahan Balandai, Kecamatan Bara, Kota Palopo, Sulawesi Selatan</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="icon"><i class="bi bi-envelope-fill"></i></div>
                        <div class="info">
                            <h5>Alamat Email</h5>
                            <p>kontak@uinpalopo.ac.id</p>
                        </div>
                    </div>
                    <div class="mt-4 social-icons">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="100">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3984.777997992925!2d120.2104923147568!3d-2.921819997905814!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2db1846b02388e63%3A0x629535b91b553c39!2sIAIN%20PALOPO!5e0!3m2!1sen!2sid!4v1678886543210!5m2!1sen!2sid" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer text-white text-center py-3">
        <div class="container">
            <p class="mb-0">© <?= date('Y'); ?> SMART-BA | Inisiatif Smart & Green Campus UIN Palopo.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });
        new Typed('#typed-element', {
            strings: ['SMART Bimbingan', 'Akademik Terpadu'],
            typeSpeed: 70, backSpeed: 40, loop: true, backDelay: 2200
        });

        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    </script>
</body>
</html>