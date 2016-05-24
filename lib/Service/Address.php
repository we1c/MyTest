<?php

class Service_Address extends Service
{
    //添加收货地址
    public function add($address){
        $this->db->insert('address', $address);
        return $this->db->lastInsertId();
    }
    //删除地址
    public function del($id){
        return $this->db->delete('address', 'id='.$id);
    }
    //获取该会员的所有收货地址
    public function getAddressByCustomerId($customer_id){
        $res = $this->db->fetchAll('SELECT * FROM `address` WHERE `customer_id` =' . $customer_id);
        return $res;
    }
    //获取收货地址
    public function getAddressById($id){
        $res = $this->db->fetchRow('SELECT * FROM `address` WHERE `id` =' . $id);
        return $res;
    }
    //获取会员id
    public function getCustomerId($tel){
        $res = $this->db->fetchOne('SELECT id FROM `customer` WHERE `tel` =' . $tel);
        return $res;
    }
    //以下方法为处理存量数据用到
    public function getOrders(){
        $res = $this->db->fetchAll('select uname as name,tel from orders group by tel');
        return $res;
    }
    public function getOrdersAll(){
        $res = $this->db->fetchAll('select c.id as customer_id,o.uname as name,o.tel,o.province,o.city,o.area,o.address from orders as o left join customer as c on o.tel = c.tel where length(c.tel) = 11 ');
        return $res;
    }
    
    public function getCustomer(){
        $res = $this->db->fetchAll('select tel from customer');
        return $res;
    }
    public function addCustomer($data){
        $this->db->insert('customer', $data);
        return $this->db->lastInsertId();
    }
    public function addAddress($data){
        $this->db->insert('address', $data);
        return $this->db->lastInsertId();
    }
    public function getAddressAll(){
        $res = $this->db->fetchAll('SELECT customer_id,name,tel,province,city,area,address FROM `address`');
        return $res;
    }
    public function getDevelopers(){
        $res = $this->db->fetchAll('SELECT name,account as tel,openId as openid FROM `developers`');
        return $res;
    }
    public function getUsers(){
        $res = $this->db->fetchAll('SELECT name,account as tel FROM `users`');
        return $res;
    }
}

