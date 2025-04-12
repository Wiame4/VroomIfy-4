document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
    });

    // Form submission
    const adminForm = document.getElementById('adminForm');
    const messageDiv = document.getElementById('message');

    adminForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Get form values
        const nomAdmin = document.getElementById('nomAdmin').value;
        const emailAdmin = document.getElementById('emailAdmin').value;
        const mot_de_passeAdmin = document.getElementById('mot_de_passeAdmin').value;
        const confirm_password = document.getElementById('confirm_password').value;

        // Validate passwords match
        if (mot_de_passeAdmin !== confirm_password) {
            showMessage('Les mots de passe ne correspondent pas.', 'error');
            return;
        }

        // Validate password strength (optional)
        if (mot_de_passeAdmin.length < 8) {
            showMessage('Le mot de passe doit contenir au moins 8 caractères.', 'error');
            return;
        }

        // Send data to server
        const formData = new FormData();
        formData.append('nomAdmin', nomAdmin);
        formData.append('emailAdmin', emailAdmin);
        formData.append('mot_de_passeAdmin', mot_de_passeAdmin);

        fetch('Admin-ajouter.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                adminForm.reset();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('Une erreur est survenue. Veuillez réessayer.', 'error');
            console.error('Error:', error);
        });
    });

    function showMessage(message, type) {
        messageDiv.textContent = message;
        messageDiv.className = type;
        messageDiv.style.display = 'block';
        
        // Hide message after 5 seconds
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }
});

document.addEventListener("DOMContentLoaded", function() {
    fetch("../pages/pied-page.php")
        .then(response => response.text())
        .then(data => {
            document.getElementById("footer-container").innerHTML = data;
        })
        .catch(error => console.error("Erreur lors du chargement de la navbar:", error));
});
