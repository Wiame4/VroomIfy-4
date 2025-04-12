<?php
// Connexion à la base de données SQLite
try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer la moyenne des votes et le nombre total d'avis
    try {
        $query = "SELECT AVG(noteAvis) as moyenne, COUNT(*) as total FROM avis";
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $moyenne = is_numeric($result['moyenne']) ? number_format($result['moyenne'], 1) : null;
        $totalVotes = $result['total'];

    } catch (PDOException $e) {
        $moyenne = null;
        $totalVotes = 0;
    }

    // Récupérer le nombre d'avis pour chaque note (1 à 5 étoiles)
    try {
        $query = "
            SELECT noteAvis, COUNT(*) as count
            FROM avis
            GROUP BY noteAvis
            ORDER BY noteAvis DESC
        ";
        $stmt = $pdo->query($query);
        $noteCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $percentages = [];
        for ($i = 5; $i >= 1; $i--) {
            $percentages[$i] = $totalVotes > 0 ? ($noteCounts[$i] ?? 0) / $totalVotes * 100 : 0;
        }

    } catch (PDOException $e) {
        $noteCounts = [];
        $percentages = [];
        $totalVotes = 0;
    }

    // Récupérer les trois derniers avis
    $query = '
        SELECT 
            a.idAvis,
            a.textAvis,
            a.noteAvis,
            c.nomClient
        FROM avis a
        JOIN client c ON a.idClient = c.idClient
        ORDER BY a.idAvis DESC
        LIMIT 3
    ';
    
    $stmt = $pdo->query($query);
    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $avis = [];
    $errorMessage = 'Erreur de connexion à la base de données : ' . $e->getMessage();
}

// Fonction pour choisir l'image selon la note
function getImageAvis($note) {
    if ($note >= 4) {
        return "/VroomIfy-4/images/sourir.png";
    } elseif ($note >= 2) {
        return "/VroomIfy-4/images/neutre.png";
    } else {
        return "/VroomIfy-4/images/facher.png";
    }
}

// Fonction pour afficher les étoiles dynamiquement
function getStars($note) {
    if (!is_numeric($note)) {
        return "N/A";
    }

    $fullStars = floor($note);
    $halfStar = ($note - $fullStars) >= 0.5 ? "★" : "";
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    return str_repeat('★', $fullStars) . $halfStar . str_repeat('☆', $emptyStars);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Avis</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="/VroomIfy-4/styles/accueil.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>

<body>
<section id="avis" class="avis">
    <div class="bg-light">
        <div class="container mt-5">
            <div class="card p-4 shadow-sm">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <h2 class="text-warning"><?= $moyenne !== null ? $moyenne : "N/A" ?></h2>
                        <div class="text-warning fs-3"><?= $moyenne !== null ? getStars($moyenne) : "N/A" ?></div>
                        <p class="text-muted"><?= $totalVotes ?> Notes</p>
                    </div>

                    <div class="col-md-6">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <div class="d-flex align-items-center mb-2">
                                <span class="text-warning"><?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?></span>
                                <div class="progress flex-grow-1 ms-2" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: <?= $percentages[$i] ?? 0 ?>%;"></div>
                                </div>
                                <span class="ms-2"><?= $noteCounts[$i] ?? 0 ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h4>Commentaires récents</h4>
                    <?php if (!empty($avis)): ?>
                        <?php foreach ($avis as $avisItem): ?>
                            <div class="card mb-3 p-3 d-flex flex-row align-items-center">
                                <img src="<?= getImageAvis($avisItem['noteAvis']) ?>" class="rounded-circle me-3" width="50">
                                <div>
                                    <h6><?= htmlspecialchars($avisItem['nomClient']) ?></h6>
                                    <div class="text-warning"><?= getStars($avisItem['noteAvis']) ?></div>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($avisItem['textAvis']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun avis à afficher.</p>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <h4>Ajouter un avis</h4>
                    <form id="review-form" method="POST" action="/VroomIfy-4/pages/avis.php" class="p-3 border rounded bg-white">
                        <div class="mb-2">
                            <label class="form-label">Ajoutez votre note</label>
                            <div class="star-rating fs-3">
                                <span data-value="1">☆</span>
                                <span data-value="2">☆</span>
                                <span data-value="3">☆</span>
                                <span data-value="4">☆</span>
                                <span data-value="5">☆</span>
                            </div>
                            <input type="hidden" id="rating" name="rating" value="">
                        </div>
                        <input type="text" name="nom" class="form-control mb-2" placeholder="Nom" required oninvalid="this.setCustomValidity('Veuillez saisir votre nom !')" oninput="this.setCustomValidity('')" title="Veuillez saisir votre nom !">
                        <input type="email" name="email" class="form-control mb-2" placeholder="Email" required oninvalid="this.setCustomValidity('Veuillez saisir votre email valide !')" oninput="this.setCustomValidity('')" title="Veuillez saisir votre email valide ! ">
                        <textarea name="commentaire" class="form-control mb-2" placeholder="Écrivez votre avis" required oninvalid="this.setCustomValidity('Veuillez saisir un avis !')" oninput="this.setCustomValidity('')" title="Veuillez saisir un avis !"></textarea>
                        <button type="submit" class="btn btn-warning w-100">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="/VroomIfy-4/js/avis.js"></script>

</body>
</html>