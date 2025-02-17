#!/bin/bash

# Création des dossiers
echo "Création de l'arborescence du projet EcoRide..."
mkdir -p backend/config backend/routes backend/controllers backend/models backend/views
mkdir -p database/migrations database/seeds
echo "Dossiers créés avec succès."

# Création des fichiers
touch backend/config/database.php

touch backend/routes/index.php

touch backend/routes/users.php

touch backend/routes/rides.php

touch backend/controllers/userController.php

touch backend/controllers/rideController.php

touch backend/models/User.php

touch backend/models/Ride.php

touch backend/views/index.php

touch database/database.sql

touch backend/index.php

touch backend/.htaccess

echo "Fichiers créés avec succès."

# Vérification
echo "Vérification de la structure créée :"
tree backend database
