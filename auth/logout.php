<?php
session_start();
session_destroy();
header("Location: ../cliente/landing.php");
exit();
