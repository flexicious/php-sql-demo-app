<?php

require $_SERVER['DOCUMENT_ROOT']."/constants/Constants.php";

/**
 * Created by PhpStorm.
 * User: 19.06.2013-7pm
 * Date: 06-Nov-15
 * Time: 3:11 PM
 */
class SqlUtil
{
    public static function buildFilterQuery($filters)
    {
        if (count($filters) == 0)
            return "";
        $filterQuery = " WHERE ";
        $i = 0;
        foreach ($filters as $filter) {
            if ($i != 0) {
                $filterQuery .= (" AND ");
            }
            $i++;
            $filterQuery .= ($filter->columnName);

            if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_BEGINS_WITH)) {
                $filterQuery .= (" LIKE '" . $filter->expression . "%' ");
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_CONTAINS)) {
                $filterQuery .= (" LIKE '%" . $filter->expression . "%' ");
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_ENDS_WITH)) {
                $filterQuery .= (" LIKE '%" . $filter->expression . "' ");
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_EQUALS)) {
                $filterQuery .= (" LIKE '%" . $filter->expression . "' ");
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_IS_NOT_NULL)) {
                $filterQuery .= (" is not null ");
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_IS_NULL)) {
                $filterQuery .= (" is null ");
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_NOT_EQUALS)) {
                $filterQuery .= " <> '" . $filter->expression . "' ";
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_GREATER_THAN)) {
                $filterQuery .= " > " . $filter->expression . " ";
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_GREATER_THAN_EQUALS)) {
                $filterQuery .= " >= " . $filter->expression . " ";
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_LESS_THAN)) {
                $filterQuery .= " < " . $filter->expression . " ";
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_LESS_THAN_EQUALS)) {
                $filterQuery .= " <= " . $filter->expression . " ";
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_IN_LIST)
                || $filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_NOT_IN_LIST)
            ) {
                $inList = $filter->expression;
                if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_NOT_IN_LIST)) {
                    $filterQuery .= (" NOT ");
                }
                $filterQuery .= (" IN (");
                $count = count($inList);
                for ($k = 0; $k < $count; $k++) {
                    $filterQuery .= $inList[$k] . (($k != ($count - 1)) ? ", " : " ");
                }
                $filterQuery .= (" ) ");
            } else if ($filter->filterOperation == (Constants::$FILTER_OPERATION_TYPE_BETWEEN)) {
                $betweenList = $filter->expression;
                if (count($betweenList) != 2)
                    continue;
                $filterQuery .= (" BETWEEN ");
                if($filter->filterComparisonType == "date")
                    $filterQuery .= ("'".$betweenList[0]."'" . " AND " . "'".$betweenList[1]."'");
                else
                    $filterQuery .= ($betweenList[0] . " AND " . $betweenList[1]);
            }
        }
        return $filterQuery;
    }

    public static function buildSortQuery($sorts){
        $sortQuery = " ORDER BY ";
        $i = 0;
        foreach($sorts as $sort){
            $sortQuery .= $sort->sortColumn." ";
            $sortQuery .= $sort->isAscending ? "ASC" :  "DESC";
            $sortQuery .= $i == count($sorts) - 1 ? " " : ", ";
            $i++;
        }
        return $sortQuery;
    }
}