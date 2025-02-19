API pour la plateforme de covoiturage **EcoRide**.

## 📌 Prérequis

Avant de lancer l'API, assurez-vous d'avoir installé :

- **PHP 8+** (avec `pdo_mysql` activé)
- **MySQL ou MariaDB** (base de données)
- **Apache ou Nginx**
- **Composer** (gestionnaire de dépendances PHP)

## 🚀 Installation

1️⃣ **Clonez le projet :**

git clone https://github.com/Twicks78/EcoRide.git
cd EcoRide/backend

2️⃣ **Installez les dépendances PHP :**

composer install

3️⃣ **Configurez votre base de données :**
- Importez le fichier `database.sql` dans MySQL.
- Configurez `backend/config/database.php` avec vos identifiants.

4️⃣ **Lancez le serveur PHP (si vous n'avez pas Apache) :**

php -S localhost:3000 -t backend

5️⃣ **Testez l'API avec Postman ou cURL :**

curl -X POST http://localhost:3000/routes/users.php?action=register -d '{"pseudo":"test","email":"test@example.com","password":"123456"}' -H "Content-Type: application/json"

## 📡 Endpoints API (Exemples)

| Méthode | Endpoint | Description |
|---------|---------|-------------|
| `POST`  | `/routes/users.php?action=register` | Inscription utilisateur |
| `POST`  | `/routes/users.php?action=login` | Connexion utilisateur |
| `GET`   | `/routes/rides.php` | Voir tous les trajets |
| `POST`  | `/routes/rides.php?action=create` | Créer un trajet |

## 📌 Déploiement sur Fly.io

1️⃣ **Connectez-vous à Fly.io :**

flyctl auth login

2️⃣ **Lancez le déploiement :**

flyctl launch
flyctl deploy

3️⃣ **Accédez à votre API en ligne 🚀 :**

https://ecoride.fly.dev

💡 **Contact & Support**  
Si vous avez un problème, ouvrez une **issue** sur GitHub ! 🚀
