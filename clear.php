<?php
// Run this file ONCE in your browser: http://127.0.0.1:8000/clear.php
// Or via terminal: php clear.php
// Then DELETE this file after.

$commands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan view:clear',
    'php artisan route:clear',
];

foreach ($commands as $cmd) {
    $output = shell_exec($cmd . ' 2>&1');
    echo "<b>$cmd</b><br><pre>$output</pre><hr>";
}

echo "<h3>✅ Done! Delete this file now.</h3>";
