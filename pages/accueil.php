<?php 
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/VroomIfy-4/styles/accueil.css?v=<?php echo time(); ?>">
  <title>VroomIfy</title>
</head>
<body>
  <!--navbar : -->
  <div id="navbar-container"></div>
  <!--section d'introduction sur le site : -->
  <section class="home">
    <video src="/VroomIfy-4/videos/1.mp4" class="video-slide active" autoplay muted loop></video>
    <video src="/VroomIfy-4/videos/2.mp4" class="video-slide" autoplay muted loop></video>
    <video src="/VroomIfy-4/videos/3.mp4" class="video-slide" autoplay muted loop></video>
    <video src="/VroomIfy-4/videos/4.mp4" class="video-slide" autoplay muted loop></video>
    <video src="/VroomIfy-4/videos/5.mp4" class="video-slide" autoplay muted loop></video>
    <div class="content">
        <h1>Bienvenue chez<br><span>VroomIfy </span></h1>
        <p>
          Une panne imprévue ? Un véhicule immobilisé au mauvais moment ? VroomIfy est là pour vous offrir un service de remorquage rapide, sécurisé et professionnel. Peu importe où vous êtes, notre équipe intervient efficacement pour vous remettre en route en toute sérénité.
          <br><br>
          Découvrez nos services et laissez-nous vous accompagner. Votre tranquillité est notre priorité.
        </p>
        <a href="#do-section">En Savoir Plus </a>
        <!--<a href="deconnexion.php">Deconnexion</a>
        <a href="connexion.html">Connexion</a>-->
    </div>
    <div class="media-icons">
        <a href="https://www.facebook.com/share/1AFtGYsSMK/?mibextid=qi2Omg"><i class="fa-brands fa-facebook"></i></a>
        <a href="https://www.instagram.com/vroom.ify?igsh=MTI5ZTE2MzNtZGQ2Yw=="><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-twitter"></i></a>
    </div>
    <div class="slider-navigation">
        <div class="nav-btn"></div>
        <div class="nav-btn"></div>
        <div class="nav-btn"></div>
        <div class="nav-btn"></div>
        <div class="nav-btn"></div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Variable globale pour l'état de connexion
  const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

  // Fonction de vérification de connexion
  function checkAuth(event) {
    if (!isLoggedIn) {
      event.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'Connexion requise',
        text: 'Vous devez vous connecter pour accéder à cette fonctionnalité.',
        confirmButtonText: 'OK'
      });
    }
  }

  // Initialisation après chargement du DOM
  document.addEventListener('DOMContentLoaded', () => {
    // Ajout des écouteurs d'événements sur les liens protégés
    document.querySelectorAll('a[href="/VroomIfy-4/pages/contact.php"], a[href="/VroomIfy-4/pages/profile.php"]')
      .forEach(link => {
        link.addEventListener('click', checkAuth);
      });
  });
</script>

<!--section des activités de site : -->
<section class="do_section layout_padding" id="do-section">
  <div class="container">
    <div class="heading_container">
      <h2 id="titre">Nos activités</h2>
      <p>
        Découvrez notre gamme complète de produits et services de remorquage, conçus pour répondre à tous vos besoins en assistance routière et transport de véhicules en toute sécurité.
      </p>
    </div>
    <div class="do_container">
      <div class="box arrow-start arrow_bg">
        <a href="/VroomIfy-4/pages/service.html" class="do">
          <div class="img-box">
              <img src="/VroomIfy-4/images/w1.png" alt="">
            </div>
            <div class="detail-box">
              <h6>Services de <br>remorquage</h6>
            </div>
        </a>
      </div>
      <div class="box arrow-middle arrow_bg">
        <a href="/VroomIfy-4/pages/boutique.php" class="do">
          <div class="img-box">
              <img src="/VroomIfy-4/images/w6.png" alt="">
            </div>
            <div class="detail-box">
              <h6>Boutique</h6>
            </div>
        </a>
      </div>
      <div class="box arrow-middle arrow_bg">
        <a href="/VroomIfy-4/pages/contact.php" class="do"  onclick="checkAuth(event)">
          <div class="img-box">
              <img src="/VroomIfy-4/images/w5.png" alt="">
            </div>
            <div class="detail-box">
              <h6>Service Client <br>24/7</h6>
            </div>
        </a>
      </div>
      <div class="box arrow-end arrow_bg">
        <a href="/VroomIfy-4/pages/profile.php" class="do"  onclick="checkAuth(event)">
          <div class="img-box">
          <img src="/VroomIfy-4/images/w7.png" alt="">
        </div>
        <div class="detail-box">
          <h6> Profil utilisateur</h6>
        </div>
      </a>
      </div>
    </div>
  </div>
</section>
       <!-- section des avis :-->
 <section id="avis" class="avis">
  <div id="avis-container">Chargement des avis...</div>
</section>


<script>
  fetch('/VroomIfy-4/pages/avis4.php')
  .then(response => response.text())
  .then(data => {
      document.getElementById('avis-container').innerHTML = data;
      setTimeout(() => {
          if (typeof initAvisStars === "function") {
              console.log("Réinitialisation des étoiles...");
              initAvisStars();
          } else {
              console.error("La fonction initAvisStars() n'est pas définie !");
          }
      }, 500);
  })
  .catch(error => console.error('Erreur lors du chargement des avis:', error));
</script>


<!-- pied de page :-->
<div id="footer-container"></div>

<script src="/VroomIfy-4/js/accueil.js"></script>
<script src="/VroomIfy-4/js/avis.js"></script>

</body>
</html>