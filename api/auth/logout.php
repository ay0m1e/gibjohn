<?php
session_start();
$_SESSION = [];
session_unset();
session_destroy();

header("Location: /gibjohn/public/index.html");
exit;
