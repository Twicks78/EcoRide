<?php
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
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->user_id) && !empty($data->depart) && !empty($data->arrivee) && !empty($data->prix) && !empty($data->places_disponibles)) {
        // Vérifier si l'utilisateur existe
        $query = "SELECT id FROM users WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":user_id", $data->user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Ajouter le trajet avec user_id
            $query = "INSERT INTO rides (depart, arrivee, prix, places_disponibles, user_id, created_at) 
                      VALUES (:depart, :arrivee, :prix, :places_disponibles, :user_id, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":depart", $data->depart);
            $stmt->bindParam(":arrivee", $data->arrivee);
            $stmt->bindParam(":prix", $data->prix);
            $stmt->bindParam(":places_disponibles", $data->places_disponibles);
            $stmt->bindParam(":user_id", $data->user_id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Trajet ajouté avec succès"]);
            } else {
                echo json_encode(["message" => "Erreur lors de l'ajout du trajet"]);
            }
        } else {
            echo json_encode(["message" => "Utilisateur non trouvé"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes"]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['action']) && $_GET['action'] === 'updateRide') {
    $data = json_decode(file_get_contents("php://input"), true);
    error_log("Données reçues : " . json_encode($data)); // Debug: afficher les données reçues


    if (!empty($data->ride_id) && !empty($data->user_id) && !empty($data->depart) && !empty($data->arrivee) && !empty($data->prix) && !empty($data->places_disponibles)) {
        // Vérifier si le trajet appartient bien au chauffeur
        $query = "SELECT id FROM rides WHERE id = :ride_id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":ride_id", $data->ride_id);
        $stmt->bindParam(":user_id", $data->user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Mettre à jour le trajet
            $query = "UPDATE rides SET depart = :depart, arrivee = :arrivee, prix = :prix, places_disponibles = :places_disponibles WHERE id = :ride_id AND user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":depart", $data->depart);
            $stmt->bindParam(":arrivee", $data->arrivee);
            $stmt->bindParam(":prix", $data->prix);
            $stmt->bindParam(":places_disponibles", $data->places_disponibles);
            $stmt->bindParam(":ride_id", $data->ride_id);
            $stmt->bindParam(":user_id", $data->user_id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Trajet mis à jour avec succès"]);
            } else {
                echo json_encode(["message" => "Erreur lors de la mise à jour"]);
            }
        } else {
            echo json_encode(["message" => "Aucun trajet trouvé ou vous n'êtes pas le propriétaire"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes"]);
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['action']) && $_GET['action'] === 'deleteRide') {
    $data = json_decode(file_get_contents("php://input"), true);
    error_log("Données reçues : " . json_encode($data)); // Debug: afficher les données reçues


    if (!empty($data['ride_id']) && !empty($data['user_id'])) {
        // Vérifier si le trajet appartient bien au chauffeur
        $query = "SELECT id FROM rides WHERE id = :ride_id AND user_id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":ride_id", $data['ride_id']);
        $stmt->bindParam(":user_id", $data['user_id']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Supprimer le trajet
            $query = "DELETE FROM rides WHERE id = :ride_id AND user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":ride_id", $data['ride_id']);
            $stmt->bindParam(":user_id", $data['user_id']);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Trajet supprimé avec succès"]);
            } else {
                echo json_encode(["message" => "Erreur lors de la suppression"]);
            }
        } else {
            echo json_encode(["message" => "Aucun trajet trouvé ou vous n'êtes pas le propriétaire"]);
        }
    } else {
        echo json_encode(["message" => "Données incomplètes"]);
    }
}

?>