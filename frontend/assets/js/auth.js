window.addEventListener("hashchange", function () {
    if (window.location.hash === "#register") {
        console.log("🔄 Détection de la page `register` !");

        setTimeout(() => {
            let form = document.getElementById("register-form");
            if (form) {
                console.log("✅ Formulaire `register-form` détecté !");
                
                form.addEventListener("submit", function(event) {
                    event.preventDefault();
                    console.log("🚀 Fonction `submit` exécutée !");
                
                    debugger; // Arrête l'exécution ici pour voir si le code atteint `fetch()`
                
                    let pseudo = document.getElementById("pseudo").value;
                    let email = document.getElementById("email").value;
                    let password = document.getElementById("password").value;
                
                    let userData = { pseudo, email, password };
                    console.log("📤 Données envoyées :", userData);
                
                    fetch("http://localhost/backend/routes/users.php?action=register", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(userData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("📩 Réponse API :", data);
                        if (data.message.includes("Inscription réussie")) {
                            alert("✅ Inscription réussie !");
                            window.location.hash = "#login"; // Redirection vers la page de connexion
                        } else {
                            alert("❌ " + data.message);
                        }
                    })
                    .catch(error => console.error("❌ Erreur lors de la requête API :", error));
                });
                



// Déconnexion
function logout() {
    localStorage.removeItem("token");
    localStorage.removeItem("role");
    window.location.hash = "login";
}

// Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return localStorage.getItem("token") !== null;
}

// Vérifier le rôle de l'utilisateur
function getUserRole() {
    return localStorage.getItem("role") || "user";
}
