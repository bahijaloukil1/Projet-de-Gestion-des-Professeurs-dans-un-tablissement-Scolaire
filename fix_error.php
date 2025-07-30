<?php
// Script pour identifier et corriger l'erreur "Table 'gestion_coordinteur.unites_enseignement' doesn't exist"

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correction de l'erreur 'Table unites_enseignement doesn't exist'</h1>";

// Fonction pour rechercher une chaîne dans un fichier et afficher le contexte
function searchInFileWithContext($file, $search, $contextLines = 2) {
    $content = file_get_contents($file);
    $lines = explode("\n", $content);
    $results = [];
    
    foreach ($lines as $lineNumber => $line) {
        if (stripos($line, $search) !== false) {
            $start = max(0, $lineNumber - $contextLines);
            $end = min(count($lines) - 1, $lineNumber + $contextLines);
            
            $context = [];
            for ($i = $start; $i <= $end; $i++) {
                $context[] = [
                    'line' => $i + 1,
                    'content' => $lines[$i],
                    'highlight' => ($i === $lineNumber)
                ];
            }
            
            $results[] = [
                'line' => $lineNumber + 1,
                'content' => $line,
                'context' => $context
            ];
        }
    }
    
    return $results;
}

// Fonction pour remplacer une chaîne dans un fichier
function replaceInFile($file, $search, $replace) {
    $content = file_get_contents($file);
    $newContent = str_ireplace($search, $replace, $content);
    file_put_contents($file, $newContent);
    
    return substr_count(strtolower($content), strtolower($search));
}

// Récupérer tous les fichiers PHP du répertoire courant
$phpFiles = glob("*.php");

// Rechercher les références à unites_enseignement (singulier)
$searchTerm = "unites_enseignement";
$replaceTerm = "unites_enseignements";
$results = [];

foreach ($phpFiles as $file) {
    // Exclure le script courant pour éviter les problèmes
    if ($file === basename(__FILE__)) {
        continue;
    }
    
    // Rechercher le terme dans le fichier avec contexte
    $fileResults = searchInFileWithContext($file, $searchTerm);
    
    if (!empty($fileResults)) {
        $results[$file] = $fileResults;
    }
}

// Afficher les résultats
if (empty($results)) {
    echo "<p style='color:green'>Aucune référence à '$searchTerm' trouvée dans les fichiers PHP.</p>";
} else {
    echo "<h2>Références à '$searchTerm' trouvées dans les fichiers suivants :</h2>";
    
    echo "<form method='post'>";
    echo "<input type='hidden' name='action' value='replace_all'>";
    
    foreach ($results as $file => $fileResults) {
        echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;'>";
        echo "<h3>$file (" . count($fileResults) . " occurrence(s))</h3>";
        
        foreach ($fileResults as $index => $result) {
            echo "<div style='margin-bottom: 10px;'>";
            echo "<p>Ligne " . $result['line'] . ": <code>" . htmlspecialchars($result['content']) . "</code></p>";
            
            echo "<div style='background-color: #f5f5f5; padding: 10px; border-left: 3px solid #007bff;'>";
            echo "<pre>";
            foreach ($result['context'] as $ctx) {
                if ($ctx['highlight']) {
                    echo "<strong style='background-color: #ffff00;'>" . $ctx['line'] . ": " . htmlspecialchars($ctx['content']) . "</strong>\n";
                } else {
                    echo $ctx['line'] . ": " . htmlspecialchars($ctx['content']) . "\n";
                }
            }
            echo "</pre>";
            echo "</div>";
            
            echo "</div>";
        }
        
        echo "<div>";
        echo "<input type='checkbox' name='files[]' value='$file' id='file_$file' checked>";
        echo "<label for='file_$file'> Corriger ce fichier</label>";
        echo "</div>";
        
        echo "</div>";
    }
    
    echo "<button type='submit' style='padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>Remplacer toutes les occurrences sélectionnées</button>";
    echo "</form>";
}

// Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'replace_all') {
    if (isset($_POST['files']) && is_array($_POST['files'])) {
        echo "<h2>Résultats du remplacement :</h2>";
        echo "<ul>";
        
        foreach ($_POST['files'] as $file) {
            $count = replaceInFile($file, $searchTerm, $replaceTerm);
            echo "<li><strong>$file</strong> : $count occurrence(s) remplacée(s)</li>";
        }
        
        echo "</ul>";
        echo "<p style='color:green'>Les remplacements ont été effectués avec succès.</p>";
        echo "<p>Veuillez rafraîchir la page pour voir les changements.</p>";
    } else {
        echo "<p style='color:orange'>Aucun fichier sélectionné pour le remplacement.</p>";
    }
}

// Vérifier également les tables dans la base de données
echo "<h2>Vérification des tables dans la base de données</h2>";

// Inclure le fichier de configuration
require_once 'config.php';

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
    
    // Récupérer la liste des tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Tables trouvées dans la base de données '" . DB_NAME . "' :</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Vérifier si unites_enseignement existe
    if (in_array('unites_enseignement', $tables)) {
        echo "<p style='color:orange'>La table 'unites_enseignement' existe dans la base de données.</p>";
        
        // Proposer de renommer la table
        echo "<form method='post'>";
        echo "<input type='hidden' name='action' value='rename_table'>";
        echo "<button type='submit' style='padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>Renommer la table unites_enseignement en unites_enseignements</button>";
        echo "</form>";
    } else {
        echo "<p style='color:green'>La table 'unites_enseignement' n'existe pas dans la base de données.</p>";
    }
    
    // Vérifier si unites_enseignements existe
    if (in_array('unites_enseignements', $tables)) {
        echo "<p style='color:green'>La table 'unites_enseignements' existe dans la base de données.</p>";
    } else {
        echo "<p style='color:red'>La table 'unites_enseignements' n'existe pas dans la base de données.</p>";
        
        // Proposer de créer la table
        echo "<form method='post'>";
        echo "<input type='hidden' name='action' value='create_table'>";
        echo "<button type='submit' style='padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;'>Créer la table unites_enseignements</button>";
        echo "</form>";
    }
    
    // Traiter les actions de base de données
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'rename_table') {
            try {
                // Vérifier si la table unites_enseignements existe déjà
                $stmt = $pdo->query("SHOW TABLES LIKE 'unites_enseignements'");
                $tableExists = $stmt->rowCount() > 0;
                
                if ($tableExists) {
                    echo "<p style='color:red'>La table 'unites_enseignements' existe déjà. Impossible de renommer 'unites_enseignement'.</p>";
                } else {
                    // Renommer la table
                    $pdo->exec("RENAME TABLE unites_enseignement TO unites_enseignements");
                    echo "<p style='color:green'>La table 'unites_enseignement' a été renommée en 'unites_enseignements' avec succès.</p>";
                    echo "<p>Veuillez rafraîchir la page pour voir les changements.</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erreur lors du renommage de la table: " . $e->getMessage() . "</p>";
            }
        } elseif ($_POST['action'] === 'create_table') {
            try {
                // Créer la table unites_enseignements
                $pdo->exec("
                    CREATE TABLE unites_enseignements (
                        id_ue INT AUTO_INCREMENT PRIMARY KEY,
                        code_ue VARCHAR(20) NOT NULL,
                        intitule VARCHAR(255) NOT NULL,
                        filiere VARCHAR(100),
                        niveau VARCHAR(50),
                        credit INT DEFAULT 3,
                        volume_horaire INT DEFAULT 30,
                        departement_id INT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )
                ");
                
                echo "<p style='color:green'>La table 'unites_enseignements' a été créée avec succès.</p>";
                
                // Insérer quelques données de test
                $pdo->exec("
                    INSERT INTO unites_enseignements (code_ue, intitule, filiere, niveau, credit, volume_horaire, departement_id)
                    VALUES
                    ('INF101', 'Introduction à l\'informatique', 'Informatique', 'L1', 3, 30, 1),
                    ('INF102', 'Algorithmique', 'Informatique', 'L1', 4, 40, 1),
                    ('INF201', 'Programmation orientée objet', 'Informatique', 'L2', 5, 50, 1),
                    ('MAT101', 'Analyse mathématique', 'Mathématiques', 'L1', 3, 30, 1),
                    ('MAT102', 'Algèbre linéaire', 'Mathématiques', 'L1', 4, 40, 1)
                ");
                
                echo "<p style='color:green'>Des données de test ont été insérées dans la table 'unites_enseignements'.</p>";
                echo "<p>Veuillez rafraîchir la page pour voir les changements.</p>";
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erreur lors de la création de la table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}
?>
