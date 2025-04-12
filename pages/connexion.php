<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Variables pour les messages d'erreur
$login_error = '';
$signup_error = '';
$show_register = false;

// Traitement des formulaires
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["signup"])) {
        $show_register = true;
        
        // Récupération des données
        $nomClient = trim($_POST["signup_name"]);
        $emailClient = trim($_POST["signup_email"]);
        $telClient = trim($_POST["signup_phone"]);
        $mot_de_passeClient = trim($_POST["signup_password"]);
        $confirm_password = trim($_POST["signup_confirm_password"]);
        
        // Validation
        if (empty($nomClient) || empty($emailClient) || empty($telClient) || empty($mot_de_passeClient)) {
            $signup_error = "Tous les champs sont obligatoires.";
        } elseif ($mot_de_passeClient !== $confirm_password) {
            $signup_error = "Les mots de passe ne correspondent pas.";
        } elseif (!filter_var($emailClient, FILTER_VALIDATE_EMAIL)) {
            $signup_error = "Format d'email invalide.";
        } elseif (!preg_match("/^[0-9]{10}$/", $telClient)) {
            $signup_error = "Le téléphone doit contenir 10 chiffres (ex: 0612345678).";
        } elseif (strlen($mot_de_passeClient) < 8 || !preg_match("/[A-Z]/", $mot_de_passeClient) || !preg_match("/[0-9]/", $mot_de_passeClient)) {
            $signup_error = "Le mot de passe doit contenir au moins 8 caractères, dont 1 majuscule et 1 chiffre.";
        } else {
            try {
                // Vérification email existant
                $stmt = $pdo->prepare("SELECT * FROM client WHERE emailClient = ?");
                $stmt->execute([$emailClient]);
                if ($stmt->fetch()) {
                    $signup_error = "Cet email est déjà utilisé.";
                    throw new Exception("Email existant");
                }

                // Vérification téléphone existant
                $stmt = $pdo->prepare("SELECT * FROM client WHERE telClient = ?");
                $stmt->execute([$telClient]);
                if ($stmt->fetch()) {
                    $signup_error = "Ce numéro de téléphone est déjà utilisé.";
                    throw new Exception("Téléphone existant");
                }

                // Hashage du mot de passe
                $mot_de_passe_hash = password_hash($mot_de_passeClient, PASSWORD_DEFAULT);
                
                // Insertion
                $stmt = $pdo->prepare("INSERT INTO client (nomClient, emailClient, telClient, mot_de_passeClient) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nomClient, $emailClient, $telClient, $mot_de_passe_hash]);
                
                // Connexion automatique
                $_SESSION["user_id"] = $pdo->lastInsertId();
                $_SESSION["nom"] = $nomClient;
                $_SESSION["email"] = $emailClient;
                $_SESSION["telephone"] = $telClient;
                $_SESSION["role"] = "client";
                
                header("Location: /VroomIfy-4/pages/accueil.php");
                exit();
                
            } catch (PDOException $e) {
                if (empty($signup_error)) {
                    $signup_error = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
                error_log("Erreur inscription: " . $e->getMessage());
            } catch (Exception $e) {
                // Les erreurs de validation sont déjà gérées par $signup_error
            }
        }
    }
    elseif (isset($_POST["signin_email"]) && isset($_POST["signin_password"])) {
        $email = trim($_POST["signin_email"]);
        $password = trim($_POST["signin_password"]);
        
        // Vérification admin
$stmt = $pdo->prepare("SELECT * FROM admin WHERE emailAdmin = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin["mot_de_passeAdmin"])) {
    $_SESSION["admin_id"] = $admin["id"];
    $_SESSION["nom"] = $admin["nomAdmin"];
    $_SESSION["email"] = $admin["emailAdmin"];
    $_SESSION["role"] = "admin";
    header("Location: /VroomIfy-4/pages/Admin-accueil.html");
    exit();
}
        
        // Vérification client
        $stmt = $pdo->prepare("SELECT * FROM client WHERE emailClient = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user["mot_de_passeClient"])) {
            $_SESSION["user_id"] = $user["idClient"];
            $_SESSION["nom"] = $user["nomClient"];
            $_SESSION["email"] = $user["emailClient"];
            $_SESSION["telephone"] = $user["telClient"];
            $_SESSION["role"] = "client";
            header("Location: /VroomIfy-4/pages/accueil.php");
            exit();
        }
        
        $login_error = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="/VroomIfy-4/styles/accueil.css?v=<?php echo time(); ?>">
  <title>Connexion - VroomIfy</title>
  <style>
    /* Styles pour les vidéos en fond */
    .video-background {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      overflow: hidden;
    }
    
    .video-slide {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0;
      transition: opacity 1s ease-in-out;
    }
    
    .video-slide.active {
      opacity: 1;
    }
    
    /* Styles pour le contenu de connexion */
    .login-content {
      position: relative;
      z-index: 1;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: rgba(0, 0, 0, 0.5);
    }
    
    .login-box {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 15px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .login-title {
      color: #081b29;
      text-align: center;
      margin-bottom: 30px;
      font-weight: 700;
    }
    
    .form-group {
      margin-bottom: 25px;
      position: relative;
    }
    
    .form-control {
      height: 50px;
      border-radius: 25px;
      padding-right: 45px;
      padding-left: 15px;
      border: 1px solid #ddd;
      position: relative;
      z-index: 1;
      background-color: transparent;
      width: 100%;
    }
    
    .input-icon {
      position: absolute;
      top: 50%;
      right: 20px;
      transform: translateY(-50%);
      color: #777;
      z-index: 2;
      pointer-events: none;
    }
    
    .btn-login {
      background: #2696e9;
      color: white;
      border: none;
      border-radius: 25px;
      padding: 12px;
      width: 100%;
      font-weight: 600;
      transition: background 0.3s;
    }
    
    .btn-login:hover {
      background: #1e7bc8;
    }
    
    .error-message {
      color: #dc3545;
      text-align: center;
      margin-bottom: 20px;
      padding: 10px;
      background-color: #f8d7da;
      border-radius: 5px;
    }
    
    .form-footer {
      text-align: center;
      margin-top: 20px;
    }
    
    .form-footer a {
      color: #2696e9;
      text-decoration: none;
      font-weight: 500;
    }
    
    .form-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- Barre de navigation -->
  <div id="navbar-container"></div>

  <!-- Vidéos en fond sans boutons de navigation -->
  <div class="video-background">
    <video src="/VroomIfy-4/videos/1.mp4" class="video-slide active" autoplay muted loop></video>
    <video src="/VroomIfy-4/videos/2.mp4" class="video-slide" autoplay muted loop></video>
    <video src="/VroomIfy-4/videos/3.mp4" class="video-slide" autoplay muted loop></video>
    <video src="/VroomIfy-4/videos/4.mp4" class="video-slide" autoplay muted loop></video>
    <video src="/VroomIfy-4/videos/5.mp4" class="video-slide" autoplay muted loop></video>
  </div>

  <!-- Contenu de connexion -->
  <section class="login-content">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <!-- Formulaire de connexion -->
          <div class="login-box" id="login-form" style="<?php echo $show_register ? 'display: none;' : 'display: block;' ?>">
            <h2 class="login-title">Connexion</h2>
            <?php if ($login_error): ?>
              <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
              <div class="form-group">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="signin_email" class="form-control" placeholder="Email" required oninvalid="this.setCustomValidity('Veuillez donner votre email !')" oninput="this.setCustomValidity('')">
              </div>
              <div class="form-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="signin_password" class="form-control" placeholder="Mot de passe" required oninvalid="this.setCustomValidity('Veuillez saisir le mot de passe !')" oninput="this.setCustomValidity('')">
              </div>
              <button type="submit" class="btn btn-login">Se connecter</button>
              <div class="form-footer">
                <p>Pas encore de compte ? <a href="#" onclick="showRegister(); return false;">S'inscrire</a></p>
              </div>
            </form>
          </div>

          <!-- Formulaire d'inscription -->
          <div class="login-box" id="register-form" style="<?php echo $show_register ? 'display: block;' : 'display: none;' ?>">
            <h2 class="login-title">Inscription</h2>
            <?php if ($signup_error): ?>
              <div class="error-message"><?php echo htmlspecialchars($signup_error); ?></div>
            <?php endif; ?>
            <form method="POST" action="">
              <div class="form-group">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="signup_name" class="form-control" placeholder="Nom complet" required value="<?php echo isset($_POST['signup_name']) ? htmlspecialchars($_POST['signup_name']) : ''; ?>" oninvalid="this.setCustomValidity('Veuillez saisir votre nom !')" oninput="this.setCustomValidity('')">
              </div>
              <div class="form-group">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="signup_email" class="form-control" placeholder="Email" required value="<?php echo isset($_POST['signup_email']) ? htmlspecialchars($_POST['signup_email']) : ''; ?>" oninvalid="this.setCustomValidity('Veuillez saisir votre email !')" oninput="this.setCustomValidity('')">
              </div>
              <div class="form-group">
                <i class="fas fa-phone input-icon"></i>
                <input type="tel" name="signup_phone" class="form-control" 
                       placeholder="Téléphone (ex: 0612345678)" 
                       pattern="[0-9]{10}" 
                       title="Entrez 10 chiffres sans espaces" 
                       required
                       value="<?php echo isset($_POST['signup_phone']) ? htmlspecialchars($_POST['signup_phone']) : ''; ?>" oninvalid="this.setCustomValidity('Veuillez saisir votre numéro de téléphone !')" oninput="this.setCustomValidity('')">
              </div>
              <div class="form-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="signup_password" class="form-control" placeholder="Mot de passe (min. 8 caractères)" required oninvalid="this.setCustomValidity('Veuillez saisir le mot de passe !')" oninput="this.setCustomValidity('')">
              </div>
              <div class="form-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="signup_confirm_password" class="form-control" placeholder="Confirmer le mot de passe" required oninvalid="this.setCustomValidity('Veuillez confirmer le mot de passe  !')" oninput="this.setCustomValidity('')">
              </div>
              <button type="submit" name="signup" class="btn btn-login">S'inscrire</button>
              <div class="form-footer">
                <p>Déjà un compte ? <a href="#" onclick="showLogin(); return false;">Se connecter</a></p>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Pied de page -->
  <div id="footer-container"></div>

  <script>
    // Fonctions pour basculer entre les formulaires
    function showRegister() {
      document.getElementById('login-form').style.display = 'none';
      document.getElementById('register-form').style.display = 'block';
    }
    
    function showLogin() {
      document.getElementById('register-form').style.display = 'none';
      document.getElementById('login-form').style.display = 'block';
    }

    // Script pour les vidéos en fond (navigation automatique sans boutons)
    const slides = document.querySelectorAll(".video-slide");
    let currentSlide = 0;
    
    function changeSlide() {
      // Retirer la classe active de la vidéo actuelle
      slides[currentSlide].classList.remove("active");
      
      // Passer à la vidéo suivante
      currentSlide = (currentSlide + 1) % slides.length;
      
      // Ajouter la classe active à la nouvelle vidéo
      slides[currentSlide].classList.add("active");
    }
    
    // Démarrer le changement automatique (toutes les 5 secondes)
    setInterval(changeSlide, 5000);

    // Chargement des composants
    document.addEventListener('DOMContentLoaded', function() {
      fetch('/VroomIfy-4/pages/client-barre.php')
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.text();
        })
        .then(data => {
          document.getElementById('navbar-container').innerHTML = data;
        })
        .catch(err => {
          console.error('Erreur navbar:', err);
          document.getElementById('navbar-container').innerHTML = '<div class="alert alert-warning">Erreur de chargement du menu</div>';
        });
        
      fetch('/VroomIfy-4/pages/pied-page.php')
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.text();
        })
        .then(data => {
          document.getElementById('footer-container').innerHTML = data;
        })
        .catch(err => {
          console.error('Erreur footer:', err);
        });
    });
  </script>
  <script src="/VroomIfy-4/js/accueil.js"></script>
</body>
</html>