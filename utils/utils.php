<?php
require $_SERVER['DOCUMENT_ROOT']."/constants/Constants.php";
/**
 * possible filter operations.
 *   FILTER_OPERATION_TYPE_NONE = "None";
 *   FILTER_OPERATION_TYPE_EQUALS = "Equals";
 *   FILTER_OPERATION_TYPE_NOT_EQUALS = "NotEquals";
 *   FILTER_OPERATION_TYPE_BEGINS_WITH = "BeginsWith";
 *   FILTER_OPERATION_TYPE_ENDS_WITH = "EndsWith";
 *   FILTER_OPERATION_TYPE_CONTAINS="Contains";
 *   FILTER_OPERATION_TYPE_DOES_NOT_CONTAIN = "DoesNotContain";
 *   FILTER_OPERATION_TYPE_GREATER_THAN = "GreaterThan";
 *   FILTER_OPERATION_TYPE_LESS_THAN = "LessThan";
 *   FILTER_OPERATION_TYPE_GREATER_THAN_EQUALS = "GreaterThanEquals";
 *   FILTER_OPERATION_TYPE_LESS_THAN_EQUALS = "LessThanEquals";
 *   FILTER_OPERATION_TYPE_IN_LIST = "InList";
 *   FILTER_OPERATION_TYPE_NOT_IN_LIST = "NotInList";
 *   FILTER_OPERATION_TYPE_BETWEEN = "Between";
 *   FILTER_OPERATION_TYPE_IS_NOT_NULL = "IsNotNull";
 *   FILTER_OPERATION_TYPE_IS_NULL = "IsNull";
 *
 */
class Utils
{
    /**
     * @param $array
     * @param $filters
     *
     * filters = [{
     *      expression = {} || [],
     *      filterOperation = "",
     *      columnName = "",
     *      filterComparisonType = ""
     * }, ....]
     * @return array
     */
    function filterArray($array, $filters)
    {
        $result = array();
        foreach ($array as $item) {
            if ($this->isMatch($item, $filters))
                array_push($result, $item);
        }
        return $result;
    }


    /**
     * @param $array
     * @param $sortList
     *
     * sorts = [
     *   {
     *       sortColumn  = string,
     *       isAscending = boolean,
     *       sortNumeric = boolean
     *   } , .....
     * ]
     */
    function sortArray(&$array, $sortList)
    {
        $this->sorts = $sortList;
        usort($array, array($this, "compare"));
    }

    private $sorts; // store the sort list to re use in the callback
    public function compare($aObj, $bObj)
    {
         $toReturn = -1;
        foreach ($this->sorts as $sort){
            $columnName = $sort->sortColumn;
            $aObjValue = $sort->isAscending ? $aObj->$columnName : $bObj->$columnName;
            $bObjValue = $sort->isAscending ? $bObj->$columnName : $aObj->$columnName;
            if($sort->sortNumeric) {
                if (strpos($aObjValue, ".") !== false || strpos($bObjValue, ".") !== -false)
                    $toReturn = (float)$aObjValue < (float)($bObjValue) - 1 ? -1 : (float)$aObjValue == (float)$bObjValue ? 0 : 1;
                else
                    $toReturn = (int)$aObjValue < (int)($bObjValue) - 1 ? -1 : (int)$aObjValue == (int)$bObjValue ? 0 : 1;
            } else {
                $toReturn = strcmp((string)$aObjValue,(string)$bObjValue);
            }
            if($toReturn !== 0)
                return $toReturn;
        }
        return $toReturn;
    }


    function isMatch($item, $filterList){
        $matched = true;
        foreach($filterList as $filter){
            $columnName = $filter->columnName;
            $expression = $this->convert($item->$columnName, $filter->filterComparisonType);
            switch($filter->filterOperation){
                case Constants::$FILTER_OPERATION_TYPE_NONE:
                    if($filter->expression == null || $item == null || $filter->columnName == null)
                        $matched = true;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_EQUALS:
                    $matched = $expression == $filter->expression;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_NOT_EQUALS:
                    $matched = $expression != $filter->expressions;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_BEGINS_WITH:
                    $pos = strpos(strtolower((string)$expression), strtolower($filter->expression));
                    $matched = is_int($pos) && $pos == 0;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_ENDS_WITH:
                    $posA = strrpos(strtolower((string)$expression),strtolower($filter->expression));
                    $posB = strlen((string)$expression)-strlen((string)$filter->expression);
                    $matched = is_int($posA) && is_int($posB) && $posA == $posB;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_CONTAINS:
                    $pos = strpos(strtolower((string)$expression),strtolower($filter->expression));
                    $matched =  is_int($pos) && $pos >= 0;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_DOES_NOT_CONTAIN:
                    $pos = strpos(strtolower((string)$expression),strtolower($filter->expression));
                    $matched = is_int($pos) && $pos <= 0;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_GREATER_THAN:
                    $matched = $expression > $filter->expression;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_GREATER_THAN_EQUALS:
                    $matched = $expression >= $filter->expression;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_LESS_THAN:
                    $matched = $expression < $filter->expression;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_LESS_THAN_EQUALS:
                    $matched = $expression <= $filter->expression;
                    break;
                case Constants::$FILTER_OPERATION_TYPE_IN_LIST:
                    $matched = false;
                    foreach($filter->expression as $val) {
                        if (is_int(strpos(strtolower((string)$expression), strtolower($val))) && strpos(strtolower((string)$expression), strtolower($val)) == 0) {
                            $matched = true;
                            break;
                        }
                    }
                    break;
                case Constants::$FILTER_OPERATION_TYPE_NOT_IN_LIST:
                    $matched = false;
                    foreach($filter->expression as $val) {
                        if (is_int(strpos(strtolower((string)$expression), strtolower($val))) && strpos(strtolower((string)$expression), strtolower($val)) != 0) {
                            $matched = true;
                            break;
                        }
                    }
                    break;
                case Constants::$FILTER_OPERATION_TYPE_BETWEEN:
                    if(count($filter->expression) != 2) {
                        $matched = false;
                        break;
                    }
                    if($filter->filterComparisonType == "date"){
                        $date1 = new DateTime($filter->expression[0]);
                        $date2 = new DateTime($filter->expression[1]);
                        $date = new DateTime($expression);
                        $matched = $date1 <= $date && $date2 >= $date;
                    } else {
                        $matched = $filter->expression[0] <= $expression && $filter->expression[1] >= $expression;
                    }
                    break;
                default:
                    $matched =true;
            }
            if(!$matched)
                return false;
        }
        return $matched;
    }

    function convert($item, $filterComparisonType){
        if($item === null)
            return $item;
        if($filterComparisonType == Constants::$FILTER_COMPARISION_TYPE_NUMBER && !(is_numeric($item))) {
            return (float)($item);
        }elseif($filterComparisonType == Constants::$FILTER_COMPARISION_TYPE_DATE){
            //$str = (string)$item;
            ///return strlen($str) > 0 ? date_parse($item) : null;
            return $item;
        }else if($filterComparisonType == Constants::$FILTER_COMPARISION_TYPE_BOOLEAN && !(is_bool($item))){
            return (boolean)($item);
        }else if($filterComparisonType == Constants::$FILTER_COMPARISION_TYPE_STRING && !(is_string($item))){
            return (string)$item;
        }
		return $item;
    }
}
?>