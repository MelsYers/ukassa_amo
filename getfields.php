<?php

require "connect.php";
require "test.php";

echo "<pre>";
print_r(json_decode(execRest("leads/custom_fields"),1));

?>