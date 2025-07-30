<?php
// Script pour corriger la structure de la table charge_horaire_minimale

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "gestion_coordinteur");
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

echo "<h2>🔧 Correction de la table charge_horaire_minimale</h2>";

// 1. Vérifier si la table existe
$result = $mysqli->query("SHOW TABLES LIKE 'charge_horaire_minimale'");
if ($result->num_rows === 0) {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 10px; border-radius: 5px;'>";
    echo "<h3>📋 Création de la table charge_horaire_minimale</h3>";
    
    $create_table = "CREATE TABLE charge_horaire_minimale (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_enseignant INT NOT NULL,
        annee_scolaire VARCHAR(20) NOT NULL,
        charge_min INT NOT NULL DEFAULT 192,
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_modification TIMESTAMP NULL,
        UNIQUE KEY unique_enseignant_annee (id_enseignant, annee_scolaire),
        FOREIGN KEY (id_enseignant) REFERENCES utilisateurs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if ($mysqli->query($create_table)) {
        echo "✅ Table charge_horaire_minimale créée avec succès<br>";
    } else {
        echo "❌ Erreur lors de la création : " . $mysqli->error . "<br>";
    }
    echo "</div>";
} else {
    echo "<div style='background: #d1ecf1; padding: 15px; margin: 10px; border-radius: 5px;'>";
    echo "<h3>📋 Vérification de la structure existante</h3>";
    
    // 2. Vérifier la structure actuelle
    $structure = $mysqli->query("DESCRIBE charge_horaire_minimale");
    if ($structure) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #007bff; color: white;'>";
        echo "<th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th>";
        echo "</tr>";
        
        $columns = [];
        while ($row = $structure->fetch_assoc()) {
            $columns[] = $row['Field'];
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
        
        // 3. Vérifier et corriger les colonnes
        $corrections = [];
        
        // Vérifier si on a id_utilisateur au lieu de id_enseignant
        if (in_array('id_utilisateur', $columns) && !in_array('id_enseignant', $columns)) {
            $corrections[] = "ALTER TABLE charge_horaire_minimale CHANGE id_utilisateur id_enseignant INT NOT NULL";
            echo "⚠️ Colonne id_utilisateur trouvée, sera renommée en id_enseignant<br>";
        }
        
        // Vérifier les autres colonnes nécessaires
        $required_columns = [
            'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'id_enseignant' => 'INT NOT NULL',
            'annee_scolaire' => 'VARCHAR(20) NOT NULL',
            'charge_min' => 'INT NOT NULL DEFAULT 192',
            'date_creation' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            'date_modification' => 'TIMESTAMP NULL'
        ];
        
        foreach ($required_columns as $col => $definition) {
            if (!in_array($col, $columns)) {
                if ($col === 'id') {
                    $corrections[] = "ALTER TABLE charge_horaire_minimale ADD $col $definition FIRST";
                } else {
                    $corrections[] = "ALTER TABLE charge_horaire_minimale ADD $col $definition";
                }
                echo "⚠️ Colonne manquante: $col<br>";
            }
        }
        
        // Appliquer les corrections
        if (!empty($corrections)) {
            echo "<h4>🔧 Application des corrections :</h4>";
            foreach ($corrections as $correction) {
                echo "<code>$correction</code><br>";
                if ($mysqli->query($correction)) {
                    echo "✅ Correction appliquée<br>";
                } else {
                    echo "❌ Erreur : " . $mysqli->error . "<br>";
                }
            }
        } else {
            echo "✅ Structure de la table correcte<br>";
        }
    }
    echo "</div>";
}

// 4. Vérifier la contrainte de clé étrangère
echo "<div style='background: #d4edda; padding: 15px; margin: 10px; border-radius: 5px;'>";
echo "<h3>🔗 Vérification des contraintes</h3>";

$constraints = $mysqli->query("
    SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'gestion_coordinteur' 
    AND TABLE_NAME = 'charge_horaire_minimale' 
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

if ($constraints && $constraints->num_rows > 0) {
    echo "✅ Contraintes existantes :<br>";
    while ($constraint = $constraints->fetch_assoc()) {
        echo "- {$constraint['CONSTRAINT_NAME']}: {$constraint['COLUMN_NAME']} → {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}<br>";
    }
} else {
    echo "⚠️ Aucune contrainte de clé étrangère trouvée. Ajout de la contrainte...<br>";
    $add_fk = "ALTER TABLE charge_horaire_minimale 
               ADD CONSTRAINT fk_charge_enseignant 
               FOREIGN KEY (id_enseignant) REFERENCES utilisateurs(id) ON DELETE CASCADE";
    
    if ($mysqli->query($add_fk)) {
        echo "✅ Contrainte de clé étrangère ajoutée<br>";
    } else {
        echo "❌ Erreur lors de l'ajout de la contrainte : " . $mysqli->error . "<br>";
    }
}
echo "</div>";

// 5. Afficher la structure finale
echo "<div style='background: #f8f9fa; padding: 15px; margin: 10px; border-radius: 5px;'>";
echo "<h3>📊 Structure finale de la table</h3>";

$final_structure = $mysqli->query("DESCRIBE charge_horaire_minimale");
if ($final_structure) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #28a745; color: white;'>";
    echo "<th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th>";
    echo "</tr>";
    
    while ($row = $final_structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Afficher le contenu actuel
$content = $mysqli->query("SELECT COUNT(*) as total FROM charge_horaire_minimale");
if ($content) {
    $count = $content->fetch_assoc();
    echo "<p><strong>Contenu actuel :</strong> {$count['total']} enregistrement(s)</p>";
}

echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; margin: 10px; border-radius: 5px;'>";
echo "<h3>🔗 Actions suivantes :</h3>";
echo "<ul>";
echo "<li><a href='initialiser_charges_minimales.php'>📝 Initialiser les charges minimales</a></li>";
echo "<li><a href='gestion_charges_minimales.php'>⚙️ Gérer les charges minimales</a></li>";
echo "<li><a href='Calcul_automatique_charge_horaire.php'>🧮 Tester le calcul de charge</a></li>";
echo "</ul>";
echo "</div>";

$mysqli->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background: #f8f9fa;
}
table {
    margin: 10px 0;
}
th, td {
    padding: 8px 12px;
    text-align: left;
    border: 1px solid #ddd;
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
code {
    background: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
