<?php
session_start();

require_once '../../config/database.php';

if($_SESSION['user']['role'] === '1'){

}elseif($_SESSION['user']['role'] === '2') {

}elseif($_SESSION['user']['role'] === '3') {
    include('../view/secretariat.php');
}elseif($_SESSION['user']['role'] === '4'){

}

?>