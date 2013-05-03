<?php
require_once("../../../includes/display/Book_Overview.php");
echo FFI\BE\Book_Overview::getBookByISBN($_GET['ISBN']);
?>