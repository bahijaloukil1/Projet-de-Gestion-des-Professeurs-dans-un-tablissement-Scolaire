<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier de configuration
require_once 'config.php';

// Fonction pour afficher un message
function display_message($message, $type = 'info') {
    $color = 'black';
    switch ($type) {
        case 'success':
            $color = 'green';
            break;
        case 'error':
            $color = 'red';
            break;
        case 'warning':
            $color = 'orange';
            break;
    }
    echo "<div style='color: $color; margin: 5px 0;'>$message</div>";
}

// Fonction pour exécuter une requête SQL
function execute_query($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return true;
    } catch (PDOException $e) {
        display_message("Erreur SQL: " . $e->getMessage(), 'error');
        return false;
    }
}

// Fonction pour vérifier si une table existe
function table_exists($pdo, $table_name) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$table_name'")->fetchAll();
        return count($result) > 0;
    } catch (PDOException $e) {
        display_message("Erreur lors de la vérification de la table $table_name: " . $e->getMessage(), 'error');
        return false;
    }
}

// Fonction pour créer la table departement
function create_departement_table($pdo) {
    $query = "
        CREATE TABLE IF NOT EXISTS departement (
            id_departement INT AUTO_INCREMENT PRIMARY KEY,
            nom_departement VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (execute_query($pdo, $query)) {
        display_message("Table 'departement' créée avec succès", 'success');
        return true;
    } else {
        display_message("Erreur lors de la création de la table 'departement'", 'error');
        return false;
    }
}

// Fonction pour créer la table specialite
function create_specialite_table($pdo) {
    $query = "
        CREATE TABLE IF NOT EXISTS specialite (
            id_specialite INT AUTO_INCREMENT PRIMARY KEY,
            nom_specialite VARCHAR(255) NOT NULL,
            id_departement INT NOT NULL,
            FOREIGN KEY (id_departement) REFERENCES departement(id_departement) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (execute_query($pdo, $query)) {
        display_message("Table 'specialite' créée avec succès", 'success');
        return true;
    } else {
        display_message("Erreur lors de la création de la table 'specialite'", 'error');
        return false;
    }
}

// Fonction pour insérer des données de test dans la table departement
function insert_test_departements($pdo) {
    $departements = [
        ['Informatique/Mathématiques'],
        ['Physique']
    ];
    
    $query = "INSERT INTO departement (nom_departement) VALUES (?)";
    $success = true;
    
    foreach ($departements as $departement) {
        if (!execute_query($pdo, $query, $departement)) {
            $success = false;
        }
    }
    
    if ($success) {
        display_message("Données de test insérées dans la table 'departement'", 'success');
        return true;
    } else {
        display_message("Erreur lors de l'insertion des données de test dans la table 'departement'", 'error');
        return false;
    }
}

// Fonction pour insérer des données de test dans la table specialite
function insert_test_specialites($pdo) {
    // Récupérer les IDs des départements
    $dept_info = $pdo->query("SELECT id_departement, nom_departement FROM departement")->fetchAll(PDO::FETCH_ASSOC);
    $dept_ids = [];
    
    foreach ($dept_info as $dept) {
        $dept_ids[$dept['nom_departement']] = $dept['id_departement'];
    }
    
    // Définir les spécialités par département
    $specialites = [
        // Spécialités pour le département Informatique/Mathématiques
        ['Développement logiciel', $dept_ids['Informatique/Mathématiques']],
        ['Intelligence Artificielle', $dept_ids['Informatique/Mathématiques']],
        ['Mathématiques Appliquées', $dept_ids['Informatique/Mathématiques']],
        ['Développement Web', $dept_ids['Informatique/Mathématiques']],
        ['Base de Données', $dept_ids['Informatique/Mathématiques']],
        ['Réseaux', $dept_ids['Informatique/Mathématiques']],
        
        // Spécialités pour le département Physique
        ['Physique Fondamentale', $dept_ids['Physique']],
        ['Physique Appliquée', $dept_ids['Physique']],
        ['Électronique', $dept_ids['Physique']],
        ['Physique Nucléaire', $dept_ids['Physique']]
    ];
    
    $query = "INSERT INTO specialite (nom_specialite, id_departement) VALUES (?, ?)";
    $success = true;
    
    foreach ($specialites as $specialite) {
        if (!execute_query($pdo, $query, $specialite)) {
            $success = false;
        }
    }
    
    if ($success) {
        display_message("Données de test insérées dans la table 'specialite'", 'success');
        return true;
    } else {
        display_message("Erreur lors de l'insertion des données de test dans la table 'specialite'", 'error');
        return false;
    }
}

// Afficher l'en-tête
echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Configuration de la base de données</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .container { max-width: 800px; margin: 0 auto; }
        .btn { display: inline-block; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
        .btn:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Configuration de la base de données</h1>";

try {
    // Connexion à la base de données
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    display_message("Connexion à la base de données réussie", 'success');
    
    // Vérifier et créer la table departement si nécessaire
    if (table_exists($pdo, 'departement')) {
        display_message("La table 'departement' existe déjà", 'info');
        
        // Afficher les départements existants
        $departements = $pdo->query("SELECT id_departement, nom_departement FROM departement")->fetchAll();
        if (count($departements) > 0) {
            echo "<h2>Départements existants</h2>";
            echo "<ul>";
            foreach ($departements as $departement) {
                echo "<li>{$departement['nom_departement']} (ID: {$departement['id_departement']})</li>";
            }
            echo "</ul>";
        } else {
            display_message("La table 'departement' est vide", 'warning');
            insert_test_departements($pdo);
        }
    } else {
        if (create_departement_table($pdo)) {
            insert_test_departements($pdo);
        }
    }
    
    // Vérifier et créer la table specialite si nécessaire
    if (table_exists($pdo, 'specialite')) {
        display_message("La table 'specialite' existe déjà", 'info');
        
        // Afficher les spécialités existantes
        $specialites = $pdo->query("
            SELECT s.id_specialite, s.nom_specialite, s.id_departement, d.nom_departement
            FROM specialite s
            JOIN departement d ON s.id_departement = d.id_departement
            ORDER BY d.nom_departement, s.nom_specialite
        ")->fetchAll();
        
        if (count($specialites) > 0) {
            echo "<h2>Spécialités existantes</h2>";
            echo "<ul>";
            foreach ($specialites as $specialite) {
                echo "<li>{$specialite['nom_specialite']} (ID: {$specialite['id_specialite']}) - Département: {$specialite['nom_departement']}</li>";
            }
            echo "</ul>";
        } else {
            display_message("La table 'specialite' est vide", 'warning');
            insert_test_specialites($pdo);
        }
    } else {
        if (create_specialite_table($pdo)) {
            insert_test_specialites($pdo);
        }
    }
    
} catch (PDOException $e) {
    display_message("Erreur de connexion à la base de données: " . $e->getMessage(), 'error');
}

// Afficher le pied de page
echo "
        <a href='gestion_chef_departement.php' class='btn'>Retour à la gestion des chefs de département</a>
    </div>
</body>
</html>";
?>
