<?php
error_reporting(E_ALL);
error_log("🔍 DEBUG : Requête POST reçue pour /noter");
ini_set('display_errors', 1);
require_once "../config/database.php";
require_once "../config/jwt.php"; // 🔹 Importer JWT



// Fonction pour récupérer l'user_id depuis le token JWT
function getUserFromToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $token = str_replace("Bearer ", "", $headers['Authorization']);
        return JWTHandler::validateToken($token);
    }
    return null;
}


require_once "../config/database.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT * FROM rides WHERE places_disponibles > 0";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rides);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'book') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->user_id) && !empty($data->ride_id)) {
        // Vérifier si le trajet a encore des places
        $query = "SELECT places_disponibles FROM rides WHERE id = :ride_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":ride_id", $data->ride_id);
        $stmt->execute();

        $ride = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ride && $ride['places_disponibles'] > 0) {
            // Insérer la réservation
            $query = "INSERT INTO reservations (user_id, ride_id) VALUES (:user_id, :ride_id)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":user_id", $data->user_id);
            $stmt->bindParam(":ride_id", $data->ride_id);

            if ($stmt->execute()) {
                // Mettre à jour les places disponibles
                $query = "UPDATE rides SET places_disponibles = places_disponibles - 1 WHERE id = :ride_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":ride_id", $data->ride_id);
                $stmt->execute();

                echo json_encode(["message" => "Réservation confirmée"]);
            } else {
                echo json_encode(["message" => "Erreur lors de la réservation"]);
            }
        } else {
            echo json_encode(["message" => "Plus de places disponibles"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes"]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'addRide') {
    $data = json_decode(file_get_contents("php://input"), true);

    // 🔍 Vérifier quel champ est absent
    if (!$data || !isset($data['depart']) || !isset($data['arrivee']) || !isset($data['prix']) || !isset($data['places_disponibles'])) {
        echo json_encode([
            "message" => "Données incomplètes",
            "details" => [
                "depart" => isset($data['depart']) ? "OK" : "MANQUANT",
                "arrivee" => isset($data['arrivee']) ? "OK" : "MANQUANT",
                "prix" => isset($data['prix']) ? "OK" : "MANQUANT",
                "places_disponibles" => isset($data['places_disponibles']) ? "OK" : "MANQUANT"
            ]
        ]);
        exit;
    }

    // Vérifier que les valeurs ne sont pas vides
    if (trim($data['depart']) === "" || trim($data['arrivee']) === "" || !is_numeric($data['prix']) || !is_numeric($data['places_disponibles'])) {
        echo json_encode(["message" => "Tous les champs sont obligatoires et doivent être valides"]);
        exit;
    }

    // Vérifier si l'utilisateur est connecté (token JWT)
    $chauffeur_id = getUserFromToken();
    if (!$chauffeur_id) {
        echo json_encode(["message" => "Accès refusé, vous devez être connecté"]);
        exit;
    }

    // 🔹 Insérer le trajet en base de données
    $query = "INSERT INTO rides (depart, arrivee, prix, places_disponibles, user_id) 
              VALUES (:depart, :arrivee, :prix, :places_disponibles, :user_id)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":depart", $data['depart']);
    $stmt->bindParam(":arrivee", $data['arrivee']);
    $stmt->bindParam(":prix", $data['prix']);
    $stmt->bindParam(":places_disponibles", $data['places_disponibles']);
    $stmt->bindParam(":user_id", $chauffeur_id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Trajet ajouté avec succès", "ride_id" => $conn->lastInsertId()]);
    } else {
        echo json_encode(["message" => "Erreur lors de l'ajout du trajet"]);
    }
}
      

if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'updateRide') {
    $data = json_decode(file_get_contents("php://input"));
    $user_id = getUserFromToken(); // 🔐 Vérifier le token JWT

    if ($user_id && !empty($data->ride_id) && !empty($data->depart) && !empty($data->arrivee) && !empty($data->prix) && !empty($data->places_disponibles)) {
        // Vérifier si le trajet appartient bien à l'utilisateur connecté
        $query = "SELECT user_id FROM rides WHERE id = :ride_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":ride_id", $data->ride_id);
        $stmt->execute();
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ride) {
            echo json_encode(["message" => "Erreur : Ce trajet n'existe pas"]);
            exit;
        }

        if ($ride['user_id'] != $user_id) {
            echo json_encode(["message" => "Accès refusé : Vous n'êtes pas propriétaire du trajet"]);
            exit;
        }

        // Mettre à jour le trajet
        $query = "UPDATE rides SET depart = :depart, arrivee = :arrivee, prix = :prix, places_disponibles = :places_disponibles WHERE id = :ride_id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":depart", $data->depart);
        $stmt->bindParam(":arrivee", $data->arrivee);
        $stmt->bindParam(":prix", $data->prix);
        $stmt->bindParam(":places_disponibles", $data->places_disponibles);
        $stmt->bindParam(":ride_id", $data->ride_id);
        $stmt->bindParam(":user_id", $user_id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Trajet mis à jour avec succès"]);
        } else {
            echo json_encode(["message" => "Erreur lors de la mise à jour"]);
        }
    } else {
        echo json_encode(["message" => "Accès refusé ou données incomplètes"]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'deleteRide') {
    $data = json_decode(file_get_contents("php://input"));
    $user_id = getUserFromToken(); // 🔐 Vérifier le token JWT

    if ($user_id && !empty($data->ride_id)) {
        // Vérifier si l'utilisateur est bien propriétaire du trajet
        $query = "SELECT user_id FROM rides WHERE id = :ride_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":ride_id", $data->ride_id);
        $stmt->execute();
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ride) {
            echo json_encode(["message" => "Erreur : Ce trajet n'existe pas"]);
            exit;
        }

        if ($ride['user_id'] != $user_id) {
            echo json_encode(["message" => "Accès refusé : Vous n'êtes pas propriétaire du trajet"]);
            exit;
        }

        // Supprimer le trajet
        $query = "DELETE FROM rides WHERE id = :ride_id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":ride_id", $data->ride_id);
        $stmt->bindParam(":user_id", $user_id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Trajet supprimé avec succès"]);
        } else {
            echo json_encode(["message" => "Erreur lors de la suppression"]);
        }
    } else {
        echo json_encode(["message" => "Accès refusé ou données incomplètes"]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getRides') {
    $query = "SELECT r.id, r.depart, r.arrivee, r.prix, r.places_disponibles, r.user_id, 
                     u.pseudo, u.email
              FROM rides r
              JOIN users u ON r.user_id = u.id
              WHERE r.places_disponibles > 0
              ORDER BY r.id DESC"; // Tri du plus récent au plus ancien

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rides) {
        echo json_encode(["message" => "Liste des trajets", "data" => $rides]);
    } else {
        echo json_encode(["message" => "Aucun trajet disponible"]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'filterRides') {
    $conditions = [];
    $params = [];

    // 🔹 Filtrer par prix maximum
    if (!empty($_GET['max_price'])) {
        $conditions[] = "r.prix <= :max_price";
        $params[':max_price'] = $_GET['max_price'];
    }

    // 🔹 Filtrer par voiture écologique (voiture électrique)
    if (!empty($_GET['ecologique']) && $_GET['ecologique'] == "true") {
        $conditions[] = "v.type_energie = 'électrique'";
    }

    // 🔹 Filtrer par durée du trajet maximum
    if (!empty($_GET['max_duration'])) {
        $conditions[] = "r.duree <= :max_duration";
        $params[':max_duration'] = $_GET['max_duration'];
    }

    // 🔹 Filtrer par note minimale du chauffeur
    if (!empty($_GET['min_rating'])) {
        $conditions[] = "u.note_moyenne >= :min_rating";
        $params[':min_rating'] = $_GET['min_rating'];
    }

    // 🔹 Construire la requête SQL dynamique
    $query = "SELECT r.id, r.depart, r.arrivee, r.prix, r.places_disponibles, r.duree,
                     u.pseudo, u.note_moyenne, v.type_energie
              FROM rides r
              JOIN users u ON r.user_id = u.id
              LEFT JOIN vehicles v ON r.user_id = v.user_id";

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY r.id DESC"; // Trier du plus récent au plus ancien

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rides) {
        echo json_encode(["message" => "Résultats filtrés", "data" => $rides]);
    } else {
        echo json_encode(["message" => "Aucun trajet trouvé avec ces critères"]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'reserver') {
    $data = json_decode(file_get_contents("php://input"));
    $user_id = getUserFromToken(); // 🔐 Vérifier le token JWT

    if ($user_id && !empty($data->ride_id)) {
        // Vérifier si le trajet existe et a des places disponibles
        $query = "SELECT prix, places_disponibles FROM rides WHERE id = :ride_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":ride_id", $data->ride_id);
        $stmt->execute();
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ride) {
            echo json_encode(["message" => "Erreur : Ce trajet n'existe pas"]);
            exit;
        }

        if ($ride['places_disponibles'] <= 0) {
            echo json_encode(["message" => "Plus de places disponibles"]);
            exit;
        }

        // Vérifier si l'utilisateur a assez de crédits
        $query = "SELECT credits FROM users WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['credits'] < $ride['prix']) {
            echo json_encode(["message" => "Crédits insuffisants"]);
            exit;
        }

        // Déduire les crédits et mettre à jour les places
        $conn->beginTransaction();
        try {
            $query = "UPDATE users SET credits = credits - :prix WHERE id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":prix", $ride['prix']);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $query = "UPDATE rides SET places_disponibles = places_disponibles - 1 WHERE id = :ride_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":ride_id", $data->ride_id);
            $stmt->execute();

            $query = "INSERT INTO reservations (ride_id, user_id) VALUES (:ride_id, :user_id)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":ride_id", $data->ride_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            $conn->commit();
            echo json_encode(["message" => "Réservation confirmée"]);
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(["message" => "Erreur lors de la réservation"]);
        }
    } else {
        echo json_encode(["message" => "Accès refusé ou données incomplètes"]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'historique') {
    $user_id = getUserFromToken(); // 🔐 Vérifier le token JWT

    if ($user_id) {
        $query = "SELECT r.id AS ride_id, r.depart, r.arrivee, r.prix, r.duree, r.places_disponibles,
                         res.date_reservation, u.pseudo AS chauffeur, u.note_moyenne
                  FROM reservations res
                  JOIN rides r ON res.ride_id = r.id
                  JOIN users u ON r.user_id = u.id
                  WHERE res.user_id = :user_id
                  ORDER BY res.date_reservation DESC"; // Trier du plus récent au plus ancien

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($reservations) {
            echo json_encode(["message" => "Historique des réservations", "data" => $reservations]);
        } else {
            echo json_encode(["message" => "Aucune réservation trouvée"]);
        }
    } else {
        echo json_encode(["message" => "Accès refusé"]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'annuler') {
    $data = json_decode(file_get_contents("php://input"));
    $user_id = getUserFromToken(); // 🔐 Vérifier le token JWT

    if ($user_id && !empty($data->ride_id)) {
        // Vérifier si l'utilisateur est un passager ou le chauffeur
        $query = "SELECT r.user_id AS chauffeur_id, r.prix, res.user_id AS passager_id 
                  FROM reservations res
                  JOIN rides r ON res.ride_id = r.id
                  WHERE res.ride_id = :ride_id AND (res.user_id = :user_id OR r.user_id = :user_id)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":ride_id", $data->ride_id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$reservations) {
            echo json_encode(["message" => "Aucune réservation trouvée pour ce trajet"]);
            exit;
        }

        $conn->beginTransaction();
        try {
            if ($reservations[0]['chauffeur_id'] == $user_id) {
                // 🔹 Le chauffeur annule le trajet, il faut rembourser tous les passagers
                foreach ($reservations as $reservation) {
                    $query = "UPDATE users SET credits = credits + :prix WHERE id = :user_id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(":prix", $reservation['prix']);
                    $stmt->bindParam(":user_id", $reservation['passager_id']);
                    $stmt->execute();
                }

                // 🔹 Supprimer toutes les réservations du trajet
                $query = "DELETE FROM reservations WHERE ride_id = :ride_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":ride_id", $data->ride_id);
                $stmt->execute();

                // 🔹 Supprimer le trajet
                $query = "DELETE FROM rides WHERE id = :ride_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":ride_id", $data->ride_id);
                $stmt->execute();

                $conn->commit();
                echo json_encode(["message" => "Trajet annulé, tous les passagers ont été remboursés"]);
            } else {
                // 🔹 Le passager annule sa réservation, il est remboursé
                $query = "UPDATE users SET credits = credits + :prix WHERE id = :user_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":prix", $reservations[0]['prix']);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();

                // 🔹 Supprimer la réservation
                $query = "DELETE FROM reservations WHERE ride_id = :ride_id AND user_id = :user_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":ride_id", $data->ride_id);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();

                // 🔹 Augmenter le nombre de places disponibles
                $query = "UPDATE rides SET places_disponibles = places_disponibles + 1 WHERE id = :ride_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":ride_id", $data->ride_id);
                $stmt->execute();

                $conn->commit();
                echo json_encode(["message" => "Réservation annulée et crédits remboursés"]);
            }
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(["message" => "Erreur lors de l'annulation"]);
        }
    } else {
        echo json_encode(["message" => "Accès refusé ou données incomplètes"]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'noter') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = getUserFromToken(); // 🔐 Vérifier le token JWT

    if (!$user_id) {
        echo json_encode(["message" => "Accès refusé, vous devez être connecté"]);
        exit;
    }

    if (empty($data['ride_id']) || empty($data['note']) || !isset($data['commentaire'])) {
        echo json_encode(["message" => "Données incomplètes, assurez-vous d'envoyer ride_id, note et commentaire"]);
        exit;
    }

    // 🔍 Vérifier si l'utilisateur a bien réservé ce trajet
    $query = "SELECT res.user_id AS passager_id, r.user_id AS chauffeur_id 
              FROM reservations res
              JOIN rides r ON res.ride_id = r.id
              WHERE res.ride_id = :ride_id AND res.user_id = :user_id";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":ride_id", $data['ride_id']);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // 🔍 Debug : Afficher le résultat de la réservation
    if (!$result) {
        error_log("❌ DEBUG : L'utilisateur ID $user_id n'a pas réservé le trajet ID " . $data['ride_id']);
        echo json_encode(["message" => "Erreur : Vous n'avez pas participé à ce trajet"]);
        exit;
    }

    error_log("✅ DEBUG : L'utilisateur ID $user_id a bien réservé le trajet ID " . $data['ride_id']);
    $chauffeur_id = $result['chauffeur_id'];

    // Vérifier si l'utilisateur a déjà noté ce trajet
    $query = "SELECT id FROM ratings WHERE ride_id = :ride_id AND passenger_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":ride_id", $data['ride_id']);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $existing_rating = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_rating) {
        echo json_encode(["message" => "Vous avez déjà noté ce trajet"]);
        exit;
    }

    // 🔍 Debug : Vérifier avant insertion
    error_log("🔍 DEBUG : Insertion d'une note $data[note] pour le chauffeur ID $chauffeur_id");

    // Ajouter la nouvelle note
    $query = "INSERT INTO ratings (ride_id, passenger_id, driver_id, note, commentaire) 
              VALUES (:ride_id, :passenger_id, :driver_id, :note, :commentaire)";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":ride_id", $data['ride_id']);
    $stmt->bindParam(":passenger_id", $user_id);
    $stmt->bindParam(":driver_id", $chauffeur_id);
    $stmt->bindParam(":note", $data['note']);
    $stmt->bindParam(":commentaire", $data['commentaire']);
    
    if ($stmt->execute()) {
        // Mettre à jour la note moyenne du chauffeur
        $query = "UPDATE users 
                  SET note_moyenne = (SELECT AVG(note) FROM ratings WHERE driver_id = :driver_id) 
                  WHERE id = :driver_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":driver_id", $chauffeur_id);
        $stmt->execute();

        echo json_encode(["message" => "Votre avis a été enregistré"]);
    } else {
        echo json_encode(["message" => "Erreur lors de l'enregistrement de votre avis"]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'avis') {
    if (!isset($_GET['chauffeur_id'])) {
        echo json_encode(["message" => "Données incomplètes, veuillez fournir chauffeur_id"]);
        exit;
    }

    $chauffeur_id = $_GET['chauffeur_id'];

    // 🔍 Vérifier si le chauffeur existe
    $query = "SELECT pseudo, note_moyenne FROM users WHERE id = :chauffeur_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":chauffeur_id", $chauffeur_id);
    $stmt->execute();
    $chauffeur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chauffeur) {
        echo json_encode(["message" => "Chauffeur non trouvé"]);
        exit;
    }

    // 🔍 Récupérer les avis sur ce chauffeur
    $query = "SELECT r.note, r.commentaire, u.pseudo AS passager_pseudo, r.date_notation 
              FROM ratings r
              JOIN users u ON r.passenger_id = u.id
              WHERE r.driver_id = :chauffeur_id
              ORDER BY r.date_notation DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":chauffeur_id", $chauffeur_id);
    $stmt->execute();
    $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "chauffeur" => $chauffeur,
        "avis" => $avis
    ]);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'historique') {
    $user_id = getUserFromToken(); // 🔐 Vérifier le token JWT

    if (!$user_id) {
        echo json_encode(["message" => "Accès refusé, vous devez être connecté"]);
        exit;
    }

    // 🔍 Récupérer les trajets en tant que chauffeur
    $query = "SELECT id, depart, arrivee, prix, places_disponibles, created_at 
              FROM rides 
              WHERE user_id = :user_id
              ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $trajets_chauffeur = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔍 Récupérer les trajets en tant que passager
    $query = "SELECT r.id, r.depart, r.arrivee, r.prix, r.created_at 
              FROM reservations res
              JOIN rides r ON res.ride_id = r.id
              WHERE res.user_id = :user_id
              ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $trajets_passager = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "chauffeur" => $trajets_chauffeur,
        "passager" => $trajets_passager
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'annuler') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = getUserFromToken();

    if (!$user_id) {
        echo json_encode(["message" => "Accès refusé, vous devez être connecté"]);
        exit;
    }

    if (empty($data['ride_id'])) {
        echo json_encode(["message" => "Données incomplètes, veuillez fournir ride_id"]);
        exit;
    }

    $ride_id = $data['ride_id'];

    // Vérifier si l'utilisateur est bien le créateur du trajet
    $query = "SELECT user_id FROM rides WHERE id = :ride_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":ride_id", $ride_id);
    $stmt->execute();
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ride || $ride['user_id'] != $user_id) {
        echo json_encode(["message" => "Accès refusé, vous n'êtes pas le propriétaire du trajet"]);
        exit;
    }

    // Supprimer le trajet et toutes ses réservations
    $query = "DELETE FROM rides WHERE id = :ride_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":ride_id", $ride_id);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Trajet annulé avec succès"]);
    } else {
        echo json_encode(["message" => "Erreur lors de l'annulation du trajet"]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'stats') {
    $user_id = getUserFromToken();

    if (!$user_id) {
        echo json_encode(["message" => "Accès refusé, vous devez être connecté"]);
        exit;
    }

    // Vérifier si l'utilisateur est administrateur
    $query = "SELECT role FROM users WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || $user['role'] != 'admin') {
        echo json_encode(["message" => "Accès refusé, vous devez être administrateur"]);
        exit;
    }

    // Nombre total de trajets
    $query = "SELECT COUNT(*) as total_trajets FROM rides";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $trajets = $stmt->fetch(PDO::FETCH_ASSOC);

    // Nombre total d'utilisateurs
    $query = "SELECT COUNT(*) as total_utilisateurs FROM users";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $utilisateurs = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "total_trajets" => $trajets['total_trajets'],
        "total_utilisateurs" => $utilisateurs['total_utilisateurs']
    ]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'notification') {
    $data = json_decode(file_get_contents("php://input"), true);
    $user_id = getUserFromToken();

    if (!$user_id) {
        echo json_encode(["message" => "Accès refusé, vous devez être connecté"]);
        exit;
    }

    if (empty($data['destinataire_id']) || empty($data['message'])) {
        echo json_encode(["message" => "Données incomplètes, veuillez fournir destinataire_id et message"]);
        exit;
    }

    $destinataire_id = $data['destinataire_id'];
    $message = $data['message'];

    $query = "INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(":user_id", $destinataire_id);
    $stmt->bindParam(":message", $message);
    
    if ($stmt->execute()) {
        echo json_encode(["message" => "Notification envoyée"]);
    } else {
        echo json_encode(["message" => "Erreur lors de l'envoi de la notification"]);
    }
}

?>