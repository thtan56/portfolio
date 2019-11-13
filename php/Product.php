<?php

class Product {
    private $msg = "";
    //---------------------------------------------   
    public $db;
    public function __construct() { 
        $dbObj = new DB();
        $this->db = $dbObj->getPDO(); 
    }
    //-------------------------------------
    public function getMsg() { return $this->msg; }
    
    public function getProducts() {
        $stmt = $this->db->prepare("select * from product");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!$products) {
            $this->msg = 'No rows'; 
            exit;
        };
        return $products;
    }
    public function insertProduct($json) { 
        $sql = "insert into product(name,price,created) values(?,?,now())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ $json->{'data'}->{'name'}, $json->{'data'}->{'price'} ]);
        if($stmt->errorCode() != 0) { $this->msg = $stmt->errorInfo(); };
    }
    public function updateProduct($json) {        // object, not array
        $sql = "update product set name=?,price=? where id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ 
            $json->{'data'}->{'name'}, 
            $json->{'data'}->{'price'},
            $json->{'data'}->{'id'},
             ]);
        if($stmt->errorCode() != 0) { $this->msg = $stmt->errorInfo(); };
    }
    public function deleteProduct($id) {
        $stmt = $this->db->prepare("delete from product where id=?");
        $stmt->execute([ $id ]);
        if($stmt->errorCode() != 0) { $this->msg = $stmt->errorInfo(); };
    }
}