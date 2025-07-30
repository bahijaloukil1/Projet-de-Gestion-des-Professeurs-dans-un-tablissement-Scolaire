<?php
// Script pour remplacer toutes les occurrences de "unites_enseignement" par "unites_enseignements"
// dans tous les fichiers PHP du répertoire courant

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Remplacement de 'unites_enseignement' par 'unites_enseignements'</h1>";

// Récupérer tous les fichiers PHP du répertoire courant
$phpFiles = glob("*.php");

// Rechercher et remplacer
$searchTerm = "unites_enseignement";
$replaceTerm = "unites_enseignements";
$results = [];

foreach ($phpFiles as $file) {
    // Exclure le script courant pour éviter les problèmes
    if ($file === basename(__FILE__)) {
        continue;
    }
    
    // Lire le contenu du fichier
    $content = file_get_contents($file);
    
    // Vérifier si le terme recherché existe dans le fichier
    if (stripos($content, $searchTerm) !== false) {
        // Compter les occurrences avant le remplacement
        $count = substr_count(strtolower($content), strtolower($searchTerm));
        
        // Effectuer le remplacement
        $newContent = str_ireplace($searchTerm, $replaceTerm, $content);
        
        // Sauvegarder le fichier modifié
        file_put_contents($file, $newContent);
        
        // Enregistrer le résultat
        $results[$file] = $count;
    }
}

// Afficher les résultats
if (empty($results)) {
    echo "<p style='color:green'>Aucune occurrence de '$searchTerm' trouvée dans les fichiers PHP.</p>";
} else {
    echo "<h2>Remplacements effectués :</h2>";
    echo "<ul>";
    foreach ($results as $file => $count) {
        echo "<li><strong>$file</strong> : $count occurrence(s) remplacée(s)</li>";
    }
    echo "</ul>";
    echo "<p style='color:green'>Tous les remplacements ont été effectués avec succès.</p>";
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
        echo "<p>Vous devriez renommer cette table en 'unites_enseignements' ou créer une nouvelle table 'unites_enseignements' et y copier les données.</p>";
        
        // Proposer de renommer la table
        echo "<form method='post'>";
        echo "<input type='submit' name='rename_table' value='Renommer la table unites_enseignement en unites_enseignements'>";
        echo "</form>";
        
        if (isset($_POST['rename_table'])) {
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
                }
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erreur lors du renommage de la table: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p style='color:green'>La table 'unites_enseignement' n'existe pas dans la base de données.</p>";
    }
    
    // Vérifier si unites_enseignements existe
    if (in_array('unites_enseignements', $tables)) {
        echo "<p style='color:green'>La table 'unites_enseignements' existe dans la base de données.</p>";
    } else {
        echo "<p style='color:red'>La table 'unites_enseignements' n'existe pas dans la base de données.</p>";
        echo "<p>Vous devriez créer cette table.</p>";
        
        // Proposer de créer la table
        echo "<form method='post'>";
        echo "<input type='submit' name='create_table' value='Créer la table unites_enseignements'>";
        echo "</form>";
        
        if (isset($_POST['create_table'])) {
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
            } catch (PDOException $e) {
                echo "<p style='color:red'>Erreur lors de la création de la table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}
?>
