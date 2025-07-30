<?php
// Script pour vérifier et corriger la structure de la table affectations
require_once 'config.php';

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correction de la table affectations</h1>";

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
        
        // Vérifier si la colonne ue_id existe
        $hasUeIdColumn = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'ue_id') {
                $hasUeIdColumn = true;
                break;
            }
        }
        
        if ($hasUeIdColumn) {
            echo "<p style='color:green'>La colonne 'ue_id' existe dans la table 'affectations'.</p>";
        } else {
            echo "<p style='color:red'>La colonne 'ue_id' n'existe pas dans la table 'affectations'.</p>";
            
            // Proposer d'ajouter la colonne
            echo "<form method='post'>";
            echo "<input type='hidden' name='action' value='add_column'>";
            echo "<button type='submit' style='padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>Ajouter la colonne 'ue_id' à la table 'affectations'</button>";
            echo "</form>";
        }
        
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
        
        // Proposer de créer la table
        echo "<form method='post'>";
        echo "<input type='hidden' name='action' value='create_table'>";
        echo "<button type='submit' style='padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>Créer la table 'affectations'</button>";
        echo "</form>";
    }
    
    // Traiter les actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'add_column') {
            try {
                // Ajouter la colonne ue_id
                $pdo->exec("ALTER TABLE affectations ADD COLUMN ue_id INT NOT NULL AFTER professeur_id");
                echo "<p style='color:green'>La colonne 'ue_id' a été ajoutée à la table 'affectations' avec succès.</p>";
                echo "<p>Veuillez rafraîchir la page pour voir les changements.</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
            }
        } elseif ($_POST['action'] === 'create_table') {
            try {
                // Créer la table affectations
                $pdo->exec("
                    CREATE TABLE affectations (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        professeur_id INT NOT NULL,
                        ue_id INT NOT NULL,
                        annee INT DEFAULT " . date('Y') . ",
                        semestre INT DEFAULT 1,
                        date_debut DATE NOT NULL,
                        date_fin DATE,
                        heures INT NOT NULL DEFAULT 30,
                        utilisateur_id INT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
                echo "<p style='color:green'>La table 'affectations' a été créée avec succès.</p>";
                echo "<p>Veuillez rafraîchir la page pour voir les changements.</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erreur lors de la création de la table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Ajouter un lien vers charge_horaire.php
    echo "<p><a href='charge_horaire.php' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Retour à la page des charges horaires</a></p>";
    
    // Ajouter un lien vers affectation_ue.php
    echo "<p><a href='affectation_ue.php' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Retour à la page des affectations</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}
?>
