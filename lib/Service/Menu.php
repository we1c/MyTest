<?php

class Service_Menu extends Service {
  public function getMenu() {
       $sql = 'SELECT * FROM menu';
       $data = $this->db->fetchAll($sql);
       return $data;
   }
   
   public function adminMenu($roleId,$main = 0) {
    
      $sql = "SELECT menus FROM role where id = ".$roleId;
      $permission = $this->db->fetchOne($sql);
      
      $map = " WHERE id IN ( $permission ) ";
      if ($main == 1) {
        $map .= " AND pid = 0 ";
      }
      $sql = " SELECT * FROM menu {$map} ";
      $data = $this->db->fetchAll( $sql );
      $sort = array();
      foreach( $data as $k => $v ){
          $sort[$k] = $v['sort']; 
      }

      asort($sort);

      $menus = array();
      foreach( $sort as $k => $v ){
        $menus[] = $data[$k];
      }

      return $menus;
   }
  
}