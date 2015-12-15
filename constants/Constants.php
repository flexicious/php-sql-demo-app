<?php


/**
 * Created by PhpStorm.
 * User: 19.06.2013-7pm
 * Date: 06-Nov-15
 * Time: 3:23 PM
 */
class Constants
{
    public static $FILTER_OPERATION_TYPE_NONE = "None";
    public static $FILTER_OPERATION_TYPE_EQUALS = "Equals";
    public static $FILTER_OPERATION_TYPE_NOT_EQUALS = "NotEquals";
    public static $FILTER_OPERATION_TYPE_BEGINS_WITH = "BeginsWith";
    public static $FILTER_OPERATION_TYPE_ENDS_WITH = "EndsWith";
    public static $FILTER_OPERATION_TYPE_CONTAINS = "Contains";
    public static $FILTER_OPERATION_TYPE_DOES_NOT_CONTAIN = "DoesNotContain";
    public static $FILTER_OPERATION_TYPE_GREATER_THAN = "GreaterThan";
    public static $FILTER_OPERATION_TYPE_LESS_THAN = "LessThan";
    public static $FILTER_OPERATION_TYPE_GREATER_THAN_EQUALS = "GreaterThanEquals";
    public static $FILTER_OPERATION_TYPE_LESS_THAN_EQUALS = "LessThanEquals";
    public static $FILTER_OPERATION_TYPE_IN_LIST = "InList";
    public static $FILTER_OPERATION_TYPE_NOT_IN_LIST = "NotInList";
    public static $FILTER_OPERATION_TYPE_BETWEEN = "Between";

    // need to implement
    public static $FILTER_OPERATION_TYPE_IS_NOT_NULL = "IsNotNull";
    public static $FILTER_OPERATION_TYPE_IS_NULL = "IsNull";


    public static $FILTER_COMPARISION_TYPE_AUTO = "auto";
    public static $FILTER_COMPARISION_TYPE_STRING = "string";
    public static $FILTER_COMPARISION_TYPE_NUMBER = "number";
    public static $FILTER_COMPARISION_TYPE_DATE = "date";
    public static $FILTER_COMPARISION_TYPE_BOOLEAN = "boolean";

}