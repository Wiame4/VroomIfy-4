<?php 
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="/VroomIfy-4/styles/pied-page.css">
    <title>Document</title>
</head>
<body>
 <div class="container-fluid">
    <section class="footer">
        <div class="social">
            <a href="https://www.instagram.com/vroom.ify?igsh=MTI5ZTE2MzNtZGQ2Yw=="><i class="fab fa-instagram"></i></a>
            <a href=""><i class="fab fa-snapchat"></i></a>
            <a href=""><i class="fab fa-twitter"></i></a>
            <a href="https://www.facebook.com/share/1AFtGYsSMK/?mibextid=qi2Omg"><i class="fab fa-facebook"></i></a>
        </div>

        <ul>
            <li>
                <a href="/VroomIfy-4/pages/equipe.html">Qui sommes-nous?</a>
            </li>
            <?php if($isLoggedIn): ?>
            <li><a href="/Vroomify-4/pages/contact.php">Contact</a></li>
            <?php endif; ?>
        </ul>
        <p class="copyright">Vroomify @ 2025</p>
    </section>  
 </div>
</body>
</html>