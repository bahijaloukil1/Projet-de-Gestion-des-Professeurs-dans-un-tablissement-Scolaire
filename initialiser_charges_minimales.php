<?php
// Script pour initialiser les charges minimales pour tous les enseignants

// Connexion à la base de données
$mysqli = new mysqli("localhost", "root", "", "gestion_coordinteur");
if ($mysqli->connect_error) {
    die("Erreur de connexion : " . $mysqli->connect_error);
}

echo "<h2>🔧 Initialisation des charges minimales</h2>";

// Récupérer tous les enseignants
$enseignants_query = "SELECT id, nom, prenom FROM utilisateurs WHERE type_utilisateur = 'enseignant'";
$enseignants_result = $mysqli->query($enseignants_query);

if (!$enseignants_result) {
    die("❌ Erreur lors de la récupération des enseignants : " . $mysqli->error);
}

$annees = ['2023-2024', '2024-2025', '2025-2026'];
$charge_par_defaut = 192; // 192 heures par défaut

$total_insere = 0;
$total_existe = 0;

echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px; border-radius: 5px;'>";
echo "<h3>📊 Traitement en cours...</h3>";

while ($enseignant = $enseignants_result->fetch_assoc()) {
    echo "<strong>👤 {$enseignant['nom']} {$enseignant['prenom']} (ID: {$enseignant['id']})</strong><br>";

    foreach ($annees as $annee) {
        // Vérifier si la charge existe déjà
        $check = $mysqli->prepare("SELECT id FROM charge_horaire_minimale WHERE id_utilisateur = ? AND annee_scolaire = ?");
        $check->bind_param("is", $enseignant['id'], $annee);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo "&nbsp;&nbsp;⚠️ $annee : Charge déjà définie<br>";
            $total_existe++;
        } else {
            // Insérer la charge minimale
            $insert = $mysqli->prepare("INSERT INTO charge_horaire_minimale (id_utilisateur, annee_scolaire, charge_min, date_creation) VALUES (?, ?, ?, NOW())");
            $insert->bind_param("isi", $enseignant['id'], $annee, $charge_par_defaut);

            if ($insert->execute()) {
                echo "&nbsp;&nbsp;✅ $annee : Charge de {$charge_par_defaut}h ajoutée<br>";
                $total_insere++;
            } else {
                echo "&nbsp;&nbsp;❌ $annee : Erreur - " . $insert->error . "<br>";
            }
        }
    }
    echo "<br>";
}

echo "</div>";

echo "<div style='background: #d4edda; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
echo "<h3>📈 Résumé :</h3>";
echo "<ul>";
echo "<li><strong>Charges ajoutées :</strong> $total_insere</li>";
echo "<li><strong>Charges existantes :</strong> $total_existe</li>";
echo "<li><strong>Charge par défaut :</strong> {$charge_par_defaut}h</li>";
echo "</ul>";
echo "</div>";

// Afficher le contenu de la table
echo "<div style='background: #d1ecf1; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #bee5eb;'>";
echo "<h3>📋 Contenu actuel de la table charge_horaire_minimale :</h3>";

$all_charges = $mysqli->query("
    SELECT cm.*, u.nom, u.prenom
    FROM charge_horaire_minimale cm
    LEFT JOIN utilisateurs u ON cm.id_utilisateur = u.id
    ORDER BY u.nom, u.prenom, cm.annee_scolaire
");

if ($all_charges && $all_charges->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #007bff; color: white;'>";
    echo "<th>ID</th><th>Enseignant</th><th>Année</th><th>Charge Min</th><th>Date Création</th>";
    echo "</tr>";

    while ($charge = $all_charges->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $charge['id'] . "</td>";
        echo "<td>" . htmlspecialchars($charge['nom'] . ' ' . $charge['prenom']) . " (ID: " . $charge['id_utilisateur'] . ")</td>";
        echo "<td>" . $charge['annee_scolaire'] . "</td>";
        echo "<td><strong>" . $charge['charge_min'] . "h</strong></td>";
        echo "<td>" . $charge['date_creation'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p><strong>Total :</strong> " . $all_charges->num_rows . " enregistrements</p>";
} else {
    echo "<p>❌ Aucune charge minimale trouvée dans la table</p>";
}
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; margin: 10px; border-radius: 5px; border: 1px solid #ffeaa7;'>";
echo "<h3>🔗 Actions suivantes :</h3>";
echo "<ul>";
echo "<li><a href='gestion_charges_minimales.php'>📝 Gérer les charges minimales</a></li>";
echo "<li><a href='Calcul_automatique_charge_horaire.php'>🧮 Calculer les charges horaires</a></li>";
echo "<li><a href='Affichage_liste_UE.php'>📚 Retour à la liste des UE</a></li>";
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
</style>
