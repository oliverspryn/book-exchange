<?php
require_once("../../../includes/APIs/IndexDen.php");
echo FFI\BE\IndexDen::search($_GET['q'], $_GET['by'], $_GET['in'], $_GET['sort'], $_GET['page'], $_GET['limit']);
?>