<?php
//
// LOCAL TABLE VARIABLES
//

$MYSQL_TABLE         = "cglbrd.PROVINCES";
$MYSQL_RECORDKEY     = "recordkey";
$MYSQL_DEFAULT_ORDER = "Province_Code";
$MYSQL_DEFAULT_LIMIT = 50;

require("$_SERVER[DOCUMENT_ROOT]/lib/authenticate.php");

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");
header ("Content-Type: text/html; charset=utf-8");

$success = false;
$reason  = '';
$whereClause = '';
$apiRecords = array();   
$returnString = array();

$cmd          = isset($_REQUEST['cmd']) ? $_REQUEST['cmd'] : 'read';
$sort         = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
$start        = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
$limit        = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : $MYSQL_DEFAULT_LIMIT;
$searchfield  = isset($_REQUEST['searchfield']) ? $_REQUEST['searchfield'] : '';
$searchvalue  = isset($_REQUEST['searchvalue']) ? $_REQUEST['searchvalue'] : '';
$recordkey    = isset($_REQUEST['recordkey']) ? $_REQUEST['recordkey'] : '';

if ($limit || $start) {
   $limitClause = "LIMIT $start, $limit";
}

if ($searchfield && $searchvalue) {
   $whereClause = "WHERE $searchfield LIKE '%$searchvalue%' OR $MYSQL_RECORDKEY = '$searchvalue'" ;
}

if ($recordkey) {
   $whereClause = "WHERE $MYSQL_RECORDKEY = '$recordkey'";
}

if ($sort) {
   $orderClause = "ORDER BY $sort $dir";
}
else {
   $orderClause = "ORDER BY $MYSQL_DEFAULT_ORDER";
}


switch ($cmd) {
      case 'create' :
//         $result = _mysql_do("INSERT INTO $MYSQL_TABLE 
//                                (`supplier_id`, `supplier_name`, `address_1`, `address_2`, `city`, `state`, `zipcode`, `contact_name`, `phone`, `fax`, `email`, `instructions`, 
//                                 `minimum_order`, `availability_min`, `availability_max`, `availability_unit`, `notes`, `disabled`, date_entered, last_updated)
//                                VALUES
//                                 ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', NOW(), NOW())", 
//                                 $supplierid, $suppliername, $address1, $address2, $city, $state, $zipcode, $contactname, $phone, $fax, $email, $instructions, $minimumorder, $availabilitymin,
//                                 $availabilitymax, $availabilityunit, $notes, $disabled);
//         if ($result == 1) {
//            $success = true;
//         }
         break;
      case 'read':
         $SQL = "SELECT SQL_CALC_FOUND_ROWS * FROM $MYSQL_TABLE $whereClause $orderClause $limitClause"; 
         $count = _mysql_get_records("$SQL", $result1);
         $rc = count($mysql_fields);
         for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j < $rc; $j++) {
                $record[$mysql_fields[$j]] = utf8_encode($result1[$i][$mysql_fields[$j]]);
            }
            array_push($apiRecords, $record);
         }
         if ($count == 0) {
            $reason = 'No records found';
         }
         $success = true;
         break;
      case 'update' :
         $SQL = "UPDATE $MYSQL_TABLE SET 
                 title = '%s',
                 comments = '%s',
                 rating = '%s'
                 WHERE $MYSQL_RECORDKEY = '$recordkey'";

         $result = _mysql_do($SQL, $_REQUEST['title'], $_REQUEST_['comments'],  $_REQUEST['rating']);

         if ($result > 0) {
            $success = true;
         }
         else {
            $reason = 'Record not found';
         }
         break;
      case 'delete' :
//         $result = _mysql_do("DELETE FROM $MYSQL_TABLE WHERE $MYSQL_RECORDKEY = '$recordkey'");
//         if ($result == 1) {
//            $success = true;
//         }
//         else {
//            $reason = 'This supplier is currently in use - you cannot delete this supplier.';
//         }
         break;
      default: 
         $reason = 'Unknown command';
}

$returnString['sqlinsertid'] = $mysql_insert_id;
$returnString['sqlerror']    = $mysql_errmsg;
$returnString['totalCount']  = $mysql_found_rows;

$returnString['success']     = $success;
$returnString['reason']      = $reason;
$returnString['apiRecords']  = $apiRecords;
$returnString['sql']         = $SQL;

print json_encode($returnString);

exit;

?>