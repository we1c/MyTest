<?php

class Service_GoodsTemplet extends Service {

    //添加详情模板
    public function add($data) {
        $this->db->insert('goods_templet', $data);
        return $this->db->lastInsertId();
    }
    //通过类目id获取模板id
    public function getTempletIdByCatId($cat_id) {
        return $this->db->fetchAll('SELECT `id` FROM `goods_templet` WHERE `cat_id` = ' . $cat_id);
    }
    //编辑详情模板
    public function edit($data, $id) {
        return $this->db->update('goods_templet', $data, 'id = ' . $id);
    }
    //获取详情模板列表
    public function getTempletAll($page, $perpage, $keyword = '', $searchType = '') {
        $where = 'WHERE 1=1 ';
        if ($keyword != '') {
            if ($searchType == 1) {
                $where .= " AND (c.name like '%{$keyword}%') ";
            } elseif ($searchType == 2) {
                $where .= "  ";
            } elseif ($searchType == 3) {
                $where .= "  ";
            } elseif ($searchType == 4) {
                $where .= "  ";
            }
        }
        $data = $this->db->fetchAll(" SELECT c.*,d.createname,e.name as editname from goods_templet as c left join developers as e on c.editor = e.account left join (select a.id,b.name as createname from goods_templet as a left join developers as b on a.creator = b.account) as d on d.id = c.id {$where} group by c.`cat_id` order by c.id desc" . $this->db->buildLimit($page, $perpage));
        $all = $this->db->fetchAll("SELECT c.*,d.createname,e.name as editname from goods_templet as c left join developers as e on c.editor = e.account left join (select a.id,b.name as createname from goods_templet as a left join developers as b on a.creator = b.account) as d on d.id = c.id {$where} group by c.`cat_id` order by c.id desc");
        $data['total'] = count($all);
        return $data;
    }
    //通过类目id获取模板，在编辑时用到
    public function getTempletByCatId($cat_id) {
        $data = $this->db->fetchAll(' SELECT * FROM `goods_templet` WHERE `cat_id` = ' . $cat_id);
        $sort = array();
        foreach ($data as $k => $v) {
            $sort[$k] = $v['sort'];
        }
        asort($sort);
        $newdata = array();
        foreach ($sort as $k => $v) {
            $newdata[] = $data[$k];
        }
        return $newdata;
    }
    //通过id删除详情模板
    public function delTempletById($id) {
        return $this->db->delete('goods_templet', ' id =' . $id);
    }
    //通过类目id删除详情模板
    public function delTempletByCatId($cat_id) {
        return $this->db->delete('goods_templet', 'cat_id = ' . $cat_id);
    }
    //通过模板id获取模板图片
    public function getTempletImageByTempId($temp_id) {
        $data = $this->db->fetchAll(' SELECT * FROM goods_templet_image WHERE temp_id = ' . $temp_id);
        $sort = array();
        foreach ($data as $k => $v) {
            $sort[$k] = $v['sort'];
        }
        asort($sort);
        $newdata = array();
        foreach ($sort as $k => $v) {
            $newdata[] = $data[$k];
        }
        return $newdata;
    }
    //添加模板图片
    public function addTempletImages($sql) {
        return $this->db->query($sql);
    }
    //更新模板图片
    public function editTempletImages($data, $id) {
        return $this->db->update('goods_templet_image', $data, 'id = ' . $id);
    }
    //删除模板图片
    public function delTempletImages($sql) {
        return $this->db->query($sql);
    }
    //获取模板图片的名字
    public function getTempletImagesName($temp_id) {
        return $this->db->fetchAll('SELECT `image` FROM `goods_templet_image` WHERE `temp_id` = ' . $temp_id);
    }

}
