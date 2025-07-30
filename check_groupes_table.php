<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'gestion-coordinteur';

// Connexion à la base de données
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

echo "<h1>Vérification de la table groupes</h1>";

// Vérifier si la table groupes existe
$result = $conn->query("SHOW TABLES LIKE 'groupes'");
if ($result->num_rows == 0) {
    echo "<p style='color:red'>La table 'groupes' n'existe pas. Création de la table...</p>";
    
    // Créer la table groupes
    $sql = "CREATE TABLE groupes (
        id_groupe INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        type ENUM('TP', 'TD') NOT NULL,
        filiere VARCHAR(100) NOT NULL,
        niveau VARCHAR(50) NOT NULL,
        capacite INT NOT NULL DEFAULT 30,
        annee_scolaire VARCHAR(20) NOT NULL,
        departement_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>Table 'groupes' créée avec succès.</p>";
        
        // Insérer quelques groupes de test
        $sql = "INSERT INTO groupes (nom, type, filiere, niveau, capacite, annee_scolaire, departement_id) VALUES
            ('Groupe 1', 'TD', 'Informatique', 'L1', 30, '2023-2024', 1),
            ('Groupe 2', 'TD', 'Informatique', 'L1', 30, '2023-2024', 1),
            ('Groupe 3', 'TP', 'Informatique', 'L1', 15, '2023-2024', 1),
            ('Groupe 4', 'TP', 'Informatique', 'L1', 15, '2023-2024', 1),
            ('Groupe 5', 'TD', 'Informatique', 'L2', 25, '2023-2024', 1)";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green'>Groupes de test ajoutés avec succès.</p>";
        } else {
            echo "<p style='color:red'>Erreur lors de l'ajout des groupes de test : " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red'>Erreur lors de la création de la table 'groupes' : " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>La table 'groupes' existe déjà.</p>";
    
    // Afficher la structure de la table
    $result = $conn->query("DESCRIBE groupes");
    if ($result) {
        echo "<h2>Structure de la table 'groupes'</h2>";
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
    }
    
    // Afficher les données de la table
    $result = $conn->query("SELECT * FROM groupes");
    if ($result) {
        echo "<h2>Données de la table 'groupes'</h2>";
        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Type</th><th>Filière</th><th>Niveau</th><th>Capacité</th><th>Année scolaire</th><th>Département</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id_groupe'] . "</td>";
                echo "<td>" . $row['nom'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . $row['filiere'] . "</td>";
                echo "<td>" . $row['niveau'] . "</td>";
                echo "<td>" . $row['capacite'] . "</td>";
                echo "<td>" . $row['annee_scolaire'] . "</td>";
                echo "<td>" . $row['departement_id'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Aucun groupe trouvé dans la table.</p>";
            
            // Proposer d'ajouter des groupes de test
            echo "<form method='post'>";
            echo "<input type='hidden' name='action' value='add_test_data'>";
            echo "<button type='submit'>Ajouter des groupes de test</button>";
            echo "</form>";
        }
    }
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_test_data') {
        $sql = "INSERT INTO groupes (nom, type, filiere, niveau, capacite, annee_scolaire, departement_id) VALUES
            ('Groupe 1', 'TD', 'Informatique', 'L1', 30, '2023-2024', 1),
            ('Groupe 2', 'TD', 'Informatique', 'L1', 30, '2023-2024', 1),
            ('Groupe 3', 'TP', 'Informatique', 'L1', 15, '2023-2024', 1),
            ('Groupe 4', 'TP', 'Informatique', 'L1', 15, '2023-2024', 1),
            ('Groupe 5', 'TD', 'Informatique', 'L2', 25, '2023-2024', 1)";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green'>Groupes de test ajoutés avec succès.</p>";
            echo "<meta http-equiv='refresh' content='1'>"; // Rafraîchir la page
        } else {
            echo "<p style='color:red'>Erreur lors de l'ajout des groupes de test : " . $conn->error . "</p>";
        }
    }
}

// Fermer la connexion
$conn->close();
?>

<p><a href="emplois_temps_complet.php">Retour à la gestion des emplois du temps</a></p>
