<?php
// Script pour corriger le problème de redirection
session_start();

// Afficher les erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correction du problème de redirection</h1>";

// Vérifier l'état actuel de la session
echo "<h2>État actuel de la session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
if ($isLoggedIn) {
    echo "<p style='color:green'>Utilisateur connecté (ID: " . $_SESSION['user_id'] . ")</p>";
} else {
    echo "<p style='color:red'>Utilisateur non connecté</p>";
}

// Vérifier si le type d'utilisateur est défini
if (isset($_SESSION['type_utilisateur'])) {
    echo "<p>Type d'utilisateur (type_utilisateur): " . $_SESSION['type_utilisateur'] . "</p>";
} else {
    echo "<p style='color:red'>Le type d'utilisateur (type_utilisateur) n'est pas défini dans la session.</p>";
}

// Vérifier si le user_type est défini
if (isset($_SESSION['user_type'])) {
    echo "<p>Type d'utilisateur (user_type): " . $_SESSION['user_type'] . "</p>";
} else {
    echo "<p style='color:red'>Le type d'utilisateur (user_type) n'est pas défini dans la session.</p>";
}

// Vérifier si le rôle est défini
if (isset($_SESSION['role'])) {
    echo "<p>Rôle (role): " . $_SESSION['role'] . "</p>";
} else {
    echo "<p style='color:red'>Le rôle (role) n'est pas défini dans la session.</p>";
}

// Vérifier si l'ID du département est défini
if (isset($_SESSION['id_departement'])) {
    echo "<p>ID du département (id_departement): " . $_SESSION['id_departement'] . "</p>";
} else {
    echo "<p style='color:red'>L'ID du département (id_departement) n'est pas défini dans la session.</p>";
}

// Vérifier la condition de redirection
$redirectCondition = !isset($_SESSION['user_id']) || $_SESSION['type_utilisateur'] !== 'chef_departement';
echo "<h2>Condition de redirection</h2>";
echo "<p>!isset(\$_SESSION['user_id']) || \$_SESSION['type_utilisateur'] !== 'chef_departement'</p>";
echo "<p>Résultat: " . ($redirectCondition ? "VRAI (redirection)" : "FAUX (pas de redirection)") . "</p>";

// Analyser la condition
echo "<h2>Analyse de la condition</h2>";
echo "<p>!isset(\$_SESSION['user_id']): " . (!isset($_SESSION['user_id']) ? "VRAI" : "FAUX") . "</p>";
if (isset($_SESSION['type_utilisateur'])) {
    echo "<p>\$_SESSION['type_utilisateur'] !== 'chef_departement': " . ($_SESSION['type_utilisateur'] !== 'chef_departement' ? "VRAI" : "FAUX") . "</p>";
} else {
    echo "<p>\$_SESSION['type_utilisateur'] n'est pas défini, donc la condition est VRAI</p>";
}

// Proposer des solutions
echo "<h2>Solutions possibles</h2>";

// Formulaire pour mettre à jour la session
echo "<form method='post' style='margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;'>";
echo "<h3>Mettre à jour la session</h3>";
echo "<input type='hidden' name='action' value='update_session'>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label>ID Utilisateur: <input type='text' name='user_id' value='" . ($_SESSION['user_id'] ?? '') . "'></label>";
echo "</div>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label>Type Utilisateur (type_utilisateur): <select name='type_utilisateur'>";
echo "<option value='chef_departement'" . (($_SESSION['type_utilisateur'] ?? '') === 'chef_departement' ? " selected" : "") . ">chef_departement</option>";
echo "<option value='coordinateur'" . (($_SESSION['type_utilisateur'] ?? '') === 'coordinateur' ? " selected" : "") . ">coordinateur</option>";
echo "<option value='enseignant'" . (($_SESSION['type_utilisateur'] ?? '') === 'enseignant' ? " selected" : "") . ">enseignant</option>";
echo "<option value='admin'" . (($_SESSION['type_utilisateur'] ?? '') === 'admin' ? " selected" : "") . ">admin</option>";
echo "</select></label>";
echo "</div>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label>Type Utilisateur (user_type): <select name='user_type'>";
echo "<option value='chef_departement'" . (($_SESSION['user_type'] ?? '') === 'chef_departement' ? " selected" : "") . ">chef_departement</option>";
echo "<option value='coordinateur'" . (($_SESSION['user_type'] ?? '') === 'coordinateur' ? " selected" : "") . ">coordinateur</option>";
echo "<option value='enseignant'" . (($_SESSION['user_type'] ?? '') === 'enseignant' ? " selected" : "") . ">enseignant</option>";
echo "<option value='admin'" . (($_SESSION['user_type'] ?? '') === 'admin' ? " selected" : "") . ">admin</option>";
echo "</select></label>";
echo "</div>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label>Rôle: <select name='role'>";
echo "<option value='chef_departement'" . (($_SESSION['role'] ?? '') === 'chef_departement' ? " selected" : "") . ">chef_departement</option>";
echo "<option value='coordinateur'" . (($_SESSION['role'] ?? '') === 'coordinateur' ? " selected" : "") . ">coordinateur</option>";
echo "<option value='enseignant'" . (($_SESSION['role'] ?? '') === 'enseignant' ? " selected" : "") . ">enseignant</option>";
echo "<option value='admin'" . (($_SESSION['role'] ?? '') === 'admin' ? " selected" : "") . ">admin</option>";
echo "</select></label>";
echo "</div>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label>ID Département: <input type='text' name='id_departement' value='" . ($_SESSION['id_departement'] ?? '') . "'></label>";
echo "</div>";

echo "<button type='submit' style='padding: 5px 10px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer;'>Mettre à jour la session</button>";
echo "</form>";

// Formulaire pour modifier le fichier charge_horaire.php
echo "<form method='post' style='margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;'>";
echo "<h3>Modifier le fichier charge_horaire.php</h3>";
echo "<input type='hidden' name='action' value='modify_file'>";
echo "<p>Modifier la condition de redirection dans charge_horaire.php :</p>";
echo "<div style='margin-bottom: 10px;'>";
echo "<select name='condition_type'>";
echo "<option value='type_utilisateur'>Utiliser type_utilisateur</option>";
echo "<option value='user_type'>Utiliser user_type</option>";
echo "<option value='role'>Utiliser role</option>";
echo "<option value='both'>Utiliser les deux (type_utilisateur OU user_type)</option>";
echo "</select>";
echo "</div>";
echo "<button type='submit' style='padding: 5px 10px; background-color: #2196F3; color: white; border: none; border-radius: 3px; cursor: pointer;'>Modifier le fichier</button>";
echo "</form>";

// Traiter les actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_session') {
        // Mettre à jour la session
        if (isset($_POST['user_id']) && !empty($_POST['user_id'])) {
            $_SESSION['user_id'] = $_POST['user_id'];
        }
        
        if (isset($_POST['type_utilisateur'])) {
            $_SESSION['type_utilisateur'] = $_POST['type_utilisateur'];
        }
        
        if (isset($_POST['user_type'])) {
            $_SESSION['user_type'] = $_POST['user_type'];
        }
        
        if (isset($_POST['role'])) {
            $_SESSION['role'] = $_POST['role'];
        }
        
        if (isset($_POST['id_departement'])) {
            $_SESSION['id_departement'] = $_POST['id_departement'];
            $_SESSION['departement_id'] = $_POST['id_departement']; // Pour la compatibilité
        }
        
        echo "<p style='color:green'>Session mise à jour avec succès. <a href='fix_session_redirect.php'>Rafraîchir</a></p>";
    } elseif ($_POST['action'] === 'modify_file') {
        // Modifier le fichier charge_horaire.php
        $file = 'charge_horaire.php';
        $content = file_get_contents($file);
        
        $condition_type = $_POST['condition_type'];
        $new_condition = '';
        
        switch ($condition_type) {
            case 'type_utilisateur':
                $new_condition = "if (!isset(\$_SESSION['user_id']) || \$_SESSION['type_utilisateur'] !== 'chef_departement') {";
                break;
            case 'user_type':
                $new_condition = "if (!isset(\$_SESSION['user_id']) || \$_SESSION['user_type'] !== 'chef_departement') {";
                break;
            case 'role':
                $new_condition = "if (!isset(\$_SESSION['user_id']) || \$_SESSION['role'] !== 'chef_departement') {";
                break;
            case 'both':
                $new_condition = "if (!isset(\$_SESSION['user_id']) || (\$_SESSION['type_utilisateur'] !== 'chef_departement' && \$_SESSION['user_type'] !== 'chef_departement')) {";
                break;
        }
        
        $content = preg_replace("/if \(!isset\(\\\$_SESSION\['user_id'\]\) \|\| \\\$_SESSION\['type_utilisateur'\] !== 'chef_departement'\) \{/", $new_condition, $content);
        file_put_contents($file, $content);
        
        echo "<p style='color:green'>Fichier charge_horaire.php modifié avec succès. <a href='fix_session_redirect.php'>Rafraîchir</a></p>";
    }
}

// Ajouter des liens utiles
echo "<h2>Liens utiles</h2>";
echo "<ul>";
echo "<li><a href='login.php'>Page de connexion</a></li>";
echo "<li><a href='charge_horaire.php'>Charge Horaire</a></li>";
echo "<li><a href='debug_session.php'>Déboguer la session</a></li>";
echo "</ul>";
?>
