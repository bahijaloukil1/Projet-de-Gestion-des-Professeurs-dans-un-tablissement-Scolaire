<?php
require_once 'config.php';

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correction de la structure de la table utilisateurs</h1>";

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
    
    echo "<p>Connexion réussie à la base de données</p>";
    
    // Vérifier la structure actuelle de la table utilisateurs
    echo "<h2>Structure actuelle de la table utilisateurs</h2>";
    $columns = $pdo->query("SHOW COLUMNS FROM utilisateurs")->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    
    $idColumnExists = false;
    $idColumnIsAutoIncrement = false;
    $idColumnIsPrimary = false;
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
        
        if ($column['Field'] === 'id') {
            $idColumnExists = true;
            if ($column['Extra'] === 'auto_increment') {
                $idColumnIsAutoIncrement = true;
            }
            if ($column['Key'] === 'PRI') {
                $idColumnIsPrimary = true;
            }
        }
    }
    
    echo "</table>";
    
    // Vérifier si des corrections sont nécessaires
    $needsCorrection = false;
    
    if (!$idColumnExists) {
        echo "<p style='color: red;'>PROBLÈME: La colonne 'id' n'existe pas dans la table utilisateurs.</p>";
        $needsCorrection = true;
    } else {
        if (!$idColumnIsAutoIncrement) {
            echo "<p style='color: red;'>PROBLÈME: La colonne 'id' n'est pas configurée avec AUTO_INCREMENT.</p>";
            $needsCorrection = true;
        }
        if (!$idColumnIsPrimary) {
            echo "<p style='color: red;'>PROBLÈME: La colonne 'id' n'est pas configurée comme clé primaire.</p>";
            $needsCorrection = true;
        }
    }
    
    // Afficher les 10 premiers utilisateurs avant correction
    echo "<h2>10 premiers utilisateurs avant correction</h2>";
    $users = $pdo->query("SELECT * FROM utilisateurs LIMIT 10")->fetchAll();
    
    if (count($users) > 0) {
        echo "<table border='1'>";
        
        // En-têtes de colonnes
        echo "<tr>";
        foreach (array_keys($users[0]) as $header) {
            echo "<th>$header</th>";
        }
        echo "</tr>";
        
        // Données
        foreach ($users as $user) {
            echo "<tr>";
            foreach ($user as $key => $value) {
                echo "<td>" . ($value !== null ? htmlspecialchars($value) : "NULL") . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>Aucun utilisateur trouvé</p>";
    }
    
    // Effectuer les corrections si nécessaire
    if ($needsCorrection) {
        echo "<h2>Application des corrections</h2>";
        
        // Créer une sauvegarde de la table
        echo "<p>Création d'une sauvegarde de la table utilisateurs...</p>";
        $pdo->exec("CREATE TABLE IF NOT EXISTS utilisateurs_backup LIKE utilisateurs");
        $pdo->exec("INSERT INTO utilisateurs_backup SELECT * FROM utilisateurs");
        echo "<p style='color: green;'>Sauvegarde créée avec succès: utilisateurs_backup</p>";
        
        // Commencer une transaction
        $pdo->beginTransaction();
        
        try {
            if (!$idColumnExists) {
                // Ajouter la colonne ID
                echo "<p>Ajout de la colonne 'id' comme clé primaire auto-incrémentée...</p>";
                $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
            } else {
                // Modifier la colonne ID existante
                if (!$idColumnIsPrimary || !$idColumnIsAutoIncrement) {
                    echo "<p>Modification de la colonne 'id' pour la rendre clé primaire auto-incrémentée...</p>";
                    
                    // Supprimer la clé primaire existante si nécessaire
                    $primaryKeys = $pdo->query("SHOW KEYS FROM utilisateurs WHERE Key_name = 'PRIMARY'")->fetchAll();
                    if (count($primaryKeys) > 0) {
                        $pdo->exec("ALTER TABLE utilisateurs DROP PRIMARY KEY");
                    }
                    
                    // Modifier la colonne ID
                    $pdo->exec("ALTER TABLE utilisateurs MODIFY id INT AUTO_INCREMENT PRIMARY KEY");
                }
            }
            
            // Vérifier si les IDs sont tous à 0 et les corriger si nécessaire
            $zeroIds = $pdo->query("SELECT COUNT(*) as count FROM utilisateurs WHERE id = 0")->fetch();
            if ($zeroIds['count'] > 0) {
                echo "<p>Correction des IDs à 0...</p>";
                
                // Récupérer tous les utilisateurs avec ID = 0
                $usersWithZeroId = $pdo->query("SELECT * FROM utilisateurs WHERE id = 0")->fetchAll();
                
                // Trouver le prochain ID disponible
                $maxId = $pdo->query("SELECT MAX(id) as max_id FROM utilisateurs WHERE id > 0")->fetch();
                $nextId = ($maxId['max_id'] ?? 0) + 1;
                
                // Mettre à jour chaque utilisateur avec un nouvel ID
                foreach ($usersWithZeroId as $user) {
                    $stmt = $pdo->prepare("UPDATE utilisateurs SET id = ? WHERE id = 0 AND email = ? LIMIT 1");
                    $stmt->execute([$nextId, $user['email']]);
                    echo "<p>Utilisateur {$user['nom']} {$user['prenom']} (email: {$user['email']}) : ID mis à jour de 0 à $nextId</p>";
                    $nextId++;
                }
            }
            
            // Valider les modifications
            $pdo->commit();
            echo "<p style='color: green;'>Corrections appliquées avec succès!</p>";
            
        } catch (Exception $e) {
            // Annuler les modifications en cas d'erreur
            $pdo->rollBack();
            echo "<p style='color: red;'>Erreur lors de l'application des corrections: " . $e->getMessage() . "</p>";
        }
        
        // Afficher la nouvelle structure de la table
        echo "<h2>Nouvelle structure de la table utilisateurs</h2>";
        $newColumns = $pdo->query("SHOW COLUMNS FROM utilisateurs")->fetchAll();
        
        echo "<table border='1'>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        
        foreach ($newColumns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Afficher les 10 premiers utilisateurs après correction
        echo "<h2>10 premiers utilisateurs après correction</h2>";
        $updatedUsers = $pdo->query("SELECT * FROM utilisateurs LIMIT 10")->fetchAll();
        
        if (count($updatedUsers) > 0) {
            echo "<table border='1'>";
            
            // En-têtes de colonnes
            echo "<tr>";
            foreach (array_keys($updatedUsers[0]) as $header) {
                echo "<th>$header</th>";
            }
            echo "</tr>";
            
            // Données
            foreach ($updatedUsers as $user) {
                echo "<tr>";
                foreach ($user as $key => $value) {
                    echo "<td>" . ($value !== null ? htmlspecialchars($value) : "NULL") . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Aucun utilisateur trouvé</p>";
        }
    } else {
        echo "<p style='color: green;'>La structure de la table utilisateurs est correcte. Aucune correction nécessaire.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}
?>
