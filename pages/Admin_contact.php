<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-6.9.3/src/Exception.php';
require 'PHPMailer-6.9.3/src/PHPMailer.php';
require 'PHPMailer-6.9.3/src/SMTP.php';

$pdo = new PDO("sqlite:../base de donnee/VroomIfy.db");

$action = $_GET['action'] ?? '';
$messages_par_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $messages_par_page;

// Récupérer les messages avec pagination
if ($action == "get_messages") {
    $stmt = $pdo->prepare("SELECT * FROM message LIMIT ? OFFSET ?");
    $stmt->execute([$messages_par_page, $offset]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_messages = $pdo->query("SELECT COUNT(*) FROM message")->fetchColumn();
    $total_pages = ceil($total_messages / $messages_par_page);

    echo json_encode(["messages" => $messages, "total_pages" => $total_pages]);
    exit;
}

// Récupérer l'email de l'admin
if ($action == "get_admin_email") {
    $id_admin = $_SESSION['id_admin'] ?? 1;
    $stmt = $pdo->prepare("SELECT emailAdmin FROM admin WHERE id = ?");
    $stmt->execute([$id_admin]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $admin['emailAdmin'] ?? '';
    exit;
}

// Envoyer un e-mail au client
if ($action == "send_email") {
    $id_message = $_POST['id_message'] ?? '';
    $reponse_message = $_POST['message-reponse'] ?? ''; 

    if (!$id_message || !$reponse_message) {
        echo "❌ Données manquantes.";
        exit;
    }

    // Récupérer l'email du client
    $stmt = $pdo->prepare("SELECT emailC FROM message WHERE idContact = ?");
    $stmt->execute([$id_message]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo "❌ Client introuvable.";
        exit;
    }

    $email_client = $client['emailC'];

    $mail = new PHPMailer(true);

    try {
        // Paramètres du serveur
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fatimazahra.massaoudi23@ump.ac.ma';  // Ton email
        $mail->Password = 'tdwa lmmb jfcv mgcm';                // Ton mot de passe
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Destinataires
        $mail->setFrom('fatimazahra.massaoudi23@ump.ac.ma', 'VroomIfy');
        $mail->addAddress($email_client);

        // Contenu de l'e-mail
        $mail->isHTML(true);
        $mail->Subject = 'La reponse de VroomIfy sur votre message';
        $mail->Body = nl2br($reponse_message);
        $mail->AltBody = $reponse_message;

        $mail->send();
        echo '✅ Message envoyé avec succès.';
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'envoi du message : {$mail->ErrorInfo}";
    }

    exit;
}

?>
