<?php
$f = 'app/views/projetos/historico.php';
$c = file_get_contents($f);
$c = str_replace("\r\n", "\n", $c);
file_put_contents($f, $c);
echo "Normalized: $f\n";
