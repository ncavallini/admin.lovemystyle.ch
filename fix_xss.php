<?php
$file = 'pages/bookings/view.php';
$content = file_get_contents($file);

// Fix line 36
$content = str_replace(
    'value="<?php echo $_GET['"q"'] ?? '""' ?>"',
    'value="<?php echo htmlspecialchars($_GET['"q"'] ?? '""', ENT_QUOTES, '"UTF-8"'); ?>"',
    $content
);

// Fix line 39
$content = str_replace(
    'value="<?php echo $_GET['"page"'] ?>"',
    'value="<?php echo htmlspecialchars($_GET['"page"'] ?? '""', ENT_QUOTES, '"UTF-8"'); ?>"',
    $content
);

file_put_contents($file, $content);
echo "Fixed XSS in bookings/view.php\n";
?>
