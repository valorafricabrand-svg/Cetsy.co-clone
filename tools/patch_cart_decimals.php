<?php
$p = 'resources/views/theme/cetsy/partials/_cart.blade.php';
$s = file_get_contents($p);
$s = preg_replace('/data-decimals="[^"]*"/', 'data-decimals="{{ $__dec }}"', $s);
file_put_contents($p, $s);
?>
