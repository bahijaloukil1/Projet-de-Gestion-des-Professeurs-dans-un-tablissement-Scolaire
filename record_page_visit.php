<?php
/**
 * Système d'enregistrement et de suivi des visites de pages
 * Ce fichier contient les fonctions pour enregistrer et récupérer les statistiques de visite
 */

// Connexion à la base de données
function getDbConnection() {
    require_once 'config.php';
    
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
        return $pdo;
    } catch (PDOException $e) {
        error_log("Erreur de connexion à la base de données: " . $e->getMessage());
        return null;
    }
}

/**
 * Vérifie si la table page_visits existe, sinon la crée
 */
function ensurePageVisitsTableExists() {
    $pdo = getDbConnection();
    if (!$pdo) return false;
    
    try {
        // Vérifier si la table existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'page_visits'");
        if ($stmt->rowCount() === 0) {
            // Créer la table si elle n'existe pas
            $pdo->exec("
                CREATE TABLE page_visits (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    page_name VARCHAR(255) NOT NULL,
                    user_id INT,
                    user_type VARCHAR(50),
                    visit_date DATE NOT NULL,
                    visit_time TIME NOT NULL,
                    visit_count INT DEFAULT 1,
                    INDEX (page_name),
                    INDEX (user_type),
                    INDEX (visit_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            return true;
        }
        return true;
    } catch (PDOException $e) {
        error_log("Erreur lors de la création de la table page_visits: " . $e->getMessage());
        return false;
    }
}

/**
 * Enregistre une visite de page
 * 
 * @param string $pageName Nom de la page visitée
 * @param string $userType Type d'utilisateur (admin, coordinateur, etc.)
 * @return bool Succès ou échec de l'opération
 */
function recordPageVisit($pageName, $userType = null) {
    if (!ensurePageVisitsTableExists()) return false;
    
    $pdo = getDbConnection();
    if (!$pdo) return false;
    
    try {
        // Récupérer l'ID utilisateur de la session si disponible
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        
        // Si le type d'utilisateur n'est pas spécifié, essayer de le récupérer de la session
        if ($userType === null && isset($_SESSION['user_type'])) {
            $userType = $_SESSION['user_type'];
        } elseif ($userType === null && isset($_SESSION['role'])) {
            $userType = $_SESSION['role'];
        }
        
        $today = date('Y-m-d');
        $now = date('H:i:s');
        
        // Vérifier si une entrée existe déjà pour cette page, cet utilisateur et cette date
        $stmt = $pdo->prepare("
            SELECT id, visit_count 
            FROM page_visits 
            WHERE page_name = ? 
            AND (user_id = ? OR (user_id IS NULL AND ? IS NULL))
            AND user_type = ? 
            AND visit_date = ?
        ");
        $stmt->execute([$pageName, $userId, $userId, $userType, $today]);
        $existingVisit = $stmt->fetch();
        
        if ($existingVisit) {
            // Mettre à jour le compteur de visites existant
            $stmt = $pdo->prepare("
                UPDATE page_visits 
                SET visit_count = visit_count + 1, 
                    visit_time = ? 
                WHERE id = ?
            ");
            return $stmt->execute([$now, $existingVisit['id']]);
        } else {
            // Créer une nouvelle entrée
            $stmt = $pdo->prepare("
                INSERT INTO page_visits 
                (page_name, user_id, user_type, visit_date, visit_time) 
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$pageName, $userId, $userType, $today, $now]);
        }
    } catch (PDOException $e) {
        error_log("Erreur lors de l'enregistrement de la visite: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les statistiques de visite pour une page
 * 
 * @param string $pageName Nom de la page
 * @param int $days Nombre de jours à considérer (par défaut 30)
 * @param string $userType Type d'utilisateur à filtrer (optionnel)
 * @return array Statistiques de visite
 */
function getPageVisitStats($pageName, $days = 30, $userType = null) {
    $pdo = getDbConnection();
    if (!$pdo) return [];
    
    try {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        $sql = "
            SELECT 
                visit_date, 
                SUM(visit_count) as total_visits 
            FROM page_visits 
            WHERE page_name = ? 
            AND visit_date >= ?
        ";
        $params = [$pageName, $startDate];
        
        if ($userType !== null) {
            $sql .= " AND user_type = ?";
            $params[] = $userType;
        }
        
        $sql .= " GROUP BY visit_date ORDER BY visit_date";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupère les statistiques de visite par mois
 * 
 * @param string $pageName Nom de la page
 * @param int $year Année (par défaut année courante)
 * @param string $userType Type d'utilisateur à filtrer (optionnel)
 * @return array Statistiques mensuelles
 */
function getMonthlyVisitStats($pageName, $year = null, $userType = null) {
    if ($year === null) {
        $year = date('Y');
    }
    
    $pdo = getDbConnection();
    if (!$pdo) return [];
    
    try {
        $sql = "
            SELECT 
                MONTH(visit_date) as month, 
                SUM(visit_count) as total_visits 
            FROM page_visits 
            WHERE page_name = ? 
            AND YEAR(visit_date) = ?
        ";
        $params = [$pageName, $year];
        
        if ($userType !== null) {
            $sql .= " AND user_type = ?";
            $params[] = $userType;
        }
        
        $sql .= " GROUP BY MONTH(visit_date) ORDER BY MONTH(visit_date)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        // Initialiser un tableau avec tous les mois à 0
        $monthlyStats = array_fill(1, 12, 0);
        
        // Remplir avec les données réelles
        foreach ($stmt->fetchAll() as $row) {
            $monthlyStats[(int)$row['month']] = (int)$row['total_visits'];
        }
        
        return $monthlyStats;
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération des statistiques mensuelles: " . $e->getMessage());
        return array_fill(1, 12, 0);
    }
}
