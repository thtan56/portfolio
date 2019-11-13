<?php
//require_once('configLog.php');
require_once('Product.php');

$data = file_get_contents('php://input');
$json = json_decode($data);

//$logger = getLogger();
$op = $json->{'op'};

if(isset($op)){

    switch($op){
        case "getProducts":
            $obj = new Product();
            $ret = $obj->getProducts();
            $count = count($ret,1);
            $msg = $obj->getMsg();

            if (!empty($msg)) { $resp = array('code' => -1, 'msg' => $msg);
            } else { $resp = array('code' => 1, 'msg' => '','data' => $ret);
            };
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST');
//            header('Access-Control-Allow-Headers: "Content-type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
            echo json_encode($resp);
            break;
        case "save":
            $id = $json->{'data'}->{'id'};

            $obj = new Product();
            $code = -1;
            if(empty($id) || $id=="") { 
                $code = $obj->insertProduct($json);
            } else { 
                $code = $obj->updateProduct($json);
            };
            $resp = array('code' => $code, 'msg' => $obj->getMsg());
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST');
            echo json_encode($resp);
            break;

        case "delete":
            $id = $json->{'id'};
            $obj = new Product();
            $code = $obj->deleteProduct($id);
            $resp = array('code' => $code, 'msg' => $obj->getMsg());
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST');
            echo json_encode($resp);
            break;

        default:
            $ret = -999;
            $resp = array('code' => $ret, 'msg' => 'invalid operation');
            echo json_encode($resp);
            break;
    }
}else{
    $ret = -999;
    $resp = array('code' => $ret, 'msg' => 'invalid operation');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($resp);

}