<?php
session_start();

// uitloggen
session_destroy();

header("location: index.php");