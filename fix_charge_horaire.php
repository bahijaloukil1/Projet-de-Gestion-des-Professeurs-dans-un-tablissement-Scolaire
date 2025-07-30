<?php
// Script pour corriger le fichier charge_horaire.php
require_once 'config.php';

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correction du fichier charge_horaire.php</h1>";

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
    
    // Vérifier si la colonne ue_id existe dans la table affectations
    $stmt = $pdo->query("SHOW COLUMNS FROM affectations LIKE 'ue_id'");
    $ueIdExists = $stmt->rowCount() > 0;
    
    if (!$ueIdExists) {
        echo "<p style='color:red'>La colonne 'ue_id' n'existe pas dans la table 'affectations'.</p>";
        echo "<p>Ajout de la colonne 'ue_id' à la table 'affectations'...</p>";
        
        try {
            // Ajouter la colonne ue_id
            $pdo->exec("ALTER TABLE affectations ADD COLUMN ue_id INT NOT NULL AFTER professeur_id");
            echo "<p style='color:green'>La colonne 'ue_id' a été ajoutée à la table 'affectations' avec succès.</p>";
            
            // Récupérer les données des unités d'enseignement
            $stmt = $pdo->query("SELECT * FROM unites_enseignements");
            $ues = $stmt->fetchAll();
            
            echo "<p>Nombre d'unités d'enseignement trouvées : " . count($ues) . "</p>";
            
            // Mettre à jour les affectations existantes avec des valeurs par défaut pour ue_id
            if (!empty($ues)) {
                // Utiliser la première UE comme valeur par défaut
                $defaultUeId = $ues[0]['id_ue'];
                
                $stmt = $pdo->prepare("UPDATE affectations SET ue_id = ? WHERE ue_id = 0 OR ue_id IS NULL");
                $stmt->execute([$defaultUeId]);
                
                $rowCount = $stmt->rowCount();
                echo "<p style='color:green'>$rowCount affectations ont été mises à jour avec l'UE par défaut (ID: $defaultUeId).</p>";
            } else {
                echo "<p style='color:orange'>Aucune unité d'enseignement trouvée. Veuillez en créer avant d'affecter des professeurs.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color:red'>Erreur lors de l'ajout de la colonne: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color:green'>La colonne 'ue_id' existe déjà dans la table 'affectations'.</p>";
    }
    
    // Tester la requête SQL corrigée
    echo "<h2>Test de la requête SQL corrigée</h2>";
    
    try {
        $query = "
            SELECT
                p.id,
                p.nom,
                p.prenom,
                'permanent' AS type,
                192 AS heures_max,
                96 AS heures_vacataire,
                COALESCE(SUM(ue.volume_horaire), 0) AS heures_affectees
            FROM
                professeurs p
            LEFT JOIN affectations a ON p.id = a.professeur_id
            LEFT JOIN unites_enseignements ue ON a.ue_id = ue.id_ue
            WHERE
                p.id_departement = :departement_id
            GROUP BY
                p.id
            ORDER BY
                p.nom, p.prenom
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([':departement_id' => 1]); // Utiliser un ID de département valide
        $professeurs = $stmt->fetchAll();
        
        echo "<p style='color:green'>La requête SQL a été exécutée avec succès.</p>";
        echo "<p>Nombre de professeurs trouvés : " . count($professeurs) . "</p>";
        
        if (!empty($professeurs)) {
            echo "<h3>Résultats de la requête</h3>";
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Type</th><th>Heures Max</th><th>Heures Vacataire</th><th>Heures Affectées</th></tr>";
            
            foreach ($professeurs as $prof) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($prof['id']) . "</td>";
                echo "<td>" . htmlspecialchars($prof['nom']) . "</td>";
                echo "<td>" . htmlspecialchars($prof['prenom']) . "</td>";
                echo "<td>" . htmlspecialchars($prof['type']) . "</td>";
                echo "<td>" . htmlspecialchars($prof['heures_max']) . "</td>";
                echo "<td>" . htmlspecialchars($prof['heures_vacataire']) . "</td>";
                echo "<td>" . htmlspecialchars($prof['heures_affectees']) . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Aucun professeur trouvé.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>Erreur lors de l'exécution de la requête: " . $e->getMessage() . "</p>";
    }
    
    // Ajouter un lien vers charge_horaire.php
    echo "<p><a href='charge_horaire.php' style='padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Retour à la page des charges horaires</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de base de données: " . $e->getMessage() . "</p>";
}
?>
