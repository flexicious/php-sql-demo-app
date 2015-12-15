<?php

require "Service.php";
require $_SERVER['DOCUMENT_ROOT']."/utils/SqlUtil.php";
require $_SERVER['DOCUMENT_ROOT']."/model/server_records.php";
require $_SERVER['DOCUMENT_ROOT']."/model/api_response.php";
require $_SERVER['DOCUMENT_ROOT']."/transaction/DB.php";

/**
 * Created by PhpStorm.
 * User: 19.06.2013-7pm
 * Date: 06-Nov-15
 * Time: 2:58 PM
 */
class PhpMemoryService implements Service
{
    /**
     * @return Response
     */
    public function getTopLevelData()
    {
        $conn = DB::getConnection();
        $sql = "SELECT * FROM ".DB::$tableName;
        $result = $conn->query($sql);

        $rawList = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $serverRecord = new ServerRecord();
                $serverRecord->copyFrom($row);
                array_push($rawList, $serverRecord);
            }
        }
        $conn->close();

        $topArray = $this->groupByTopLevel($rawList);

        if(!isset($_GET["filterPageSort"])){
            $response = new Response();
            $response->message = "Data Fetched";
            $response->success = true;
            $response->data = $topArray;
            return $response;
        }

        $filterPageSort = json_decode((string)$_GET["filterPageSort"]);
        $response = $this->doFilterPageSort($topArray, $filterPageSort);
        return $response;
    }

    public function getChildLevelData()
    {
        $parentId = null;
        if(isset($_GET["parent_id"]))
            $parentId = $_GET["parent_id"];
        if(!isset($parentId)){
            $response = new Response();
            $response->success = false;
            $response->data = null;
            $response->message = "Parent ID is invalid for fetching child..";
            return ($response);
        }

        $conn = DB::getConnection();
        $sql = "SELECT * FROM server_records WHERE record_id = ".$parentId;
        $result = $conn->query($sql);

        $rawList = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $serverRecord = new ServerRecord();
                $serverRecord->copyFrom($row);
                array_push($rawList, $serverRecord);
            }
        }
        $conn->close();

        if(!isset($_GET["filterPageSort"])){
            $response = new Response();
            $response->message = "Data Fetched";
            $response->success = true;
            $response->data = $rawList;
            return $response;
        }

        $filterPageSort = json_decode($_GET["filterPageSort"]);
        $response = $this->doFilterPageSort($rawList, $filterPageSort);
        return $response;
    }



    function doFilterPageSort($array, $filterPageSort){
        $utils = new Utils();
        $resultArray = $array;
        if(property_exists($filterPageSort, "filters"))
            $resultArray = $utils->filterArray($array,$filterPageSort->filters);

        if(property_exists($filterPageSort, "sorts")) {
            $utils->sortArray($resultArray, $filterPageSort->sorts);
        }

        $response = new Response();
        $totalRecords = count($resultArray);
        if(property_exists($filterPageSort, "pageSize") && $filterPageSort->pageSize != -1){
            if($totalRecords <= $filterPageSort->pageSize){
                $response->details = (object)array("pageSize" => $filterPageSort->pageSize, "pageIndex" => 1, "totalRecords" => $totalRecords);
            } else {
                $startIndex = $filterPageSort->pageSize * ($filterPageSort->pageIndex);
                $resultArray = array_slice($resultArray, $startIndex, $startIndex+$filterPageSort->pageSize < $totalRecords ? $filterPageSort->pageSize : $totalRecords-$startIndex );
                $response->details = (object)array("pageSize" => $filterPageSort->pageSize, "pageIndex" => $filterPageSort->pageIndex, "totalRecords" => $totalRecords);
            }
        }
        $response->data=$resultArray;
        $response->success=true;
        $response->message="Data Fetched Successfully";
        return $response;
    }

    function groupByTopLevel($array){
        $newArray = array();
        $field = "record_id";

        foreach($array as $item){
            if((int)$item->$field == 0)
                array_push($newArray, $item);
        }

        foreach($array as $item){
            foreach($newArray as $parent){
                if($item->$field == $parent->id) {
                    $parent->childCounts++;
                    break;
                }
            }
        }
        return $newArray;
    }

}