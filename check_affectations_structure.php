<?php
// Script pour vérifier la structure de la table affectations
require_once 'config.php';

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Structure de la table affectations</h1>";

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Vérifier si la table affectations existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'affectations'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p style='color:green'>La table 'affectations' existe dans la base de données.</p>";
        
        // Afficher la structure de la table
        $stmt = $pdo->query("DESCRIBE affectations");
        $columns = $stmt->fetchAll();
        
        echo "<h2>Colonnes de la table affectations</h2>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Nom</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Afficher quelques données de la table
        $stmt = $pdo->query("SELECT * FROM affectations LIMIT 5");
        $data = $stmt->fetchAll();
        
        if (!empty($data)) {
            echo "<h2>Exemples de données dans la table affectations</h2>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else {
            echo "<p>La table 'affectations' est vide.</p>";
        }
    } else {
        echo "<p style='color:red'>La table 'affectations' n'existe pas dans la base de données.</p>";
    }
    
    // Vérifier la structure de la table unites_enseignements
    $stmt = $pdo->query("SHOW TABLES LIKE 'unites_enseignements'");
    $ueTableExists = $stmt->rowCount() > 0;
    
    if ($ueTableExists) {
        echo "<h2>Structure de la table unites_enseignements</h2>";
        
        // Afficher la structure de la table
        $stmt = $pdo->query("DESCRIBE unites_enseignements");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Nom</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Afficher quelques données de la table
        $stmt = $pdo->query("SELECT * FROM unites_enseignements LIMIT 5");
        $data = $stmt->fetchAll();
        
        if (!empty($data)) {
            echo "<h2>Exemples de données dans la table unites_enseignements</h2>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        } else {
            echo "<p>La table 'unites_enseignements' est vide.</p>";
        }
    } else {
        echo "<p style='color:red'>La table 'unites_enseignements' n'existe pas dans la base de données.</p>";
    }
    
    // Ajouter un lien pour retourner à la page principale
    echo "<p><a href='index.php' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Retour à la page principale</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de base de données: " . $e->getMessage() . "</p>";
}
?>
