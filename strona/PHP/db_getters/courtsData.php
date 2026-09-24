<?php
    require("config.php");
    header('Content-Type: application/json; charset=utf-8');

    $sql = mysqli_query($config, "
        SELECT c.*, s.name AS surface_name 
        FROM courts c 
        LEFT JOIN surfaces s ON c.surface_id = s.id
    ");
    
    $data = [];
    
    while($res = mysqli_fetch_array($sql)){
        $data[] = [
            "id" => (int)$res["ID"],
            "name" => $res["name"],
            "surfaceId" => (int)$res["surface_id"],
            "surfaceLabel" => $res["surface_name"] ? $res["surface_name"] : "Brak",
            "isOutdoor" => (bool)$res["is_outdoor"],
            "address" => $res["address"],
            "receptionPhone" => $res["reception_phone"],
            "price" => (float)$res["price_per_hour"],
            "isActive" => (bool)$res["is_active"],
            "photo" => $res["photo"],
            "description" => $res["description"]
        ];
    }
    
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>