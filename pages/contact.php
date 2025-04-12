<?php
session_start(); // Démarrer la session
$showModal = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION["user_id"])) {
        $showModal = true;
    } else {    
        $nomC = $_POST["nomC"];
        $emailC = $_POST["emailC"];
        $telC = $_POST["telC"];
        $messageC = $_POST["messageC"];

        try {
            // Connexion à SQLite
            $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Insertion des données
            $stmt = $pdo->prepare("INSERT INTO message (nomC, emailC, telC, messageC) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nomC, $emailC, $telC, $messageC]);

           // Par ceci
header("Location: contact.php?success=1");
exit();
        } catch (PDOException $e) {
            echo "<script type='text/javascript'>alert('Erreur : " . $e->getMessage() . "');</script>";
        }
    }
}

$nomClient = isset($_SESSION["nom"]) ? htmlspecialchars($_SESSION["nom"]) : "";
$emailClient = isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VroomIfy - Contact</title>
    <link rel="stylesheet" href="/VroomIfy-4/styles/contact.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Styles de la modale */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .custom-modal {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 90%;
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        .modal-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }

        .modal-body p {
            color: #34495e;
            font-size: 1.1rem;
            margin: 1rem 0;
        }

        .modal-footer {
            margin-top: 1.5rem;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn-primary {
            background: #2ecc71;
            color: white;
        }

        .close {
            position: absolute;
            top: 1rem;
            right: 1.5rem;
            font-size: 1.8rem;
            cursor: pointer;
            color: #7f8c8d;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #e74c3c;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div id="navbar-container"></div>
    <div class="container" id="aboutUs">  
        <form id="contact" action="contact.php" method="post">
            <h3>Contactez-Nous</h3>
            <h4>Clients, partenaires...</h4>
            <fieldset>
                <input placeholder="Nom" type="text" name="nomC" tabindex="1" value="<?= $nomClient ?>" required oninvalid="this.setCustomValidity('Veuillez saisir votre nom !')" oninput="this.setCustomValidity('')" title="Veuillez saisir votre nom !"> 
            </fieldset>
            <fieldset>
                <input placeholder="Email" type="email" name="emailC" tabindex="2" value="<?= $emailClient ?>" required oninvalid="this.setCustomValidity('Veuillez saisir votre email valide !')" oninput="this.setCustomValidity('')" title="Veuillez saisir votre email valide !">
            </fieldset>
            <fieldset>
            <input type="text" name="telC" placeholder="Saisir un numéro (ex: 0123456789)" pattern="^0\d{9}$" maxlength="10" required oninvalid="this.setCustomValidity('Veuillez saisir votre numéro de téléphone valide (ex: 0123456789) !')" oninput="this.setCustomValidity('')" title="Le numéro doit commencer par 0 et contenir 10 chiffres.">
            </fieldset>
            <fieldset>
                <textarea placeholder="Message..." name="messageC" tabindex="5" required oninvalid="this.setCustomValidity('Veuillez saisir un message !')" oninput="this.setCustomValidity('')" title="Veuillez saisir un message !"></textarea>
            </fieldset>
            <fieldset>
                <button name="submit" type="submit" id="contact-submit" data-submit="...Sending">Envoyer</button>
            </fieldset>
        </form>
    </div>
    <div id="footer-container"></div>

    <!-- Modale de connexion -->
    <div id="loginModal" class="modal-overlay" style="display: none;">
        <div class="custom-modal">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h3>Accès sécurisé</h3>
            </div>
            <div class="modal-body">
                <p>Connectez-vous pour envoyer un message !</p>
            </div>
            <div class="modal-footer">
                <a href="connexion.php" class="btn btn-primary">🚪 Se connecter</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('success')) {
        Swal.fire({
            icon: 'success',
            title: 'Message envoyé !',
            text: 'Votre message a été transmis avec succès.',
            confirmButtonColor: '#2ecc71'
        });
        
        // Nettoyer l'URL
        history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById('loginModal');
            const closeBtn = document.querySelector('.close');

            // Afficher la modale si l'utilisateur n'est pas connecté
            <?php if ($showModal): ?>
                modal.style.display = 'flex';
            <?php endif; ?>

            // Gestion de la fermeture de la modale
            if (closeBtn) {
                closeBtn.onclick = () => modal.style.display = 'none';
            }

            window.onclick = (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            };

            // Empêcher l'envoi du formulaire si non connecté
            document.getElementById('contact').addEventListener('submit', (e) => {
                <?php if (!isset($_SESSION["user_id"])): ?>
                    e.preventDefault();
                    modal.style.display = 'flex';
                <?php endif; ?>
            });
        });
    </script>
    <script src="../js/contact.js"></script>
</body>
</html>