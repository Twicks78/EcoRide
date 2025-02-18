// Fonction pour charger une page HTML dynamiquement
function loadPage(page) {
    fetch(`pages/${page}.html`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('app').innerHTML = html;
        })
        .catch(error => console.error("Erreur lors du chargement de la page :", error));
}

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
