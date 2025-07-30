<?php
// Script pour trouver les références à unites_enseignement dans les fichiers PHP
echo "<h1>Recherche de références à 'unites_enseignement'</h1>";

// Récupérer tous les fichiers PHP du répertoire courant
$phpFiles = glob("*.php");

// Rechercher les références à unites_enseignement (singulier)
$searchTerm = "unites_enseignement";
$results = [];

foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    if (stripos($content, $searchTerm) !== false) {
        $lines = explode("\n", $content);
        $fileResults = [];
        
        foreach ($lines as $lineNumber => $line) {
            if (stripos($line, $searchTerm) !== false) {
                $fileResults[] = [
                    'line' => $lineNumber + 1,
                    'content' => trim($line)
                ];
            }
        }
        
        if (!empty($fileResults)) {
            $results[$file] = $fileResults;
        }
    }
}

// Afficher les résultats
if (empty($results)) {
    echo "<p style='color:green'>Aucune référence à '$searchTerm' trouvée dans les fichiers PHP.</p>";
} else {
    echo "<h2>Références à '$searchTerm' trouvées dans les fichiers suivants :</h2>";
    echo "<ul>";
    foreach ($results as $file => $fileResults) {
        echo "<li><strong>$file</strong> (" . count($fileResults) . " occurrences) :";
        echo "<ul>";
        foreach ($fileResults as $result) {
            echo "<li>Ligne " . $result['line'] . ": <code>" . htmlspecialchars($result['content']) . "</code></li>";
        }
        echo "</ul>";
        echo "</li>";
    }
    echo "</ul>";
}
?>
