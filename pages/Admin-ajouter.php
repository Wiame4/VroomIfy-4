<?php
header('Content-Type: application/json');

$dbPath = __DIR__ . '/../base de donnee/VroomIfy.db'; // Adjust path as needed

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $nomAdmin = trim($_POST['nomAdmin'] ?? '');
    $emailAdmin = trim($_POST['emailAdmin'] ?? '');
    $mot_de_passeAdmin = $_POST['mot_de_passeAdmin'] ?? '';

    if (empty($nomAdmin) || empty($emailAdmin) || empty($mot_de_passeAdmin)) {
        throw new Exception('Tous les champs sont obligatoires');
    }

    if (!filter_var($emailAdmin, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Adresse email invalide');
    }

    $stmt = $db->prepare('SELECT id FROM admin WHERE emailAdmin = :email');
    $stmt->bindValue(':email', $emailAdmin, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->fetch()) {
        throw new Exception('Cet email est déjà utilisé par un autre administrateur');
    }

    $hashedPassword = password_hash($mot_de_passeAdmin, PASSWORD_DEFAULT);

    $stmt = $db->prepare('INSERT INTO admin (nomAdmin, emailAdmin, mot_de_passeAdmin) VALUES (:nom, :email, :password)');
    $stmt->bindValue(':nom', $nomAdmin, PDO::PARAM_STR);
    $stmt->bindValue(':email', $emailAdmin, PDO::PARAM_STR);
    $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Administrateur ajouté avec succès'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>