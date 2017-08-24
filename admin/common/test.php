<?php
$fruits = array("coconut" => "", "lemon" => "400", "orange" => "0", "banana" => "900", "apple" => "3");
var_dump($fruits);
arsort($fruits);
//var_dump($fruits);
foreach ($fruits as $key => $val) {
    echo "$key = $val\n";
}
?>

