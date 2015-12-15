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
 * Time: 2:59 PM
 */
class SqlMemoryService implements Service
{
    public function getTopLevelData()
    {
        $filterPageSort = null;
        if(isset($_GET["filterPageSort"])){
            $filterPageSort = json_decode((string)$_GET["filterPageSort"]);
        } else{
            $filterPageSort = new stdClass();
        }

        $query = "SELECT *, ";
        $query .= "((select count(child.id) from server_records as child where child.record_id=parent.id)) as childCounts ";
        $query .= "FROM " . DB::$tableName. " as parent";
        if (property_exists($filterPageSort, "filters")) {
            $query .= SqlUtil::buildFilterQuery($filterPageSort->filters);
            $query .= " AND record_id = 0 "; // fetch only parent in case
        } else {
            $query .= " WHERE record_id = 0 ";
        }
        if(property_exists($filterPageSort, "sorts")){
            $query .= SqlUtil::buildSortQuery($filterPageSort->sorts);
        }
        if(property_exists($filterPageSort, "pageSize")){
            $query .= " LIMIT ".($filterPageSort->pageIndex * $filterPageSort->pageSize).",".($filterPageSort->pageSize);
        }

        $conn = DB::getConnection();
        $result = $conn->query($query);

        $responseData = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $serverRecord = new ServerRecord();
                $serverRecord->copyFrom($row);
                array_push($responseData, $serverRecord);
            }
        }

        $response = new Response();
        $response->data = $responseData;
        $response->message = "Data Fetched Successfully..";
        $response->success = true;
        if(property_exists($filterPageSort, "pageSize")){
            $query = substr($query, 0, strpos($query, "LIMIT"));
            $result = $conn->query($query);
            $response->details = (object)array("pageSize" => $filterPageSort->pageSize, "pageIndex" => $filterPageSort->pageIndex, "totalRecords" => $result === FALSE ? 0 : $result->num_rows);
        }
        $conn->close();
        return $response;
    }

    public function getChildLevelData()
    {
        $filterPageSort = null;
        $parentId = $_GET["parent_id"];
        if(!isset($parentId))
            $parentId = FALSE;
        if(isset($_GET["filterPageSort"])){
            $filterPageSort = json_decode((string)$_GET["filterPageSort"]);
        } else{
            $filterPageSort = FALSE;
        }

        $response = new Response();
        if(!$parentId){
            $response->success = false;
            $response->data = null;
            $response->message = "For Child data parentId is must.";
            return $response;
        }

        $query = "SELECT * FROM ".DB::$tableName;
        if($filterPageSort != FALSE) {
            if (property_exists($filterPageSort, "filters")) {
                $query .= SqlUtil::buildFilterQuery($filterPageSort->filters);
                $query .= " AND record_id = " . $parentId;
            } else {
                $query .= " WHERE record_id = " . $parentId;
            }
            if(property_exists($filterPageSort, "sorts")){
                $query .= SqlUtil::buildSortQuery($filterPageSort->sorts);
            }
        } else {
            $query .= " WHERE record_id = ".$parentId;
        }

        $conn = DB::getConnection();
        $result = $conn->query($query);
        $responseData = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $serverRecord = new ServerRecord();
                $serverRecord->copyFrom($row);
                array_push($responseData, $serverRecord);
            }
        }

        $conn->close();

        $response->data = $responseData;
        $response->message = "Data Fetched Successfully..";
        $response->success = true;

        return $response;
    }

}