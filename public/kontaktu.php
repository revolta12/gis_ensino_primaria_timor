<?php
// =============================================
// Kontaktu - GIS Ensino Primaria Timor-Leste
// =============================================
// Página kontaktu ba website GIS Ensino Primaria

require_once '../config/database.php';
require_once '../includes/functions.php';

$db = getDB();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naran = sanitize($_POST['naran'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $asuntu = sanitize($_POST['asuntu'] ?? '');
    $mensajen = sanitize($_POST['mensajen'] ?? '');
    $honeypot = $_POST['website'] ?? ''; // Honeypot field for anti-spam
    
    // Check honeypot (should be empty - bot usually fills it)
    if (!empty($honeypot)) {
        // Bot detected, silently ignore - do nothing
        $error = '';
    } elseif (empty($naran) || empty($email) || empty($mensajen)) {
        $error = 'Favor prense naran, email, no mensajen.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email format la validu. Uza ezemplu: naran@domain.com';
    } else {
        $stmt = $db->prepare("INSERT INTO kontaktu_mensajen (naran_ema, email_ema, asuntu, mensajen) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$naran, $email, $asuntu, $mensajen])) {
            $success = '✅ Ita nia mensajen konsege haruka! Obrigadu barak. Ami sei responde lalais.';
        } else {
            $error = 'Falha hodi haruka mensajen. Favor kontaktu ami liu telefone diretamente.';
        }
    }
}

include_once '../includes/header.php';
?>

<style>
    /* Page header */
    .page-header {
        background: linear-gradient(135deg, #1F5A7A 0%, #0D3B52 100%);
        padding: 70px 0 50px;
        text-align: center;
        color: white;
        margin-bottom: 60px;
    }
    
    .page-header h1 {
        font-size: 2.8rem;
        font-weight: 900;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }
    
    .page-header p {
        font-size: 1.05rem;
        opacity: 0.95;
        font-weight: 400;
    }
    
    /* Contact Info Cards */
    .contact-info-card {
        background: white;
        border-radius: 16px;
        padding: 35px 28px;
        text-align: center;
        height: 100%;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 6px 24px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .contact-info-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 18px 45px rgba(31, 90, 122, 0.15);
    }
    
    .contact-icon {
        width: 85px;
        height: 85px;
        background: linear-gradient(135deg, #1F5A7A, #1ABC9C);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 2.2rem;
        transition: all 0.3s ease;
        box-shadow: 0 8px 24px rgba(31, 90, 122, 0.25);
    }
    
    .contact-info-card:hover .contact-icon {
        transform: scale(1.12) rotateY(5deg);
    }
    
    .contact-info-card h5 {
        color: #1F2937;
        font-weight: 800;
        margin-bottom: 16px;
        font-size: 1.1rem;
    }
    
    .contact-info-card p {
        color: #6B7280;
        margin-bottom: 6px;
        font-weight: 500;
    }
    
    .contact-info-card hr {
        margin: 22px 0;
        background: rgba(31, 90, 122, 0.12);
        border: none;
        height: 1px;
    }
    
    /* Contact Form */
    .contact-form {
        background: white;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .contact-form h4 {
        color: #1F2937;
        font-weight: 800;
        border-bottom: 3px solid #F39C12;
        display: inline-block;
        padding-bottom: 10px;
        margin-bottom: 28px;
    }
    
    .form-control, .form-select {
        border-radius: 10px;
        border: 1.5px solid #E5E7EB;
        padding: 12px 16px;
        transition: all 0.3s ease;
        background: white;
        font-size: 0.95rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #1F5A7A;
        box-shadow: 0 0 0 4px rgba(31, 90, 122, 0.1);
        outline: none;
    }
    
    .form-label {
        font-weight: 700;
        color: #1F2937;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }
    
    .btn-send {
        background: linear-gradient(135deg, #2C3E50, #1A252F);
        border: none;
        padding: 12px 30px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-send:hover {
        background: linear-gradient(135deg, #1A252F, #0f1a24);
        transform: translateY(-2px);
        box-shadow: 0 5px 20px rgba(44, 62, 80, 0.4);
    }
    
    /* Mini Map */
    .map-mini {
        height: 250px;
        border-radius: 16px;
        overflow: hidden;
        margin-top: 15px;
        border: 2px solid #F39C12;
    }
    
    /* Social Media Links */
    .social-links-contact {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        background: #f0f0f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2C3E50;
        transition: all 0.3s ease;
    }
    
    .social-link:hover {
        background: #F39C12;
        color: white;
        transform: translateY(-3px);
    }
    
    /* FAQs Section */
    .faq-section {
        margin-top: 60px;
    }
    
    .faq-item {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid #eee;
        transition: all 0.3s ease;
    }
    
    .faq-item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .faq-question {
        font-weight: 700;
        color: #2C3E50;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .faq-question i {
        color: #F39C12;
    }
    
    .faq-answer {
        color: #7F8C8D;
        font-size: 0.9rem;
        padding-left: 28px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 40px 0 30px;
        }
        .page-header h1 {
            font-size: 1.8rem;
        }
        .contact-form {
            padding: 20px;
        }
        .contact-info-card {
            padding: 20px;
        }
        .contact-icon {
            width: 60px;
            height: 60px;
            font-size: 24px;
        }
        .faq-question {
            font-size: 0.95rem;
        }
        .faq-answer {
            font-size: 0.85rem;
        }
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-envelope me-3"></i>Kontaktu Ami</h1>
        <p>Iha pergunta ka sugestaun kona-ba ensinu primaria iha Timor-Leste? Favor kontaktu ami nia ekipa.</p>
    </div>
</div>

<div class="container py-4">
    <!-- Contact Info Cards -->
    <div class="row mb-5">
        <div class="col-lg-4 mb-4">
            <div class="contact-info-card">
                <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h5>Enderesu</h5>
                <p>Ministériu Edukasaun</p>
                <p>Rua Presidente Nicolau Lobato</p>
                <p>Díli, Timor-Leste</p>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="contact-info-card">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h5>Telefone</h5>
                <p><strong>Main Office:</strong> +670 333 1234</p>
                <p><strong>Hotline:</strong> +670 7777 1234</p>
                <p><strong>Fax:</strong> +670 333 5678</p>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="contact-info-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h5>Email</h5>
                <p><strong>Jeral:</strong> info@ensinoprimaria.tl</p>
                <p><strong>Dadus:</strong> data@ensinoprimaria.tl</p>
                <p><strong>Suporta:</strong> support@ensinoprimaria.tl</p>
            </div>
        </div>
    </div>
    
    <!-- Contact Form & Map -->
    <div class="row">
        <div class="col-lg-7">
            <div class="contact-form">
                <h4><i class="fas fa-paper-plane me-2"></i> Haruka Mensajen</h4>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="contactForm">
                    <!-- Honeypot field (anti-spam) - hidden from real users -->
                    <div style="display: none;">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Naran Kompletu <span class="text-danger">*</span></label>
                            <input type="text" name="naran" class="form-control" placeholder="Exemplu: Maria da Silva" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="exemplu@email.com" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Asuntu</label>
                            <select name="asuntu" class="form-select">
                                <option value="">Hili asuntu...</option>
                                <option value="Informasaun Eskola">Informasaun Eskola Primaria</option>
                                <option value="Atualizasaun Dadus">Atualizasaun Dadus Eskola</option>
                                <option value="Laporan Error">Laporan Error Website</option>
                                <option value="Kerjasama">Kerjasama / Partnership</option>
                                <option value="Sugestaun">Sugestaun & Masukan</option>
                                <option value="Seluk">Seluknia</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Mensajen <span class="text-danger">*</span></label>
                            <textarea name="mensajen" class="form-control" rows="5" placeholder="Hakerek ita nia mensajen iha ne'e..." required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-send">
                                <i class="fas fa-paper-plane me-2"></i> Haruka Mensajen
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="contact-info-card">
                <h5><i class="fas fa-clock me-2"></i> Oras Servisu</h5>
                <p><strong>Segunda - Kinta:</strong> 08:00 - 17:00</p>
                <p><strong>Sesta:</strong> 08:00 - 15:00</p>
                <p><strong>Domingu no feriadu:</strong> Taka</p>
                
                <hr>
                
                <h5><i class="fab fa-whatsapp me-2 text-success"></i> WhatsApp Hotline</h5>
                <p>Konversa liu WhatsApp ba suporta lalais:</p>
                <p><strong><i class="fab fa-whatsapp"></i> +670 7777 1234</strong></p>
                
                <hr>
                
                <h5><i class="fab fa-facebook me-2"></i> Sosial Media</h5>
                <div class="social-links-contact">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
                
                <hr>
                
                <h5><i class="fas fa-map"></i> Lokasaun Eskritóriu</h5>
                <div id="miniContactMap" class="map-mini"></div>
                <p class="mt-2 small text-muted">
                    <i class="fas fa-building"></i> Ministériu Edukasaun, Díli, Timor-Leste
                </p>
            </div>
        </div>
    </div>
    
    <!-- FAQs Section -->
    <div class="faq-section">
        <div class="text-center mb-4">
            <h2 class="fw-bold"><i class="fas fa-question-circle me-2" style="color: #F39C12;"></i> Perguntas Frequentes (FAQ)</h2>
            <p class="text-muted">Resposta ba pergunta ne'ebé komun liu</p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-school"></i>
                        <span>Saida mak GIS Ensino Primaria?</span>
                    </div>
                    <div class="faq-answer">
                        GIS Ensino Primaria mak sistema informasaun geografiku ne'ebé hatudu lokasaun no informasaun eskola primaria iha Timor-Leste iha mapa interativu.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-database"></i>
                        <span>Oinsá atu hetan dadus eskola ne'ebé atualizadu?</span>
                    </div>
                    <div class="faq-answer">
                        Dadus eskola atualiza periodicamente husi Ministériu Edukasaun. Se ita hare dadus ne'ebé la loos, favor kontaktu ami liu formuláriu iha leten.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-route"></i>
                        <span>Bele hetan rota ba eskola?</span>
                    </div>
                    <div class="faq-answer">
                        Sin, iha mapa ita bele hetan rota husi ita nia pozisaun atual ba eskola ne'ebé ita hili.
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-star"></i>
                        <span>Saida mak rating avaliasaun eskola?</span>
                    </div>
                    <div class="faq-answer">
                        Rating avaliasaun mak nota husi vizitante/pais kona-ba kualidade eskola. Rating iha 1-5 star ne'ebé hatudu satisfasaun.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-water"></i>
                        <span>Oinsá atu filtra eskola ne'ebé iha bee moos?</span>
                    </div>
                    <div class="faq-answer">
                        Iha página Eskola, ita bele filtra uza "Fasilidade" filter hodi hatudu de'it eskola ne'ebé iha bee moos.
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <i class="fas fa-user-graduate"></i>
                        <span>Ema de'it mak bele uza sistema ne'e?</span>
                    </div>
                    <div class="faq-answer">
                        Sistema ne'e loke ba ema hotu. Maibé atu atualiza dadus eskola, presiza login nu'udar admin.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Mini map for contact page
    document.addEventListener('DOMContentLoaded', function() {
        const contactMapContainer = document.getElementById('miniContactMap');
        if (contactMapContainer) {
            const contactMap = L.map('miniContactMap').setView([-8.559, 125.579], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
            }).addTo(contactMap);
            
            // Custom icon for office location
            const officeIcon = L.divIcon({
                html: '<div style="background: #2C3E50; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 3px solid #F39C12;"><i class="fas fa-building" style="color: white; font-size: 18px;"></i></div>',
                iconSize: [40, 40],
                popupAnchor: [0, -20]
            });
            
            L.marker([-8.559, 125.579], { icon: officeIcon }).addTo(contactMap)
                .bindPopup('<strong>GIS Ensino Primaria Office</strong><br>Ministériu Edukasaun<br>Díli, Timor-Leste')
                .openPopup();
        }
    });
</script>

<?php include_once '../includes/footer.php'; ?>