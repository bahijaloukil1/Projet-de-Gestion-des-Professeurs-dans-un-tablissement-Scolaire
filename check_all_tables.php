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

echo "<h1>Vérification des tables pour la gestion des emplois du temps</h1>";

// Liste des tables nécessaires
$tables = [
    'groupes' => [
        'structure' => "CREATE TABLE groupes (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'sample_data' => "INSERT INTO groupes (nom, type, filiere, niveau, capacite, annee_scolaire, departement_id) VALUES
            ('Groupe 1', 'TD', 'Informatique', 'L1', 30, '2023-2024', 1),
            ('Groupe 2', 'TD', 'Informatique', 'L1', 30, '2023-2024', 1),
            ('Groupe 3', 'TP', 'Informatique', 'L1', 15, '2023-2024', 1),
            ('Groupe 4', 'TP', 'Informatique', 'L1', 15, '2023-2024', 1),
            ('Groupe 5', 'TD', 'Informatique', 'L2', 25, '2023-2024', 1)"
    ],
    'salles' => [
        'structure' => "CREATE TABLE salles (
            id_salle INT AUTO_INCREMENT PRIMARY KEY,
            nom VARCHAR(100) NOT NULL,
            capacite INT NOT NULL DEFAULT 30,
            type VARCHAR(50) DEFAULT 'Standard',
            batiment VARCHAR(50),
            etage VARCHAR(10),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'sample_data' => "INSERT INTO salles (nom, capacite, type, batiment, etage) VALUES
            ('Salle 101', 30, 'Cours', 'Bâtiment A', 'RDC'),
            ('Salle 102', 25, 'TP', 'Bâtiment A', 'RDC'),
            ('Salle 201', 40, 'Cours', 'Bâtiment A', '1er'),
            ('Salle 202', 35, 'TP', 'Bâtiment A', '1er'),
            ('Salle 301', 50, 'Amphithéâtre', 'Bâtiment B', 'RDC')"
    ],
    'unites_enseignements' => [
        'structure' => "CREATE TABLE unites_enseignements (
            id_ue INT AUTO_INCREMENT PRIMARY KEY,
            code_ue VARCHAR(20) NOT NULL UNIQUE,
            intitule VARCHAR(255) NOT NULL,
            credits INT NOT NULL DEFAULT 3,
            heures_cm INT NOT NULL DEFAULT 0,
            heures_td INT NOT NULL DEFAULT 0,
            heures_tp INT NOT NULL DEFAULT 0,
            semestre VARCHAR(20) NOT NULL,
            id_departement INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'sample_data' => "INSERT INTO unites_enseignements (code_ue, intitule, credits, heures_cm, heures_td, heures_tp, semestre, id_departement) VALUES
            ('INFO101', 'Introduction à l\'informatique', 3, 20, 10, 10, 'S1', 1),
            ('INFO102', 'Algorithmique', 4, 20, 20, 20, 'S1', 1),
            ('MATH101', 'Mathématiques discrètes', 3, 30, 15, 0, 'S1', 1),
            ('INFO201', 'Programmation orientée objet', 4, 20, 10, 30, 'S2', 1),
            ('INFO202', 'Bases de données', 4, 20, 10, 30, 'S2', 1)"
    ],
    'emplois_temps' => [
        'structure' => "CREATE TABLE emplois_temps (
            id_emploi INT AUTO_INCREMENT PRIMARY KEY,
            jour ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi') NOT NULL,
            heure_debut TIME NOT NULL,
            heure_fin TIME NOT NULL,
            id_groupe INT NOT NULL,
            id_salle INT NOT NULL,
            id_ue INT NOT NULL,
            id_enseignant INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (id_groupe) REFERENCES groupes(id_groupe) ON DELETE CASCADE,
            FOREIGN KEY (id_salle) REFERENCES salles(id_salle) ON DELETE CASCADE,
            FOREIGN KEY (id_ue) REFERENCES unites_enseignements(id_ue) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'sample_data' => ""
    ]
];

// Vérifier et créer les tables si nécessaire
foreach ($tables as $table_name => $table_info) {
    echo "<h2>Table '$table_name'</h2>";
    
    $result = $conn->query("SHOW TABLES LIKE '$table_name'");
    if ($result->num_rows == 0) {
        echo "<p style='color:red'>La table '$table_name' n'existe pas. Création de la table...</p>";
        
        if ($conn->query($table_info['structure']) === TRUE) {
            echo "<p style='color:green'>Table '$table_name' créée avec succès.</p>";
            
            // Ajouter des données d'exemple si disponibles
            if (!empty($table_info['sample_data'])) {
                if ($conn->query($table_info['sample_data']) === TRUE) {
                    echo "<p style='color:green'>Données d'exemple ajoutées avec succès.</p>";
                } else {
                    echo "<p style='color:red'>Erreur lors de l'ajout des données d'exemple : " . $conn->error . "</p>";
                }
            }
        } else {
            echo "<p style='color:red'>Erreur lors de la création de la table '$table_name' : " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:green'>La table '$table_name' existe déjà.</p>";
        
        // Afficher le nombre d'enregistrements
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table_name");
        if ($count_result) {
            $count = $count_result->fetch_assoc()['count'];
            echo "<p>Nombre d'enregistrements : $count</p>";
            
            if ($count == 0 && !empty($table_info['sample_data'])) {
                echo "<form method='post'>";
                echo "<input type='hidden' name='action' value='add_sample_data'>";
                echo "<input type='hidden' name='table' value='$table_name'>";
                echo "<button type='submit'>Ajouter des données d'exemple</button>";
                echo "</form>";
            }
        }
    }
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_sample_data' && isset($_POST['table'])) {
        $table = $_POST['table'];
        if (isset($tables[$table]) && !empty($tables[$table]['sample_data'])) {
            if ($conn->query($tables[$table]['sample_data']) === TRUE) {
                echo "<p style='color:green'>Données d'exemple ajoutées avec succès à la table '$table'.</p>";
                echo "<meta http-equiv='refresh' content='1'>"; // Rafraîchir la page
            } else {
                echo "<p style='color:red'>Erreur lors de l'ajout des données d'exemple à la table '$table' : " . $conn->error . "</p>";
            }
        }
    }
}

// Fermer la connexion
$conn->close();
?>

<p><a href="emplois_temps_complet.php">Retour à la gestion des emplois du temps</a></p>
