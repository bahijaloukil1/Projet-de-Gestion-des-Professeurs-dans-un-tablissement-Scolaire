<?php
// Script de débogage pour vérifier les problèmes avec gerer_emplois_temps.php

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Informations de connexion à la base de données
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'gestion-coordinteur';

// Vérifier si le fichier existe
echo "<h2>Vérification du fichier</h2>";
$file_path = 'gerer_emplois_temps.php';
if (file_exists($file_path)) {
    echo "<p style='color:green'>Le fichier $file_path existe.</p>";
    echo "<p>Taille du fichier: " . filesize($file_path) . " octets</p>";
    echo "<p>Dernière modification: " . date("Y-m-d H:i:s", filemtime($file_path)) . "</p>";
} else {
    echo "<p style='color:red'>Le fichier $file_path n'existe pas!</p>";
}

// Vérifier la connexion à la base de données
echo "<h2>Vérification de la connexion à la base de données</h2>";
try {
    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        throw new Exception("Erreur de connexion: " . $conn->connect_error);
    }
    echo "<p style='color:green'>Connexion à la base de données réussie.</p>";
    
    // Vérifier les tables nécessaires
    echo "<h2>Vérification des tables nécessaires</h2>";
    $tables_needed = ['emplois_temps', 'groupes', 'salles', 'unites_enseignements'];
    
    foreach ($tables_needed as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows > 0) {
            echo "<p style='color:green'>La table '$table' existe.</p>";
            
            // Afficher la structure de la table
            $structure = $conn->query("DESCRIBE $table");
            if ($structure) {
                echo "<details><summary>Structure de la table '$table'</summary><table border='1'>";
                echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
                while ($row = $structure->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['Field'] . "</td>";
                    echo "<td>" . $row['Type'] . "</td>";
                    echo "<td>" . $row['Null'] . "</td>";
                    echo "<td>" . $row['Key'] . "</td>";
                    echo "<td>" . $row['Default'] . "</td>";
                    echo "<td>" . $row['Extra'] . "</td>";
                    echo "</tr>";
                }
                echo "</table></details>";
            }
        } else {
            echo "<p style='color:red'>La table '$table' n'existe pas!</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
}

// Vérifier les liens dans le fichier dashboard_coordinateur.php
echo "<h2>Vérification des liens dans dashboard_coordinateur.php</h2>";
$dashboard_file = 'dashboard_coordinateur.php';
if (file_exists($dashboard_file)) {
    $content = file_get_contents($dashboard_file);
    if (strpos($content, 'gerer_emplois_temps.php') !== false) {
        echo "<p style='color:green'>Le lien vers gerer_emplois_temps.php existe dans le fichier dashboard_coordinateur.php.</p>";
        
        // Extraire le lien complet
        preg_match('/<a href="gerer_emplois_temps.php[^>]*>/', $content, $matches);
        if (!empty($matches)) {
            echo "<p>Lien trouvé: " . htmlspecialchars($matches[0]) . "</p>";
        }
    } else {
        echo "<p style='color:red'>Le lien vers gerer_emplois_temps.php n'existe pas dans le fichier dashboard_coordinateur.php!</p>";
    }
} else {
    echo "<p style='color:red'>Le fichier dashboard_coordinateur.php n'existe pas!</p>";
}

// Afficher un lien pour tester directement
echo "<h2>Test direct</h2>";
echo "<p>Cliquez sur le lien ci-dessous pour tester directement la page:</p>";
echo "<a href='gerer_emplois_temps.php' target='_blank'>Ouvrir gerer_emplois_temps.php</a>";

// Vérifier les permissions du fichier
echo "<h2>Vérification des permissions</h2>";
if (file_exists($file_path)) {
    $perms = fileperms($file_path);
    $perms_octal = substr(sprintf('%o', $perms), -4);
    echo "<p>Permissions du fichier: $perms_octal</p>";
    
    if (is_readable($file_path)) {
        echo "<p style='color:green'>Le fichier est lisible.</p>";
    } else {
        echo "<p style='color:red'>Le fichier n'est pas lisible!</p>";
    }
    
    if (is_executable($file_path)) {
        echo "<p style='color:green'>Le fichier est exécutable.</p>";
    } else {
        echo "<p style='color:red'>Le fichier n'est pas exécutable!</p>";
    }
}

// Afficher les 20 premières lignes du fichier
echo "<h2>Aperçu du contenu du fichier</h2>";
if (file_exists($file_path)) {
    $lines = file($file_path, FILE_IGNORE_NEW_LINES);
    echo "<pre>";
    for ($i = 0; $i < min(20, count($lines)); $i++) {
        echo htmlspecialchars($lines[$i]) . "\n";
    }
    echo "...</pre>";
}
?>
