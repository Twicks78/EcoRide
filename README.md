README.md pour EcoRide
markdown
Copier
# 🚗 EcoRide - Plateforme de Covoiturage Écologique 🌱

## 📖 Description
**EcoRide** est une plateforme de covoiturage qui permet aux utilisateurs de proposer et réserver des trajets en voiture. L’objectif est de **réduire l’impact environnemental** des déplacements en encourageant l’utilisation de véhicules partagés.

Cette API permet :
- 🔐 **Inscription et connexion avec JWT**
- 🚗 **Ajout, modification et suppression de trajets**
- 🛒 **Réservation de trajets**
- 📊 **Historique des trajets et système de crédits**

---

## 🏗️ **Installation et Configuration**
### 📌 **1. Cloner le projet**
```sh
git clone https://github.com/Twicks78/EcoRide.git
cd EcoRide
📌 2. Installer les dépendances
Assure-toi d’avoir Composer installé, puis exécute :

composer install

📌 3. Configurer la base de données
Crée une base de données ecoride dans MySQL.
Exécute le fichier SQL de création des tables :

mysql -u root -p ecoride < database/schema.sql

Configure la connexion à la base dans backend/config/database.php :

$host = "localhost";
$db_name = "ecoride";
$username = "root";
$password = "";

🚀 Utilisation de l’API
🔐 1. Inscription d’un utilisateur
Méthode : POST
URL : /backend/routes/users.php?action=register
Body JSON :
json
Copier
{
    "pseudo": "JohnDoe",
    "email": "johndoe@email.com",
    "password": "123456"
}
Réponse :
json
Copier
{"message": "Inscription réussie"}
🔑 2. Connexion et Génération du Token JWT
Méthode : POST
URL : /backend/routes/users.php?action=login
Body JSON :
json
Copier
{
    "email": "johndoe@email.com",
    "password": "123456"
}
Réponse :
json
Copier
{
    "message": "Connexion réussie",
    "token": "eyJhbGciOiJIUzI1..."
}
📌 Utilise ce token JWT pour les autres requêtes protégées.

🚗 3. Ajouter un trajet (🚫 Authentification requise)
Méthode : POST
URL : /backend/routes/rides.php?action=addRide
Headers :
makefile
Copier
Authorization: Bearer <TON_TOKEN>
Body JSON :
json
Copier
{
    "depart": "Paris",
    "arrivee": "Lyon",
    "prix": 25,
    "places_disponibles": 3
}
Réponse :
json
Copier
{"message": "Trajet ajouté avec succès"}
📝 4. Voir ses trajets
Méthode : GET
URL : /backend/routes/rides.php?action=myRides
Headers :
makefile
Copier
Authorization: Bearer <TON_TOKEN>
Réponse :
json
Copier
[
    {
        "id": 1,
        "depart": "Paris",
        "arrivee": "Lyon",
        "prix": 25,
        "places_disponibles": 3
    }
]
🛠️ Technologies Utilisées
Back-end : PHP 8, MySQL, MariaDB
Authentification : JWT (JSON Web Token)
Déploiement : Fly.io, Heroku, Vercel (en option)
Gestion des dépendances : Composer
Tests API : Postman
🤝 Contribuer
Les contributions sont les bienvenues ! 🚀

Fork le projet
Crée une branche : git checkout -b feature/ma-fonctionnalite
Commit tes modifications : git commit -m "Ajout d'une nouvelle fonctionnalité"
Push sur GitHub : git push origin feature/ma-fonctionnalite
Ouvre une Pull Request
📜 Licence
Ce projet est sous licence MIT.
🔗 Voir la licence

✨ Auteur
👤 Twicks_78
📧 Contact : deoliveiracyril@gmail.com
🔗 GitHub : Twicks78