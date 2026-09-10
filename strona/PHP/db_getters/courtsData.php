<?php
    require("config.php");
    header('Content-Type: application/json');
    $sql = mysqli_query($config, "SELECT * FROM courts");
    $data;
    while($res = mysqli_fetch_array($sql)){
        $data[] = [
            "id" => (int)$res["ID"],
            "name" => 'Kort '.$res["ID"],
            "surface" => $res["surface_type"],
            "surfaceLabel" => "ddd",
            "price" => 60
        ];
    }
    echo json_encode($data);
?>