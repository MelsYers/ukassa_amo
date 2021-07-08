<?php

require "connect.php";
require "test.php";

$deal = execRestPost("leads", array(
    array(
        "name" => "Test",
        "price" => 20000
    )
));

print_r(json_decode($deal));


?>