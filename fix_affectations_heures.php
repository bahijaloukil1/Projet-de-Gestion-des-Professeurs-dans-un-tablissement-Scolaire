<?php
// Script pour vérifier et ajouter la colonne heures à la table affectations
require_once 'config.php';

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Vérification et ajout de la colonne 'heures' à la table affectations</h1>";

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
    
    // Vérifier si la colonne heures existe dans la table affectations
    $stmt = $pdo->query("SHOW COLUMNS FROM affectations LIKE 'heures'");
    $heuresExists = $stmt->rowCount() > 0;
    
    if (!$heuresExists) {
        echo "<p style='color:red'>La colonne 'heures' n'existe pas dans la table 'affectations'.</p>";
        
        // Ajouter la colonne heures
        $pdo->exec("ALTER TABLE affectations ADD COLUMN heures INT NOT NULL DEFAULT 30");
        echo "<p style='color:green'>La colonne 'heures' a été ajoutée à la table 'affectations' avec succès.</p>";
    } else {
        echo "<p style='color:green'>La colonne 'heures' existe déjà dans la table 'affectations'.</p>";
    }
    
    // Afficher la structure actuelle de la table
    $stmt = $pdo->query("DESCRIBE affectations");
    $columns = $stmt->fetchAll();
    
    echo "<h2>Structure actuelle de la table affectations</h2>";
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
    
    // Vérifier les colonnes manquantes
    $requiredColumns = [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'professeur_id' => 'INT NOT NULL',
        'ue_id' => 'INT NOT NULL',
        'annee' => 'INT DEFAULT ' . date('Y'),
        'semestre' => 'INT DEFAULT 1',
        'date_debut' => 'DATE NOT NULL',
        'date_fin' => 'DATE NULL',
        'heures' => 'INT NOT NULL DEFAULT 30',
        'utilisateur_id' => 'INT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ];
    
    $columnNames = array_column($columns, 'Field');
    $missingColumns = [];
    
    foreach ($requiredColumns as $column => $definition) {
        if (!in_array($column, $columnNames)) {
            $missingColumns[$column] = $definition;
        }
    }
    
    if (!empty($missingColumns)) {
        echo "<h2>Colonnes manquantes</h2>";
        echo "<ul>";
        foreach ($missingColumns as $column => $definition) {
            echo "<li><strong>" . htmlspecialchars($column) . "</strong> : " . htmlspecialchars($definition) . "</li>";
        }
        echo "</ul>";
        
        echo "<form method='post'>";
        echo "<input type='hidden' name='action' value='add_columns'>";
        echo "<button type='submit' style='padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>Ajouter les colonnes manquantes</button>";
        echo "</form>";
    } else {
        echo "<p style='color:green'>Toutes les colonnes requises sont présentes dans la table 'affectations'.</p>";
    }
    
    // Traiter les actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'add_columns') {
            try {
                // Ajouter les colonnes manquantes
                foreach ($missingColumns as $column => $definition) {
                    $pdo->exec("ALTER TABLE affectations ADD COLUMN $column $definition");
                    echo "<p style='color:green'>La colonne '$column' a été ajoutée à la table 'affectations' avec succès.</p>";
                }
                echo "<p>Veuillez rafraîchir la page pour voir les changements.</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erreur lors de l'ajout des colonnes: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Ajouter un lien vers affectation_ue.php
    echo "<p><a href='affectation_ue.php' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Retour à la page des affectations</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de base de données: " . $e->getMessage() . "</p>";
}
?>
