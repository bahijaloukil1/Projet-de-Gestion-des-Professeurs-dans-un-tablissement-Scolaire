<?php
session_start();

echo "<h2>🔍 Debug - Souhaits Enseignants</h2>";

// 1. Vérifier la session
echo "<h3>1. Variables de session :</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 2. Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "gestion_coordinteur");
if ($mysqli->connect_error) {
    die("❌ Erreur de connexion : " . $mysqli->connect_error);
}
echo "✅ Connexion à la base de données réussie<br><br>";

// 3. Vérifier la structure de la table souhaits_enseignants
echo "<h3>2. Structure de la table souhaits_enseignants :</h3>";
$result = $mysqli->query("DESCRIBE souhaits_enseignants");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
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
    echo "</table><br>";
} else {
    echo "❌ Erreur lors de la récupération de la structure : " . $mysqli->error . "<br>";
}

// 4. Vérifier le contenu actuel de la table
echo "<h3>3. Contenu actuel de la table souhaits_enseignants :</h3>";
$result = $mysqli->query("SELECT * FROM souhaits_enseignants");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Souhait</th><th>ID Enseignant</th><th>ID UE</th><th>Année Scolaire</th><th>Date Souhait</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_souhait'] . "</td>";
        echo "<td>" . $row['id_enseignant'] . "</td>";
        echo "<td>" . $row['id_ue'] . "</td>";
        echo "<td>" . $row['annee_scolaire'] . "</td>";
        echo "<td>" . $row['date_souhait'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "📝 La table est vide ou erreur : " . $mysqli->error . "<br><br>";
}

// 5. Vérifier les UE disponibles
echo "<h3>4. Unités d'enseignement disponibles :</h3>";
$result = $mysqli->query("SELECT ue.id_ue, ue.filiere, ue.niveau, m.nom as matiere FROM unites_enseignements ue LEFT JOIN matieres m ON ue.id_matiere = m.id_matiere LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID UE</th><th>Matière</th><th>Filière</th><th>Niveau</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_ue'] . "</td>";
        echo "<td>" . $row['matiere'] . "</td>";
        echo "<td>" . $row['filiere'] . "</td>";
        echo "<td>" . $row['niveau'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "❌ Aucune UE trouvée ou erreur : " . $mysqli->error . "<br><br>";
}

// 6. Test d'insertion manuelle
echo "<h3>5. Test d'insertion manuelle :</h3>";
if (isset($_SESSION['user_id'])) {
    $test_id_enseignant = $_SESSION['user_id'];
    $test_id_ue = 1; // Premier UE
    $test_annee = "2024-2025";
    
    echo "Tentative d'insertion avec :<br>";
    echo "- ID Enseignant: $test_id_enseignant<br>";
    echo "- ID UE: $test_id_ue<br>";
    echo "- Année: $test_annee<br><br>";
    
    $stmt = $mysqli->prepare("INSERT INTO souhaits_enseignants (id_enseignant, id_ue, annee_scolaire, date_souhait) VALUES (?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("iis", $test_id_enseignant, $test_id_ue, $test_annee);
        if ($stmt->execute()) {
            echo "✅ Test d'insertion réussi !<br>";
        } else {
            echo "❌ Erreur lors de l'insertion : " . $stmt->error . "<br>";
        }
        $stmt->close();
    } else {
        echo "❌ Erreur de préparation : " . $mysqli->error . "<br>";
    }
} else {
    echo "❌ Pas d'ID utilisateur dans la session<br>";
}

// 7. Vérifier à nouveau le contenu après le test
echo "<h3>6. Contenu après test d'insertion :</h3>";
$result = $mysqli->query("SELECT * FROM souhaits_enseignants");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID Souhait</th><th>ID Enseignant</th><th>ID UE</th><th>Année Scolaire</th><th>Date Souhait</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_souhait'] . "</td>";
        echo "<td>" . $row['id_enseignant'] . "</td>";
        echo "<td>" . $row['id_ue'] . "</td>";
        echo "<td>" . $row['annee_scolaire'] . "</td>";
        echo "<td>" . $row['date_souhait'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "📝 La table est toujours vide<br>";
}

$mysqli->close();

echo "<br><h3>🔗 Liens de test :</h3>";
echo "<a href='souhaits_enseignants.php'>Tester la page souhaits</a><br>";
echo "<a href='test_session.php'>Vérifier la session</a><br>";
echo "<a href='Affichage_liste_UE.php'>Retour à la liste des UE</a>";
?>
