
    fetch('/VroomIfy-4/pages/client-barre.php')
        .then(r => r.text())
        .then(html => {
            document.getElementById('navbar-container').innerHTML = html;
        });

        fetch('/VroomIfy-4/pages/pied-page.php')
        .then(r => r.text())
        .then(html => {
            document.getElementById('footer-container').innerHTML = html;
        });
    