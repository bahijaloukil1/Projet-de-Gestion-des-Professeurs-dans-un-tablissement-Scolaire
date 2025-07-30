<?php
// Activation du rapport d'erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuration des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => false, // true en production
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_path' => '/' // Ajout important
    ]);
}

// Constantes de connexion BD
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_affectations');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données via PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ✅ Empêcher la redéclaration de la fonction
if (!function_exists('validate_session')) {
    /**
     * Validation de session améliorée
     * @param string|null $role Rôle à vérifier
     * @return bool
     */
    function validate_session($role = null) {
        $required = ['user_id', 'role', 'last_activity', 'ip_address', 'user_agent'];
        foreach ($required as $key) {
            if (!isset($_SESSION[$key])) {
                error_log("Clé de session manquante : $key");
                return false;
            }
        }

        // Sécurité IP + User Agent
        if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
            error_log("Adresse IP modifiée : " . $_SERVER['REMOTE_ADDR']);
            session_destroy();
            header("Location: login.php?error=session_hijack");
            exit();
        }

        if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
            error_log("User-Agent modifié : " . $_SERVER['HTTP_USER_AGENT']);
            session_destroy();
            header("Location: login.php?error=session_hijack");
            exit();
        }

        // Inactivité > 30 min
        if (time() - $_SESSION['last_activity'] > 1800) {
            error_log("Session expirée par inactivité");
            session_destroy();
            header("Location: login.php?error=inactivity");
            exit();
        }

        // Vérification du rôle si spécifié
        if ($role) {
            $allowed_roles = ['admin', 'chef_departement'];
            if (!in_array($role, $allowed_roles)) {
                error_log("Rôle invalide demandé : $role");
                return false;
            }

            if ($_SESSION['role'] !== $role) {
                error_log("Rôle mismatch : {$_SESSION['role']} vs $role");
                return false;
            }

            if ($role === 'chef_departement' && !isset($_SESSION['departement_id'])) {
                error_log("Chef sans département associé");
                session_destroy();
                header("Location: login.php?error=no_department");
                exit();
            }
        }

        // Mise à jour de l'activité
        $_SESSION['last_activity'] = time();
        return true;
    }
}
