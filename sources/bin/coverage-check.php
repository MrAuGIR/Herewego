<?php

/*
 * Vérifie que la couverture de lignes d'un rapport Clover atteint un seuil minimal.
 *
 * Usage : php bin/coverage-check.php <clover.xml> <seuil en %>
 */

if ($argc < 3) {
    fwrite(\STDERR, "Usage : php bin/coverage-check.php <clover.xml> <seuil>\n");
    exit(2);
}

[, $file, $threshold] = $argv;

if (!is_file($file)) {
    fwrite(\STDERR, sprintf("Rapport Clover introuvable : %s (lancer d'abord « composer test-coverage »)\n", $file));
    exit(2);
}

$xml = simplexml_load_file($file);
$metrics = false !== $xml ? $xml->project->metrics : null;
if (null === $metrics || 0 === $metrics->count()) {
    fwrite(\STDERR, "Rapport Clover invalide.\n");
    exit(2);
}

$statements = (int) $metrics['statements'];
$covered = (int) $metrics['coveredstatements'];
$coverage = 0 === $statements ? 100.0 : $covered / $statements * 100;

printf("Couverture des lignes : %.2f %% (%d/%d) — seuil : %s %%\n", $coverage, $covered, $statements, $threshold);

if ($coverage < (float) $threshold) {
    fwrite(\STDERR, "❌ Couverture insuffisante.\n");
    exit(1);
}

echo "✅ Seuil de couverture atteint.\n";
