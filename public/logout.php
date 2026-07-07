<?php
require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/helpers.php';

deconnecterLecteur();
header('Location: ' . BASE_URL . '/index.php');
exit;