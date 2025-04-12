document.addEventListener("DOMContentLoaded", function () {
    // Chargement des éléments communs
    Promise.all([
        fetch("/VroomIfy-4/pages/client-barre.php"),
        fetch("/VroomIfy-4/pages/pied-page.php")
    ])
    .then(responses => Promise.all(responses.map(r => r.text())))
    .then(([navbar, footer]) => {
        document.getElementById("navbar-container").innerHTML = navbar;
        document.getElementById("footer-container").innerHTML = footer;
    })
    .catch(error => console.error("Erreur de chargement :", error));

    // Chargement des services
    fetch("../pages/service.php?nocache=" + new Date().getTime())
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(data => {
            const serviceList = document.getElementById("service-list");
            if (!serviceList) return;

            if (data.error) {
                serviceList.innerHTML = `<p class="error-message">${data.error}</p>`;
                return;
            }

            serviceList.innerHTML = data.services.map(service => `
                <article class="card__article swiper-slide">
                    <div class="card__image">
                        <img src="${service.image}" 
                             alt="${service.nomService}" 
                             class="card__img"
                             onerror="this.src='../images/default.png'">
                        <div class="card__shadow"></div>
                    </div>
                    <div class="card__data">
                        <h3 class="card__name">${service.nomService}</h3>
                        <p class="card__description">${service.descriptionService}</p>
                        <a href="${data.isLoggedIn ? `../pages/rdv.php?service=${encodeURIComponent(service.nomService)}` : '#'}" 
                           class="card__button rdv-button"
                           data-service="${service.nomService}">
                            Prendre un RDV
                        </a>
                        
                    </div>
                </article>
            `).join('');

            // Gestion des erreurs d'image
            document.querySelectorAll('.card__img').forEach(img => {
                img.addEventListener('error', function() {
                    this.src = '../images/default.png';
                });
            });

            // Initialisation Swiper avec boucle infinie
            new Swiper(".card__container", {
                loop: true,
                spaceBetween: 32,
                grabCursor: true,
                slidesPerView: 1,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    600: {
                        slidesPerView: 2,
                    },
                    968: {
                        slidesPerView: 3,
                    },
                },
                speed: 500,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                }
            });

            // Gestion des clics sur "Prendre un RDV"
            document.querySelectorAll('.rdv-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!data.isLoggedIn) {
                        e.preventDefault();
                        new bootstrap.Modal(document.getElementById('loginModal')).show();
                    }
                });
            });

            // Gestion des clics sur "Ajouter au panier"
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!data.isLoggedIn) {
                        e.preventDefault();
                        new bootstrap.Modal(document.getElementById('loginModal')).show();
                    } else {
                        alert("Ajouté au panier !"); // Remplace ceci par la vraie logique d'ajout
                    }
                });
            });
        })
        .catch(error => {
            console.error('Erreur:', error);
            const serviceList = document.getElementById("service-list");
            if (serviceList) serviceList.innerHTML = `<p class="error-message">Service indisponible</p>`;
        });
});
