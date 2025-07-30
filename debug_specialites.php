<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier de configuration
require_once 'config.php';

echo "<h1>Débogage du chargement des spécialités</h1>";

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
    
    // Vérifier les tables disponibles
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tables disponibles dans la base de données</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Vérifier la structure de la table specialite
    if (in_array('specialite', $tables)) {
        echo "<h2>Structure de la table specialite</h2>";
        $columns = $pdo->query("DESCRIBE specialite")->fetchAll();
        
        echo "<table border='1'>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
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
        
        // Afficher les données de la table specialite
        echo "<h2>Données de la table specialite</h2>";
        $specialites = $pdo->query("SELECT * FROM specialite")->fetchAll();
        
        if (count($specialites) > 0) {
            echo "<table border='1'>";
            
            // En-têtes de colonnes
            echo "<tr>";
            foreach (array_keys($specialites[0]) as $header) {
                echo "<th>$header</th>";
            }
            echo "</tr>";
            
            // Données
            foreach ($specialites as $specialite) {
                echo "<tr>";
                foreach ($specialite as $value) {
                    echo "<td>" . ($value !== null ? htmlspecialchars($value) : "NULL") . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Aucune donnée dans la table specialite</p>";
        }
    } else {
        echo "<p>La table specialite n'existe pas.</p>";
    }
    
    // Vérifier la structure de la table departement
    if (in_array('departement', $tables)) {
        echo "<h2>Structure de la table departement</h2>";
        $columns = $pdo->query("DESCRIBE departement")->fetchAll();
        
        echo "<table border='1'>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
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
        
        // Afficher les données de la table departement
        echo "<h2>Données de la table departement</h2>";
        $departements = $pdo->query("SELECT * FROM departement")->fetchAll();
        
        if (count($departements) > 0) {
            echo "<table border='1'>";
            
            // En-têtes de colonnes
            echo "<tr>";
            foreach (array_keys($departements[0]) as $header) {
                echo "<th>$header</th>";
            }
            echo "</tr>";
            
            // Données
            foreach ($departements as $departement) {
                echo "<tr>";
                foreach ($departement as $value) {
                    echo "<td>" . ($value !== null ? htmlspecialchars($value) : "NULL") . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Aucune donnée dans la table departement</p>";
        }
    } else {
        echo "<p>La table departement n'existe pas.</p>";
    }
    
    // Tester la requête pour récupérer les spécialités par département
    echo "<h2>Test de récupération des spécialités par département</h2>";
    
    if (in_array('specialite', $tables) && in_array('departement', $tables)) {
        $departements = $pdo->query("SELECT id_departement, nom_departement FROM departement")->fetchAll();
        
        foreach ($departements as $departement) {
            $dept_id = $departement['id_departement'];
            $dept_nom = $departement['nom_departement'];
            
            echo "<h3>Spécialités pour le département: $dept_nom (ID: $dept_id)</h3>";
            
            $specialites = $pdo->query("SELECT * FROM specialite WHERE id_departement = $dept_id")->fetchAll();
            
            if (count($specialites) > 0) {
                echo "<table border='1'>";
                
                // En-têtes de colonnes
                echo "<tr>";
                foreach (array_keys($specialites[0]) as $header) {
                    echo "<th>$header</th>";
                }
                echo "</tr>";
                
                // Données
                foreach ($specialites as $specialite) {
                    echo "<tr>";
                    foreach ($specialite as $value) {
                        echo "<td>" . ($value !== null ? htmlspecialchars($value) : "NULL") . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>Aucune spécialité trouvée pour ce département</p>";
            }
        }
    } else {
        echo "<p>Les tables nécessaires n'existent pas pour tester la récupération des spécialités.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}
?>
