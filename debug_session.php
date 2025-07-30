<?php
// Démarrer la session
session_start();

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier de configuration
require_once 'config.php';

echo "<h1>Débogage de Session et Base de Données</h1>";

// Afficher les informations de session
echo "<h2>Informations de Session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    echo "<p style='color:green'>Vous êtes connecté avec l'ID utilisateur: " . $_SESSION['user_id'] . "</p>";

    // Vérifier le type d'utilisateur
    if (isset($_SESSION['user_type'])) {
        echo "<p>Type d'utilisateur (user_type): " . $_SESSION['user_type'] . "</p>";
    } else {
        echo "<p style='color:red'>Le type d'utilisateur (user_type) n'est pas défini dans la session.</p>";
    }

    if (isset($_SESSION['type_utilisateur'])) {
        echo "<p>Type d'utilisateur (type_utilisateur): " . $_SESSION['type_utilisateur'] . "</p>";
    } else {
        echo "<p style='color:red'>Le type d'utilisateur (type_utilisateur) n'est pas défini dans la session.</p>";
    }

    if (isset($_SESSION['role'])) {
        echo "<p>Rôle (role): " . $_SESSION['role'] . "</p>";
    } else {
        echo "<p style='color:red'>Le rôle (role) n'est pas défini dans la session.</p>";
    }

    // Vérifier les informations du département
    if (isset($_SESSION['id_departement'])) {
        echo "<p>ID du département (id_departement): " . $_SESSION['id_departement'] . "</p>";
    } else {
        echo "<p style='color:red'>L'ID du département (id_departement) n'est pas défini dans la session.</p>";
    }

    if (isset($_SESSION['departement_id'])) {
        echo "<p>ID du département (departement_id): " . $_SESSION['departement_id'] . "</p>";
    } else {
        echo "<p style='color:red'>L'ID du département (departement_id) n'est pas défini dans la session.</p>";
    }
} else {
    echo "<p style='color:red'>Vous n'êtes pas connecté.</p>";
}

// Vérifier la connexion à la base de données
echo "<h2>Vérification de la Base de Données</h2>";
try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "<p style='color:green'>Connexion à la base de données réussie.</p>";
    echo "<p>Base de données: " . DB_NAME . "</p>";

    // Vérifier l'existence des tables
    $tables = [
        'departement', 'departements',
        'utilisateurs', 'unites_enseignement', 'unites_enseignements',
        'choix_professeurs'
    ];

    echo "<h3>Vérification des Tables</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $exists = $stmt->rowCount() > 0;

        if ($exists) {
            echo "<li style='color:green'>La table '$table' existe.</li>";

            // Afficher la structure de la table
            $columns = $pdo->query("DESCRIBE $table")->fetchAll();
            echo "<ul>";
            foreach ($columns as $column) {
                echo "<li>" . $column['Field'] . " (" . $column['Type'] . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<li style='color:red'>La table '$table' n'existe pas.</li>";
        }
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p style='color:red'>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
}

// Formulaire pour définir les variables de session (pour les tests)
echo "<h2>Définir les Variables de Session (pour les tests)</h2>";
echo "<form method='post'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>ID Utilisateur: <input type='text' name='user_id' value='1'></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Type Utilisateur (user_type): <input type='text' name='user_type' value='chef_departement'></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Type Utilisateur (type_utilisateur): <input type='text' name='type_utilisateur' value='chef_departement'></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Rôle: <input type='text' name='role' value='chef_departement'></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>ID Département: <input type='text' name='id_departement' value='1'></label>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Nom Département: <input type='text' name='departement_nom' value='Informatique/Mathématiques'></label>";
echo "</div>";
echo "<button type='submit' name='set_session'>Définir la Session</button>";
echo "</form>";

// Traiter le formulaire
if (isset($_POST['set_session'])) {
    $_SESSION['user_id'] = $_POST['user_id'];
    $_SESSION['user_type'] = $_POST['user_type'];
    $_SESSION['type_utilisateur'] = $_POST['type_utilisateur'];
    $_SESSION['role'] = $_POST['role'];
    $_SESSION['id_departement'] = $_POST['id_departement'];
    $_SESSION['departement_id'] = $_POST['id_departement']; // Pour la compatibilité
    $_SESSION['departement_nom'] = $_POST['departement_nom'];

    echo "<p style='color:green'>Session définie. Rafraîchissez la page pour voir les changements.</p>";
    echo "<script>window.location.reload();</script>";
}

// Liens utiles
echo "<h2>Liens Utiles</h2>";
echo "<ul>";
echo "<li><a href='chef_dashboard.php'>Dashboard Chef Département</a></li>";
echo "<li><a href='gestion_choix.php'>Gestion des Choix</a></li>";
echo "<li><a href='validation_choix.php'>Validation des Choix (redirection)</a></li>";
echo "<li><a href='logout.php'>Déconnexion</a></li>";
echo "</ul>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.6;
}

h1, h2, h3 {
    color: #333;
}

pre {
    background-color: #f5f5f5;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: auto;
}

form {
    background-color: #f9f9f9;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
}

button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}

ul {
    margin-top: 10px;
}

li {
    margin-bottom: 5px;
}

a {
    color: #0066cc;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}
</style>
