<?php
$url = (string) $_GET['url'];
$url = urldecode($url);
header('Location: '.$url);
