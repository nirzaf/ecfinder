<?php
setlocale( LC_CTYPE, 'pl_PL' );

/*
* Start page
*/
$config['start_page'] = "6";
$config['rules_page'] = "4";
$config['page_search'] = "17";
$config['basket_page'] = "15";
$config['order_page'] = "16";
$config['order_print'] = "18";

$config['products_list'] = "6";

$config['currency_symbol'] = "zł";
$config['orders_email'] = "";

/*
* Your website's title, description
*/
$config['logo'] = "Quick<span>.</span><strong>Cart</strong>";
$config['title'] = "Quick.Cart - szybki i prosty sklep internetowy";
$config['description'] = "Szybki i prosty sklep internetowy. Skrypt napisany w języku PHP, oparty o plikową bazę danych i zgodny ze standardami XHTML 1.1 i WAI.";
$config['slogan'] = "Szybki i prosty sklep internetowy";
$config['foot_info'] = "Wszelkie prawa zastrzeżone";

// Define all page ids where page tree for product list should be displayed
$aDisplayPagesTreeInProductsList = Array( $config['page_search']=>true );
?>