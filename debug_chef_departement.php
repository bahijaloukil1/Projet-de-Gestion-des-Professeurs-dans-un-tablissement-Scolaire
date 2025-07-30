<?php
// Inclure le fichier de configuration
require_once 'config.php';

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Débogage de la gestion des chefs de département</h1>";

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
    
    // Vérifier si la table utilisateurs existe
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h2>Tables disponibles dans la base de données</h2>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    if (in_array('utilisateurs', $tables)) {
        echo "<p>La table utilisateurs existe.</p>";
        
        // Afficher la structure de la table utilisateurs
        $columns = $pdo->query("SHOW COLUMNS FROM utilisateurs")->fetchAll();
        
        echo "<h2>Structure de la table utilisateurs</h2>";
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
        
        // Vérifier si la colonne type_utilisateur existe
        $hasTypeColumn = false;
        foreach ($columns as $column) {
            if ($column['Field'] === 'type_utilisateur') {
                $hasTypeColumn = true;
                break;
            }
        }
        
        if ($hasTypeColumn) {
            echo "<p>La colonne type_utilisateur existe dans la table utilisateurs.</p>";
            
            // Afficher les valeurs distinctes de type_utilisateur
            $types = $pdo->query("SELECT DISTINCT type_utilisateur FROM utilisateurs")->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<h2>Types d'utilisateurs existants</h2>";
            echo "<ul>";
            foreach ($types as $type) {
                echo "<li>" . ($type ? $type : "NULL") . "</li>";
            }
            echo "</ul>";
            
            // Compter les utilisateurs par type
            echo "<h2>Nombre d'utilisateurs par type</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Type</th><th>Nombre</th></tr>";
            
            $stmt = $pdo->query("SELECT type_utilisateur, COUNT(*) as count FROM utilisateurs GROUP BY type_utilisateur");
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . ($row['type_utilisateur'] ? $row['type_utilisateur'] : "NULL") . "</td>";
                echo "<td>" . $row['count'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            
            // Afficher les chefs de département
            $chefs = $pdo->query("SELECT * FROM utilisateurs WHERE type_utilisateur = 'chef_departement'")->fetchAll();
            
            echo "<h2>Chefs de département existants</h2>";
            
            if (count($chefs) > 0) {
                echo "<table border='1'>";
                echo "<tr>";
                foreach (array_keys($chefs[0]) as $header) {
                    echo "<th>$header</th>";
                }
                echo "</tr>";
                
                foreach ($chefs as $chef) {
                    echo "<tr>";
                    foreach ($chef as $value) {
                        echo "<td>" . ($value !== null ? htmlspecialchars($value) : "NULL") . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p>Aucun chef de département trouvé.</p>";
            }
            
            // Formulaire pour ajouter un chef de département directement
            echo "<h2>Ajouter un chef de département directement</h2>";
            
            if (isset($_POST['submit'])) {
                try {
                    $nom = trim($_POST['nom']);
                    $prenom = trim($_POST['prenom']);
                    $email = trim($_POST['email']);
                    $unite_id = $_POST['unite_id'];
                    $mot_de_passe = $_POST['mot_de_passe'];
                    
                    // Validation
                    if (empty($nom) || empty($prenom) || empty($email) || empty($unite_id) || empty($mot_de_passe)) {
                        throw new Exception('Tous les champs sont obligatoires');
                    }
                    
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Email invalide');
                    }
                    
                    if (strlen($mot_de_passe) < 8) {
                        throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
                    }
                    
                    // Vérifier si l'email existe déjà
                    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
                    $stmt->execute([$email]);
                    
                    if ($stmt->rowCount() > 0) {
                        throw new Exception('Cet email est déjà utilisé');
                    }
                    
                    // Hachage du mot de passe
                    $password_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                    
                    // Insertion directe sans transaction
                    $query = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, type_utilisateur, id_departement) 
                             VALUES (?, ?, ?, ?, 'chef_departement', ?)";
                    
                    echo "<p>Requête SQL: " . $query . "</p>";
                    echo "<p>Paramètres: nom=" . $nom . ", prenom=" . $prenom . ", email=" . $email . ", unite_id=" . $unite_id . "</p>";
                    
                    $stmt = $pdo->prepare($query);
                    $result = $stmt->execute([$nom, $prenom, $email, $password_hash, $unite_id]);
                    
                    if ($result) {
                        $lastId = $pdo->lastInsertId();
                        echo "<p class='success'>Chef de département ajouté avec succès (ID: " . $lastId . ")</p>";
                        
                        // Vérifier que l'utilisateur a bien été inséré avec le bon type
                        $verif = $pdo->prepare("SELECT id, type_utilisateur FROM utilisateurs WHERE id = ?");
                        $verif->execute([$lastId]);
                        $result = $verif->fetch();
                        
                        if ($result && $result['type_utilisateur'] === 'chef_departement') {
                            echo "<p class='success'>Vérification réussie: l'utilisateur a bien le type 'chef_departement'</p>";
                        } else {
                            echo "<p class='error'>Erreur: L'utilisateur a été créé mais pas comme chef de département</p>";
                            echo "<p>Type actuel: " . ($result ? $result['type_utilisateur'] : 'inconnu') . "</p>";
                        }
                        
                        // Rafraîchir la page pour voir les changements
                        echo "<script>window.location.reload();</script>";
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        echo "<p class='error'>Erreur lors de l'insertion: " . print_r($errorInfo, true) . "</p>";
                    }
                } catch (Exception $e) {
                    echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
                }
            }
            
            echo "<form method='post'>";
            echo "<div>";
            echo "<label for='nom'>Nom:</label>";
            echo "<input type='text' id='nom' name='nom' required>";
            echo "</div>";
            
            echo "<div>";
            echo "<label for='prenom'>Prénom:</label>";
            echo "<input type='text' id='prenom' name='prenom' required>";
            echo "</div>";
            
            echo "<div>";
            echo "<label for='email'>Email:</label>";
            echo "<input type='email' id='email' name='email' required>";
            echo "</div>";
            
            echo "<div>";
            echo "<label for='unite_id'>Département:</label>";
            echo "<select id='unite_id' name='unite_id' required>";
            echo "<option value=''>Sélectionnez un département</option>";
            
            // Ajouter les départements
            $departements = [
                ['id' => 1, 'nom' => 'Informatique/Mathématiques'],
                ['id' => 2, 'nom' => 'Physique'],
                ['id' => 3, 'nom' => 'Chimie'],
                ['id' => 4, 'nom' => 'Biologie']
            ];
            
            foreach ($departements as $dept) {
                echo "<option value='" . $dept['id'] . "'>" . $dept['nom'] . "</option>";
            }
            
            echo "</select>";
            echo "</div>";
            
            echo "<div>";
            echo "<label for='mot_de_passe'>Mot de passe:</label>";
            echo "<input type='password' id='mot_de_passe' name='mot_de_passe' required minlength='8'>";
            echo "<small>Le mot de passe doit contenir au moins 8 caractères</small>";
            echo "</div>";
            
            echo "<div>";
            echo "<input type='submit' name='submit' value='Ajouter'>";
            echo "</div>";
            echo "</form>";
            
            // Vérifier les logs PHP
            echo "<h2>Logs PHP</h2>";
            
            $logFile = ini_get('error_log');
            if (file_exists($logFile) && is_readable($logFile)) {
                echo "<p>Fichier de log: " . $logFile . "</p>";
                
                // Afficher les 20 dernières lignes du fichier de log
                $logs = file($logFile);
                $logs = array_slice($logs, -20);
                
                echo "<pre>";
                foreach ($logs as $log) {
                    echo htmlspecialchars($log);
                }
                echo "</pre>";
            } else {
                echo "<p>Impossible de lire le fichier de log PHP.</p>";
            }
        } else {
            echo "<p>La colonne type_utilisateur n'existe pas dans la table utilisateurs.</p>";
            
            // Essayer d'ajouter la colonne
            echo "<p>Tentative d'ajout de la colonne type_utilisateur...</p>";
            
            try {
                $pdo->exec("ALTER TABLE utilisateurs ADD COLUMN type_utilisateur VARCHAR(50) DEFAULT 'utilisateur' AFTER mot_de_passe");
                echo "<p>Colonne type_utilisateur ajoutée avec succès.</p>";
                echo "<p>Veuillez rafraîchir la page pour voir les changements.</p>";
            } catch (PDOException $e) {
                echo "<p>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        echo "<p>La table utilisateurs n'existe pas.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    
    h1, h2 {
        color: #333;
    }
    
    pre {
        background-color: #f5f5f5;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: auto;
    }
    
    table {
        border-collapse: collapse;
        width: 100%;
        margin: 20px 0;
    }
    
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    
    th {
        background-color: #f2f2f2;
    }
    
    form {
        margin: 20px 0;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
    }
    
    form div {
        margin-bottom: 15px;
    }
    
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    
    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    input[type="submit"] {
        background-color: #4CAF50;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    input[type="submit"]:hover {
        background-color: #45a049;
    }
    
    small {
        display: block;
        color: #666;
        margin-top: 5px;
    }
    
    .success {
        color: green;
        font-weight: bold;
    }
    
    .error {
        color: red;
        font-weight: bold;
    }
</style>
