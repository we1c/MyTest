<?php

class Service_Developers extends Service
{
    public function getRegisterCodeByEmail($email, $name = '')
    {
        $row = $this->db->fetchRow('SELECT * FROM developers WHERE email=?', $email);
        if ($row) {
            return $row['code'];
        }

        $code = md5(uniqid() . '' . rand(111111, 999999));
        $salt = substr($code, 0, 6);
        $row = array('email' => $email, 'code' => $code, 'salt' => $salt);
        if ($name) {
            $row['name'] = $name;
        }

        $this->db->insert('developers', $row);

        return $code;
    }
    
    public function getRegisterInfoByEmail($email, $name = '')
    {
        $row = $this->db->fetchRow('SELECT * FROM developers WHERE email=?', $email);
        if ($row) {
            return $row['code'];
        }
    
        $code = md5(uniqid() . '' . rand(111111, 999999));
        $salt = substr($code, 0, 6);
        $row = array('email' => $email, 'code' => $code, 'salt' => $salt);
        if ($name) {
            $row['name'] = $name;
        }
    
        $this->db->insert('developers', $row);
    
        $row['lastId'] = $this->db->lastInsertId();
        return $row;
    }
    
    public function developerlist($page, $perpage,$keyword)
	{
	    $map = '';
	    if($keyword!='')
	    {
	        $map = " WHERE name like '%{$keyword}%'";
	    }
	    $sql = "SELECT * FROM developers $map ORDER BY id desc".$this->db->buildLimit($page, $perpage);
	   
	    $data['list'] = $this->db->fetchAll($sql);
	    $sql = "SELECT count(*) FROM developers $map";
	    $data['total'] = $this->db->fetchOne($sql);
	    return $data;
	}

    public function getDeveloperByRole( $roleId ){
        return $this->db->fetchAll('SELECT distinct A.id,A.name FROM developers AS A 
            LEFT JOIN user_role AS B ON A.id = B.user_id WHERE role_id = '.$roleId );
    }

    public function getDeveloperByEmail($email)
    {
        return $this->db->fetchRow('SELECT * FROM developers WHERE email = ?', $email);
    }

    public function getDisIdById( $id ){
        return $this->db->fetchOne('SELECT disId FROM developers WHERE id = '.$id );
    }

    public function getNameById( $id ){
        return $this->db->fetchOne('SELECT name FROM developers WHERE id = '.$id );
    }

    public function getDeveloperByCode($code)
    {
        return $this->db->fetchRow('SELECT * FROM developers WHERE code = ?', $code);
    }

//     public function updatePassword($id, $pwd)
//     {
//         $this->db->update('users', array('pwd' => $pwd), ' id=' . $id);
//     }
    public function updatePassword($id, $pwd)
    {
        $this->db->update('developers', array('pwd' => $pwd), 'id=' . $id);
    }

    public function updateDev ( $id,$data ){
        return $this->db->update('developers',$data,'id=' . $id);
    }

    public function getDeveloperNameList(){
        return $this->db->fetchAll(" SELECT id,name FROM developers ");
    }
}