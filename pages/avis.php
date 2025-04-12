<?php
// Connexion à la base de données SQLite
try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $message = "Erreur de connexion : " . addslashes($e->getMessage());
    echo "<script>
            alert('$message');
            window.location.href = '../index.php';
          </script>";
    exit();
}

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer et sécuriser les données du formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $textAvis = htmlspecialchars($_POST['commentaire']);
    $noteAvis = isset($_POST['rating']) ? (int) $_POST['rating'] : 0; // Convertir en entier

    // Vérifier que la note est valide (entre 1 et 5)
    if ($noteAvis < 1 || $noteAvis > 5) {
        echo "<script>
                alert('Erreur : La note doit être comprise entre 1 et 5.');
                window.location.href = '../index.php';
              </script>";
        exit();
    }

    try {
        // Vérifier si le client existe dans la table client
        $stmt = $pdo->prepare("SELECT idClient FROM client WHERE nomClient = ? AND emailClient = ?");
        $stmt->execute([$nom, $email]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($client) {
            $idClient = $client['idClient'];

            // Insérer l'avis avec la note dans la table avis
            $stmt = $pdo->prepare("INSERT INTO avis (idClient, textAvis, noteAvis) VALUES (?, ?, ?)");
            $stmt->execute([$idClient, $textAvis, $noteAvis]);

            echo "<script>
                    alert('Merci pour votre avis, $nom ! Votre note : $noteAvis ★');
                    window.location.href = '../index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Erreur : Ce client n\'existe pas dans notre base de données.');
                    window.location.href = '../index.php';
                  </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
                alert('Erreur : " . addslashes($e->getMessage()) . "');
                window.location.href = '../index.php';
              </script>";
    }
}
?>
