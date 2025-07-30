<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_coordinteur");

if ($conn->connect_error) {
    die("Connexion échouée : " . $conn->connect_error);
}

// Vérifier si la table unites_enseignements existe
echo "<h2>Table unites_enseignements</h2>";
$result = $conn->query("SHOW TABLES LIKE 'unites_enseignements'");
if ($result->num_rows > 0) {
    echo "<p>La table unites_enseignements existe.</p>";
    
    // Vérifier si la colonne id_ue est une clé primaire
    $result = $conn->query("SHOW KEYS FROM unites_enseignements WHERE Key_name = 'PRIMARY'");
    if ($result->num_rows > 0) {
        echo "<p>La colonne id_ue est déjà une clé primaire.</p>";
    } else {
        echo "<p>La colonne id_ue n'est pas une clé primaire.</p>";
    }
    
    // Afficher la structure de la table
    echo "<h3>Structure de la table unites_enseignements</h3>";
    $result = $conn->query("DESCRIBE unites_enseignements");
    echo "<table border='1'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>La table unites_enseignements n'existe pas.</p>";
}

// Vérifier si la table departement existe
echo "<h2>Table departement</h2>";
$result = $conn->query("SHOW TABLES LIKE 'departement'");
if ($result->num_rows > 0) {
    echo "<p>La table departement existe.</p>";
    
    // Vérifier si la colonne id_departement est une clé primaire
    $result = $conn->query("SHOW KEYS FROM departement WHERE Key_name = 'PRIMARY'");
    if ($result->num_rows > 0) {
        echo "<p>La colonne id_departement est déjà une clé primaire.</p>";
    } else {
        echo "<p>La colonne id_departement n'est pas une clé primaire.</p>";
    }
    
    // Afficher la structure de la table
    echo "<h3>Structure de la table departement</h3>";
    $result = $conn->query("DESCRIBE departement");
    echo "<table border='1'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>La table departement n'existe pas.</p>";
}

// Vérifier si la table enseignants existe
echo "<h2>Table enseignants</h2>";
$result = $conn->query("SHOW TABLES LIKE 'enseignants'");
if ($result->num_rows > 0) {
    echo "<p>La table enseignants existe.</p>";
    
    // Vérifier si la colonne id_enseignant est une clé primaire
    $result = $conn->query("SHOW KEYS FROM enseignants WHERE Key_name = 'PRIMARY'");
    if ($result->num_rows > 0) {
        echo "<p>La colonne id_enseignant est déjà une clé primaire.</p>";
    } else {
        echo "<p>La colonne id_enseignant n'est pas une clé primaire.</p>";
    }
    
    // Afficher la structure de la table
    echo "<h3>Structure de la table enseignants</h3>";
    $result = $conn->query("DESCRIBE enseignants");
    echo "<table border='1'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>La table enseignants n'existe pas.</p>";
}

// Vérifier si la table affectations existe
echo "<h2>Table affectations</h2>";
$result = $conn->query("SHOW TABLES LIKE 'affectations'");
if ($result->num_rows > 0) {
    echo "<p>La table affectations existe déjà.</p>";
    
    // Afficher la structure de la table
    echo "<h3>Structure de la table affectations</h3>";
    $result = $conn->query("DESCRIBE affectations");
    echo "<table border='1'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>La table affectations n'existe pas.</p>";
}

// Ajouter un formulaire pour exécuter les requêtes SQL
echo "<h2>Exécuter des requêtes SQL</h2>";
echo "<form method='post' action=''>";
echo "<textarea name='sql_query' rows='10' cols='80'>";
echo "-- D'abord, corrigeons la table unites_enseignements\n";
echo "ALTER TABLE unites_enseignements \n";
echo "MODIFY COLUMN id_ue INT NOT NULL AUTO_INCREMENT,\n";
echo "ADD PRIMARY KEY (id_ue);\n\n";
echo "-- Ensuite, créons la table affectations\n";
echo "CREATE TABLE affectations (\n";
echo "    id_affectation INT AUTO_INCREMENT PRIMARY KEY,\n";
echo "    id_ue INT NOT NULL,\n";
echo "    id_enseignant INT NOT NULL,\n";
echo "    type_enseignement ENUM('Cours','TD','TP') NOT NULL,\n";
echo "    date_affectation DATETIME DEFAULT CURRENT_TIMESTAMP,\n";
echo "    id_departement INT,\n";
echo "    \n";
echo "    FOREIGN KEY (id_ue) REFERENCES unites_enseignements(id_ue),\n";
echo "    FOREIGN KEY (id_enseignant) REFERENCES enseignants(id_enseignant)\n";
echo ");\n";
echo "</textarea><br>";
echo "<input type='submit' name='execute_sql' value='Exécuter'>";
echo "</form>";

// Traiter le formulaire
if (isset($_POST['execute_sql'])) {
    $sql_query = $_POST['sql_query'];
    
    // Diviser la requête en plusieurs requêtes
    $queries = explode(';', $sql_query);
    
    echo "<h3>Résultats de l'exécution</h3>";
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        echo "<p><strong>Requête :</strong> " . htmlspecialchars($query) . "</p>";
        
        try {
            $result = $conn->query($query);
            if ($result === TRUE) {
                echo "<p style='color: green;'>Requête exécutée avec succès.</p>";
            } else {
                echo "<p style='color: green;'>Requête exécutée avec succès. Résultats :</p>";
                echo "<pre>";
                print_r($result);
                echo "</pre>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
        }
    }
}

$conn->close();
?>
