function loadPage(page) {
    console.log("🔄 Chargement de la page :", page);
    fetch(`pages/${page}.html`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('app').innerHTML = html;
            console.log("✅ Contenu de la page ajouté au DOM !");
            console.log("📄 Contenu de `#app` :", document.getElementById('app').innerHTML);

            setTimeout(() => {
                let form = document.getElementById("register-form");
                if (form) {
                    console.log("✅ Formulaire `register-form` détecté après ajout au DOM !");
                    form.addEventListener("submit", function(event) {
                        event.preventDefault();
                        console.log("✅ Bouton `S'inscrire` cliqué !");
                    });
                    console.log("✅ Événement `submit` attaché !");
                } else {
                    console.error("❌ Formulaire `register-form` introuvable après ajout au DOM !");
                }
            }, 500);
        })
        .catch(error => console.error("❌ Erreur chargement de la page :", error));
}


// Gestion du routage (hash #)
window.onload = () => {
    let page = window.location.hash.substring(1) || "home";
    loadPage(page);

    document.addEventListener('click', function(event) {
        if (event.target.tagName === 'A' && event.target.getAttribute('data-route')) {
            event.preventDefault();
            let page = event.target.getAttribute('href').replace('.html', '');
            window.location.hash = page;
            loadPage(page);
        }
    });
};

// Fonction pour charger les composants (Header et Footer)
function loadComponent(component, targetId) {
    fetch(`components/${component}.html`)
        .then(response => response.text())
        .then(html => {
            document.getElementById(targetId).innerHTML = html;
        })
        .catch(error => console.error(`Erreur lors du chargement du composant ${component} :`, error));
}

// Gestion du routage
window.onload = () => {
    loadComponent('header', 'header');
    loadComponent('footer', 'footer');

    // Vérifier si une page est demandée via l'URL (hash)
    let page = window.location.hash.substring(1) || "home";
    loadPage(page);

    // Écouteur sur les liens pour éviter le rechargement complet
    document.addEventListener('click', function(event) {
        if (event.target.tagName === 'A' && event.target.getAttribute('data-route')) {
            event.preventDefault();
            let page = event.target.getAttribute('href').replace('.html', '');
            window.location.hash = page;
            loadPage(page);
        }
    });
};
