<?php
session_start();
header('Content-Type: application/json');

try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../base de donnee/VroomIfy.db");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT 
            idService,
            nomService,
            descriptionService,
            CASE 
                WHEN LENGTH(imageService) > 0 THEN imageService
                ELSE NULL 
            END as imageService
        FROM service
    ");

    $response = [
        'isLoggedIn' => isset($_SESSION['user_id']),
        'services' => []
    ];

    while ($service = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($service['imageService'])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($service['imageService']);
            $image = "data:$mime;base64," . base64_encode($service['imageService']);
        } else {
            $image = '/pfe/images/default.png';
        }

        $response['services'][] = [
            'idService' => $service['idService'],
            'nomService' => $service['nomService'],
            'descriptionService' => $service['descriptionService'],
            'image' => $image
        ];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Erreur technique : ' . $e->getMessage(),
        'isLoggedIn' => false
    ]);
}
?>