<?php
session_start();

// Enregistrer les informations de débogage
$debug_file = 'debug_redirect.log';
file_put_contents($debug_file, "=== Nouvelle tentative de redirection ===\n", FILE_APPEND);
file_put_contents($debug_file, "Date: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents($debug_file, "SESSION: " . print_r($_SESSION, true) . "\n", FILE_APPEND);

// Vérifier le type d'utilisateur
$user_type = $_SESSION['user_type'] ?? 'inconnu';
file_put_contents($debug_file, "Type d'utilisateur: " . $user_type . "\n", FILE_APPEND);

// Rediriger en fonction du type d'utilisateur
if ($user_type === 'coordinateur') {
    file_put_contents($debug_file, "Redirection vers dashborde_coordinateur.php\n", FILE_APPEND);
    header("Location: dashborde_coordinateur.php");
    exit;
} elseif ($user_type === 'enseignant') {
    file_put_contents($debug_file, "Redirection vers dashboard_enseignant.php\n", FILE_APPEND);
    header("Location: /gestion_groupes/dashboard_enseignant.php");
    exit;
} elseif ($user_type === 'admin') {
    file_put_contents($debug_file, "Redirection vers admin_dashboard.php\n", FILE_APPEND);
    header("Location: admin_dashboard.php");
    exit;
} elseif ($user_type === 'chef_departement') {
    file_put_contents($debug_file, "Redirection vers chef_dashboard.php\n", FILE_APPEND);
    header("Location: chef_dashboard.php");
    exit;
} else {
    file_put_contents($debug_file, "Type d'utilisateur inconnu, redirection vers chef_dashboard.php\n", FILE_APPEND);
    header("Location: chef_dashboard.php");
    exit;
}
?>
