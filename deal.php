<?php

require "connect.php";
require "test.php";

function getString($object, $valuelead){
    $items = $object["transaction_items"];
    $payment = $object["transaction_payments"];
    $nds = $object["total_nds"];
    $discount = $object["discount_total"];
    $nds_percent = $object["nds_percent"];
    
    $stringitems = "";
    foreach ($items as $key => $value) {
        $stringitems .= "Название товара: ".$value["name"]."\n";
        $stringitems .= "Цена: ".$value["price"]."\n";
        $stringitems .= "Количество: ".$value["quantity"]."\n";
        $stringitems .= "НДС: ".$value["nds_percent"]."\n";
        $stringitems .= "Стоимость: ".$value["total"]."\n";
    }
    $stringitems .= "\n";
    foreach ($payment as $key => $value) {
        $stringitems .= "Тип оплаты: ".$value["method_name"]."\n";
        $stringitems .= "Оплачено: ".$value["amount"]."\n";
    }
    $stringitems .= "НДС: ".$nds."\n";
    $stringitems .= "Сумма скидки: ".$discount."\n";
    $stringitems .= "Процент НДС: ".$nds_percent."\n";

    $object = array(
        "entity_id" => $valuelead["id"],
        "note_type" => "common",
        "params"=> array(
            "text" => $stringitems
        )
    );
    return $object;
}

/*
$deal = execRestPost("leads/3722267/notes", array(
    array(
        "entity_id" => ""
        "note_type" => "common",
        "params"=> array(
            "text" => "Hello \nwordl"
        )
    )
));
*/

$listToDeal = array();

foreach ($tranzactions as $key => $value) {
    $price = 0;
    foreach ($value["transaction_items"] as $keyitem => $valueitem) {
        $price+=$valueitem["total"];
    }
    
    $listToDeal[] = array(
        "name" => "Tranzaction UKASSA ".$value["id"],
        "price" => $price,
        "custom_fields_values" => array(
            array(
                "field_id" => 762723,
                "values" => array(
                    array(
                        "value" => strval($value["id"])
                    )
                )
            )
        )
    );
}

$deal = json_decode(execRestPost("leads", array_reverse($listToDeal)),1)["_embedded"]["leads"];

$leads=array();
foreach ($deal as $key => $value) {
    $leads[] = json_decode(execRest("leads/".$value["id"]),1);
}

$listToNotes = array();
foreach ($leads as $keylead => $valuelead) {
    foreach ($valuelead["custom_fields_values"] as $keycustom => $valuecustom) {
        if($valuecustom["field_id"] == 762723){
            foreach ($tranzactions as $keytranz => $valuetranz) {
                if($valuetranz["id"] == $valuecustom["values"][0]["value"]){
                    $listToNotes[] = getString($valuetranz, $valuelead);
                }
            }
        }
    }
}

$notes = json_decode(execRestPost("leads/notes", $listToNotes),1);

echo "<pre>";
print_r($notes);



?>