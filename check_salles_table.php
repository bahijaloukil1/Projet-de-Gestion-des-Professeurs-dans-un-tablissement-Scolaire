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

echo "<h1>Vérification de la table salles</h1>";

// Vérifier si la table salles existe
$result = $conn->query("SHOW TABLES LIKE 'salles'");
if ($result->num_rows == 0) {
    echo "<p style='color:red'>La table 'salles' n'existe pas. Création de la table...</p>";
    
    // Créer la table salles
    $sql = "CREATE TABLE salles (
        id_salle INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        capacite INT NOT NULL DEFAULT 30,
        type VARCHAR(50) DEFAULT 'Standard',
        batiment VARCHAR(50),
        etage VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>Table 'salles' créée avec succès.</p>";
        
        // Insérer quelques salles de test
        $sql = "INSERT INTO salles (nom, capacite, type, batiment, etage) VALUES
            ('Salle 101', 30, 'Cours', 'Bâtiment A', 'RDC'),
            ('Salle 102', 25, 'TP', 'Bâtiment A', 'RDC'),
            ('Salle 201', 40, 'Cours', 'Bâtiment A', '1er'),
            ('Salle 202', 35, 'TP', 'Bâtiment A', '1er'),
            ('Salle 301', 50, 'Amphithéâtre', 'Bâtiment B', 'RDC')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green'>Salles de test ajoutées avec succès.</p>";
        } else {
            echo "<p style='color:red'>Erreur lors de l'ajout des salles de test : " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:red'>Erreur lors de la création de la table 'salles' : " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:green'>La table 'salles' existe déjà.</p>";
    
    // Afficher la structure de la table
    $result = $conn->query("DESCRIBE salles");
    if ($result) {
        echo "<h2>Structure de la table 'salles'</h2>";
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
    $result = $conn->query("SELECT * FROM salles");
    if ($result) {
        echo "<h2>Données de la table 'salles'</h2>";
        if ($result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Capacité</th><th>Type</th><th>Bâtiment</th><th>Étage</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['id_salle'] . "</td>";
                echo "<td>" . $row['nom'] . "</td>";
                echo "<td>" . $row['capacite'] . "</td>";
                echo "<td>" . $row['type'] . "</td>";
                echo "<td>" . $row['batiment'] . "</td>";
                echo "<td>" . $row['etage'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Aucune salle trouvée dans la table.</p>";
            
            // Proposer d'ajouter des salles de test
            echo "<form method='post'>";
            echo "<input type='hidden' name='action' value='add_test_data'>";
            echo "<button type='submit'>Ajouter des salles de test</button>";
            echo "</form>";
        }
    }
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_test_data') {
        $sql = "INSERT INTO salles (nom, capacite, type, batiment, etage) VALUES
            ('Salle 101', 30, 'Cours', 'Bâtiment A', 'RDC'),
            ('Salle 102', 25, 'TP', 'Bâtiment A', 'RDC'),
            ('Salle 201', 40, 'Cours', 'Bâtiment A', '1er'),
            ('Salle 202', 35, 'TP', 'Bâtiment A', '1er'),
            ('Salle 301', 50, 'Amphithéâtre', 'Bâtiment B', 'RDC')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green'>Salles de test ajoutées avec succès.</p>";
            echo "<meta http-equiv='refresh' content='1'>"; // Rafraîchir la page
        } else {
            echo "<p style='color:red'>Erreur lors de l'ajout des salles de test : " . $conn->error . "</p>";
        }
    }
}

// Fermer la connexion
$conn->close();
?>

<p><a href="emplois_temps_complet.php">Retour à la gestion des emplois du temps</a></p>
