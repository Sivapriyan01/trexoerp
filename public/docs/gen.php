<?php
/**
 * gen.php — Validates openapi.yaml is present and reports stats
 */
$base = __DIR__;
$out  = $base . '/openapi.yaml';

if (!file_exists($out)) {
    die("ERROR: openapi.yaml not found.\n");
}

$content = file_get_contents($out);
echo "✅ openapi.yaml ready.\n";
echo "   Lines: " . substr_count($content, "\n") . "\n";
echo "   Size : " . number_format(strlen($content) / 1024, 1) . " KB\n";
echo "   Open : http://localhost:8000/docs/index.html\n";
