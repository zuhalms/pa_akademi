<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Tentukan judul halaman sebelum memanggil header
$page_title = 'Lengkapi Riwayat Akademik';

// Include konfigurasi database (otomatis XAMPP atau InfinityFree)
require_once 'config.php';
require 'templates/header.php';

// Keamanan: Pastikan yang mengakses adalah mahasiswa
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'mahasiswa') {
    header("Location: login.php");
    exit();
}

$nim_mahasiswa_login = $_SESSION['user_id'];
$pesan_sukses = '';

// Validasi input
if (empty($nim_mahasiswa_login)) {
    die('Error: NIM mahasiswa tidak valid');
}

// Proses penyimpanan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ips_values = $_POST['ips'] ?? [];
    $sks_values = $_POST['sks'] ?? [];

    // Gunakan prepared statement untuk INSERT dengan ON DUPLICATE KEY UPDATE
    $stmt = $conn->prepare("
        INSERT INTO riwayat_akademik (nim_mahasiswa, semester, ip_semester, sks_semester) 
        VALUES (?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE 
            ip_semester = VALUES(ip_semester), 
            sks_semester = VALUES(sks_semester)
    ");

    if (!$stmt) {
        die('Error: ' . $conn->error);
    }

    // Proses penyimpanan untuk setiap semester
    for ($i = 1; $i <= 14; $i++) {
        if (isset($ips_values[$i]) && !empty($ips_values[$i]) && 
            isset($sks_values[$i]) && !empty($sks_values[$i])) {
            
            // Validasi dan konversi IP
            $ip = floatval(str_replace(',', '.', $ips_values[$i]));
            $sks = intval($sks_values[$i]);

            // Validasi range
            if ($ip > 0 && $ip <= 4.0 && $sks > 0 && $sks <= 24) {
                $stmt->bind_param("sidi", $nim_mahasiswa_login, $i, $ip, $sks);
                if (!$stmt->execute()) {
                    error_log("Error executing INSERT: " . $stmt->error);
                }
            }
        }
    }
    $stmt->close();

    $pesan_sukses = "Data riwayat akademik Anda telah berhasil diperbarui!";

    // Perhitungan IPK & SKS Otomatis dengan prepared statement
    $stmt_riwayat = $conn->prepare("
        SELECT ip_semester, sks_semester 
        FROM riwayat_akademik 
        WHERE nim_mahasiswa = ? AND ip_semester > 0 AND sks_semester > 0
    ");

    if (!$stmt_riwayat) {
        die('Error: ' . $conn->error);
    }

    $stmt_riwayat->bind_param("s", $nim_mahasiswa_login);
    $stmt_riwayat->execute();
    $result_riwayat = $stmt_riwayat->get_result();

    $total_sks = 0;
    $total_bobot_kali_sks = 0;

    while ($row = $result_riwayat->fetch_assoc()) {
        $total_sks += $row['sks_semester'];
        $total_bobot_kali_sks += ($row['ip_semester'] * $row['sks_semester']);
    }
    $stmt_riwayat->close();

    $ipk_baru = ($total_sks > 0) ? ($total_bobot_kali_sks / $total_sks) : 0;

    // Update IPK dan total SKS di tabel mahasiswa
    $update_stmt = $conn->prepare("
        UPDATE mahasiswa 
        SET ipk = ?, total_sks = ? 
        WHERE nim = ?
    ");

    if (!$update_stmt) {
        die('Error: ' . $conn->error);
    }

    $ipk_formatted = number_format($ipk_baru, 2, '.', '');
    $update_stmt->bind_param("dis", $ipk_formatted, $total_sks, $nim_mahasiswa_login);
    if (!$update_stmt->execute()) {
        error_log("Error updating IPK: " . $update_stmt->error);
    }
    $update_stmt->close();
}

// Ambil data riwayat yang sudah ada dengan prepared statement
$stmt_fetch = $conn->prepare("
    SELECT semester, ip_semester, sks_semester 
    FROM riwayat_akademik 
    WHERE nim_mahasiswa = ?
");

if (!$stmt_fetch) {
    die('Error: ' . $conn->error);
}

$stmt_fetch->bind_param("s", $nim_mahasiswa_login);
$stmt_fetch->execute();
$result_fetch = $stmt_fetch->get_result();

$riwayat_tersimpan = [];
while ($row = $result_fetch->fetch_assoc()) {
    $riwayat_tersimpan[$row['semester']] = $row;
}
$stmt_fetch->close();

// Hitung preview IPK
$total_sks_preview = 0;
$total_bobot_preview = 0;

foreach ($riwayat_tersimpan as $data) {
    $total_sks_preview += $data['sks_semester'];
    $total_bobot_preview += ($data['ip_semester'] * $data['sks_semester']);
}

$ipk_preview = ($total_sks_preview > 0) ? ($total_bobot_preview / $total_sks_preview) : 0;
?>

<style>
    /* ========== IMPROVED RIWAYAT AKADEMIK CSS ========== */
    :root {
        --campus-green: #049D6F;
        --smart-blue: #0d6efd;
        --light-gray: #f8f9fa;
        --gray: #e9ecef;
        --dark-gray: #495057;
        --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
        --card-shadow-hover: 0 8px 30px rgba(0,0,0,0.12);
        --card-radius: 1rem;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }
    
    /* ========== HEADER SECTION ========== */
    .page-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: var(--card-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        border-left: 5px solid var(--campus-green);
        position: relative;
        overflow: hidden;
    }
    
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(4, 157, 111, 0.1) 0%, transparent 70%);
        animation: pulse 8s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }
    
    .page-header h1 {
        color: var(--campus-green);
        font-weight: 800;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
    }
    
    .page-header .subtitle {
        color: var(--dark-gray);
        font-size: 1rem;
        margin-bottom: 0;
        position: relative;
        z-index: 1;
    }
    
    /* ========== STATS PREVIEW CARDS ========== */
    .stats-preview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-preview-card {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        color: white;
        padding: 1.5rem;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        text-align: center;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    
    .stat-preview-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        opacity: 0;
        transition: var(--transition);
    }
    
    .stat-preview-card:hover::before {
        opacity: 1;
        transform: rotate(45deg);
    }
    
    .stat-preview-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(4, 157, 111, 0.3);
    }
    
    .stat-preview-card .icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }
    
    .stat-preview-card .value {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 0.25rem;
    }
    
    .stat-preview-card .label {
        font-size: 0.9rem;
        opacity: 0.95;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    .stat-preview-card.secondary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    /* ========== CARD IMPROVEMENTS ========== */
    .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        overflow: hidden;
    }
    
    .card:hover {
        box-shadow: var(--card-shadow-hover);
    }
    
    .card-body {
        padding: 2rem;
    }
    
    /* ========== TABLE IMPROVEMENTS ========== */
    .table-responsive {
        border-radius: 0.75rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table thead {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
    }
    
    .table thead th {
        color: white;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
        padding: 1.25rem 1rem;
    }
    
    .table tbody tr {
        transition: var(--transition);
        border-bottom: 1px solid #f0f2f5;
    }
    
    .table tbody tr:hover {
        background: linear-gradient(135deg, rgba(4, 157, 111, 0.05) 0%, rgba(4, 157, 111, 0.02) 100%);
        transform: scale(1.01);
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .table tbody td {
        padding: 1.25rem 1rem;
        vertical-align: middle;
        border: none;
    }
    
    .table tbody td:first-child {
        font-weight: 700;
        color: var(--campus-green);
        position: relative;
    }
    
    .table tbody td:first-child::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 60%;
        background: linear-gradient(to bottom, var(--campus-green), #43e97b);
        border-radius: 0 4px 4px 0;
    }
    
    /* ========== FORM IMPROVEMENTS ========== */
    .form-control {
        border-radius: 0.5rem;
        border: 2px solid var(--gray);
        transition: var(--transition);
        padding: 0.75rem 1rem;
        font-weight: 600;
    }
    
    .form-control:focus {
        border-color: var(--campus-green);
        box-shadow: 0 0 0 0.25rem rgba(4, 157, 111, 0.15);
        transform: translateY(-2px);
    }
    
    .form-control:valid:not(:placeholder-shown) {
        border-color: #10b981;
        background-color: rgba(16, 185, 129, 0.05);
    }
    
    .form-control::placeholder {
        color: #adb5bd;
        font-weight: 500;
    }
    
    /* ========== BUTTON IMPROVEMENTS ========== */
    .btn {
        border-radius: 0.5rem;
        font-weight: 700;
        padding: 1rem 2rem;
        transition: var(--transition);
        border: none;
        position: relative;
        overflow: hidden;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn:hover::before {
        width: 400px;
        height: 400px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--campus-green) 0%, #037a59 100%);
        box-shadow: 0 6px 20px rgba(4, 157, 111, 0.3);
        position: relative;
        z-index: 1;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #037a59 0%, #026146 100%);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(4, 157, 111, 0.4);
    }
    
    .btn-primary:active {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(4, 157, 111, 0.3);
    }
    
    /* ========== ALERT IMPROVEMENTS ========== */
    .alert {
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-left: 4px solid;
        padding: 1.25rem 1.5rem;
        animation: slideDown 0.5s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
        border-left-color: #10b981;
    }
    
    .alert-success::before {
        content: '✓';
        display: inline-block;
        font-size: 1.5rem;
        font-weight: 800;
        margin-right: 1rem;
        color: #10b981;
    }
    
    /* ========== HELPER TEXT ========== */
    .helper-box {
        background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%);
        border-left: 4px solid #ffc107;
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.2);
    }
    
    .helper-box .icon {
        font-size: 1.5rem;
        color: #ffc107;
        margin-right: 1rem;
    }
    
    .helper-box ul {
        margin-bottom: 0;
        padding-left: 1.5rem;
    }
    
    .helper-box li {
        margin-bottom: 0.5rem;
        color: #856404;
    }
    
    .helper-box code {
        background: rgba(0,0,0,0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-family: 'Courier New', monospace;
        color: #333;
    }
    
    /* ========== LOADING STATE ========== */
    .btn-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }
    
    .btn-loading::after {
        content: "";
        position: absolute;
        width: 20px;
        height: 20px;
        top: 50%;
        right: 20px;
        margin-top: -10px;
        border: 3px solid currentColor;
        border-radius: 50%;
        border-right-color: transparent;
        animation: spinner-border 0.75s linear infinite;
    }
    
    @keyframes spinner-border {
        to { transform: rotate(360deg); }
    }
    
    /* ========== RESPONSIVE IMPROVEMENTS ========== */
    @media (max-width: 768px) {
        body {
            background: var(--light-gray);
        }
        
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h1 {
            font-size: 1.75rem;
        }
        
        .stats-preview {
            grid-template-columns: 1fr;
        }
        
        .stat-preview-card .value {
            font-size: 2rem;
        }
        
        .table thead th,
        .table tbody td {
            padding: 1rem 0.75rem;
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 0.85rem 1.5rem;
            font-size: 0.9rem;
        }
    }
    
    /* ========== SCROLLBAR STYLING ========== */
    ::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }
    
    ::-webkit-scrollbar-track {
        background: var(--light-gray);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--campus-green), #037a59);
        border-radius: 5px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #037a59, #026146);
    }
</style>

<div class="container my-5">
    <!-- Header Section -->
    <div class="page-header">
        <h1><i class="bi bi-journal-text me-2"></i>Lengkapi Riwayat Akademik</h1>
        <p class="subtitle">
            <i class="bi bi-info-circle me-2"></i>
            Isi IP dan SKS yang Anda peroleh di setiap semester. IPK dan Total SKS di profil Anda akan ter-update secara otomatis.
        </p>
    </div>

    <!-- Stats Preview -->
    <?php if (count($riwayat_tersimpan) > 0): ?>
    <div class="stats-preview">
        <div class="stat-preview-card">
            <div class="icon"><i class="bi bi-trophy-fill"></i></div>
            <div class="value" id="previewIPK"><?= number_format($ipk_preview, 2); ?></div>
            <div class="label">IPK Saat Ini</div>
        </div>
        <div class="stat-preview-card secondary">
            <div class="icon"><i class="bi bi-stack"></i></div>
            <div class="value" id="previewSKS"><?= $total_sks_preview; ?></div>
            <div class="label">Total SKS</div>
        </div>
        <div class="stat-preview-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="icon"><i class="bi bi-calendar-check"></i></div>
            <div class="value"><?= count($riwayat_tersimpan); ?></div>
            <div class="label">Semester Terisi</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Helper Box -->
    <div class="helper-box">
        <div class="d-flex align-items-start">
            <div class="icon"><i class="bi bi-lightbulb-fill"></i></div>
            <div>
                <strong>Tips Pengisian:</strong>
                <ul class="mt-2 mb-0">
                    <li>Gunakan tanda titik (.) atau koma (,) untuk desimal. Contoh: <code>3.45</code> atau <code>3,45</code></li>
                    <li>IP Semester bernilai antara 0.00 - 4.00</li>
                    <li>SKS maksimal per semester umumnya 24 SKS</li>
                    <li>Kosongkan semester yang belum/tidak ditempuh</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($pesan_sukses); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="input_riwayat.php" id="formRiwayat">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width: 30%;"><i class="bi bi-calendar3 me-2"></i>Semester</th>
                                <th style="width: 35%;"><i class="bi bi-graph-up me-2"></i>Indeks Prestasi (IP)</th>
                                <th style="width: 35%;"><i class="bi bi-stack me-2"></i>Jumlah SKS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 14; $i++): ?>
                                <tr>
                                    <td><strong>Semester <?= $i; ?></strong></td>
                                    <td>
                                        <input type="text" 
                                               pattern="[0-9]+([.,][0-9]+)?" 
                                               class="form-control ip-input" 
                                               name="ips[<?= $i; ?>]" 
                                               placeholder="0.00 - 4.00" 
                                               value="<?= htmlspecialchars($riwayat_tersimpan[$i]['ip_semester'] ?? ''); ?>"
                                               data-semester="<?= $i; ?>">
                                    </td>
                                    <td>
                                        <input type="number" 
                                               min="0" 
                                               max="24" 
                                               class="form-control sks-input" 
                                               name="sks[<?= $i; ?>]" 
                                               placeholder="0 - 24" 
                                               value="<?= htmlspecialchars($riwayat_tersimpan[$i]['sks_semester'] ?? ''); ?>"
                                               data-semester="<?= $i; ?>">
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formRiwayat');
    const btnSubmit = document.getElementById('btnSubmit');
    const ipInputs = document.querySelectorAll('.ip-input');
    const sksInputs = document.querySelectorAll('.sks-input');
    
    // Validasi input IP (0-4)
    ipInputs.forEach(input => {
        input.addEventListener('blur', function() {
            let value = parseFloat(this.value.replace(',', '.'));
            if (!isNaN(value) && this.value !== '') {
                if (value > 4.0) {
                    this.value = '4.00';
                    showToast('IP maksimal adalah 4.00', 'warning');
                } else if (value < 0) {
                    this.value = '0.00';
                    showToast('IP minimal adalah 0.00', 'warning');
                } else {
                    this.value = value.toFixed(2);
                }
            }
        });
        
        input.addEventListener('focus', function() {
            this.select();
        });
    });
    
    // Validasi input SKS (0-24)
    sksInputs.forEach(input => {
        input.addEventListener('blur', function() {
            let value = parseInt(this.value);
            if (!isNaN(value) && this.value !== '') {
                if (value > 24) {
                    this.value = '24';
                    showToast('SKS maksimal adalah 24', 'warning');
                } else if (value < 0) {
                    this.value = '0';
                }
            }
        });
        
        input.addEventListener('focus', function() {
            this.select();
        });
    });
    
    // Loading state saat submit
    form.addEventListener('submit', function(e) {
        btnSubmit.classList.add('btn-loading');
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
        
        // Backup: re-enable after 10 seconds
        setTimeout(() => {
            btnSubmit.classList.remove('btn-loading');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-save me-2"></i>Simpan Perubahan';
        }, 10000);
    });
    
    // Auto-calculate preview
    function updatePreview() {
        let totalSKS = 0;
        let totalBobot = 0;
        
        ipInputs.forEach((ipInput, index) => {
            const sksInput = sksInputs[index];
            const ip = parseFloat(ipInput.value.replace(',', '.'));
            const sks = parseInt(sksInput.value);
            
            if (!isNaN(ip) && !isNaN(sks) && ip > 0 && sks > 0) {
                totalSKS += sks;
                totalBobot += (ip * sks);
            }
        });
        
        const ipk = totalSKS > 0 ? (totalBobot / totalSKS) : 0;
        
        const previewIPK = document.getElementById('previewIPK');
        const previewSKS = document.getElementById('previewSKS');
        
        if (previewIPK) {
            previewIPK.textContent = ipk.toFixed(2);
            previewIPK.style.animation = 'pulse 0.5s ease';
            setTimeout(() => {
                previewIPK.style.animation = '';
            }, 500);
        }
        
        if (previewSKS) {
            previewSKS.textContent = totalSKS;
            previewSKS.style.animation = 'pulse 0.5s ease';
            setTimeout(() => {
                previewSKS.style.animation = '';
            }, 500);
        }
    }
    
    // Attach change listeners
    ipInputs.forEach(input => input.addEventListener('change', updatePreview));
    sksInputs.forEach(input => input.addEventListener('change', updatePreview));
    
    // Toast notification function
    function showToast(message, type = 'info') {
        const colors = {
            'success': '#10b981',
            'warning': '#ffc107',
            'error': '#ef4444',
            'info': '#0d6efd'
        };
        
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${colors[type] || colors.info};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
            animation: slideInRight 0.3s ease;
            font-weight: 600;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Animation keyframes
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    `;
    document.head.appendChild(style);
});
</script>

<?php 
// Tutup koneksi
$conn->close();
require 'templates/footer.php';
?>
