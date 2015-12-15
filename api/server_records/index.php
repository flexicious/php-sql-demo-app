<?php
require_once $_SERVER['DOCUMENT_ROOT']."/service/SqlMemoryService.php";


$api_name = "";
if (isset($_GET["name"]))
    $api_name = $_GET["name"];

$service = new SqlMemoryService();
if ($api_name != "top_data" && $api_name != "child_data") {
    $response = new Response();
    $response->data = null;
    $response->success = false;
    $response->message = "Unknown Api Call";
    echo json_encode($response);
} else if ($api_name == "top_data") {
    echo json_encode($service->getTopLevelData());
} else if ($api_name == "child_data") {
    echo json_encode($service->getChildLevelData());
}
?>

