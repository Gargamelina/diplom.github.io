<?php
session_start();
session_unset(); // Очистить все переменные сессии
session_destroy(); // Уничтожить сессию

header("Location: index.php");
exit();
?>
