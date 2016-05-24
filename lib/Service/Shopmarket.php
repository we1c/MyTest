<?php

class Service_Shopmarket extends Service {

    private $error;

    public function getError() {
        return $this->error;
    }

    public function getMarketList($page, $perpage, $keyword, $minPrice, $maxPrice, $searchType, $developPlan, $checkResult = '', $stop = '', $isCount = '') {
        $map = '';
        if ($keyword != '') {
            if ($searchType == 1) {
                $map .= " AND ( G.goodsNo LIKE '%{$keyword}%' OR G.code LIKE '%{$keyword}%' OR G.name LIKE '%{$keyword}%' ) ";
            } elseif ($searchType == 2) {
                $map .= " AND ( G.code LIKE '{$keyword}' ) ";
            } elseif ($searchType == 3) {
                $map .= " AND ( G.goodsNo LIKE '{$keyword}' ) ";
            }
        }
        if ($checkResult != '') {
            $map .= " AND checkResult = '" . $checkResult . "'";
        }
        if ($stop != '') {
            $map .= $stop;
        }
        if ($developPlan != '') {
            if ('1' == $developPlan) {
                $map .= " AND G.checkResult <> '0' AND G.status = '2' ";
            } elseif ('2' == $developPlan) {
                $map .= " AND G.checkResult <> '0' AND G.status = '3' ";
            } elseif ('3' == $developPlan) {
                $map .= " AND G.checkResult = '0' AND G.fromWhere = '2' AND GC.status = 0 ";
            } elseif ('4' == $developPlan) {
                $map .= " AND G.checkResult = '0' AND G.fromWhere = '2' AND GC.status = 1 ";
            } elseif ('5' == $developPlan) {
                $map .= " AND G.checkResult = '0' AND G.fromWhere = '2' AND GC.status = 2 ";
            } elseif ('6' == $developPlan) {
                $map .= " AND G.checkResult = '0' AND G.fromWhere = '1' AND SP.follow IS NULL AND SP.command_id IS NULL ";
            } elseif ('7' == $developPlan) {
                $map .= " AND G.checkResult = '0' AND G.fromWhere = '1' AND SP.follow IS NULL AND SP.command_id IS NOT NULL ";
            } elseif ('8' == $developPlan) {
                $map .= " AND G.checkResult = '0' AND G.fromWhere = '1' AND SP.follow = '1' ";
            } elseif ('9' == $developPlan) {
                $map .= " AND G.checkResult = '0' AND G.fromWhere = '1' AND SP.follow = '2' ";
            }
        }


        if ($minPrice && $maxPrice) {
            $map .= " AND ( G.purchPrice*S.ptimes ) BETWEEN '{$minPrice}' AND '{$maxPrice}' ";
        } elseif ($minPrice) {
            $map .= " AND ( G.purchPrice*S.ptimes ) > '{$minPrice}' ";
        } elseif ($maxPrice) {
            $map .= " AND ( G.purchPrice*S.ptimes ) < '{$maxPrice}' ";
        }
        //$isCount为1表示只获取数量
        if($isCount == 1){
            $sql = " SELECT count(*) FROM `goods` AS G LEFT JOIN `shop_push` AS SP ON G.id = SP.goods_id LEFT JOIN `shop` AS S ON G.shopId = S.id LEFT JOIN `goods_check` AS GC ON G.id = GC.goodsId WHERE G.platform = '1' AND G.status <> 5 {$map} ";
            return $this->db->fetchOne($sql);
        }
        $sql = "SELECT G.id,G.name,G.code,G.fromWhere,G.goodsNo,G.price,G.platfPrice,G.purchPrice,G.attribute,G.createTime,G.status,G.remark,G.uploader,G.intro,G.shopId,G.freight,G.delflg,G.category1,G.category2,G.category3,G.content,G.showPrice,G.platform,G.checkResult,G.goodsStock,G.editor,G.updatetime,G.taobao_special,SP.id AS sp_id,SP.command_id,SP.exe_id,SP.follow,S.ptimes,S.mtimes,GC.status AS check_status FROM `goods` AS G LEFT JOIN `shop_push` AS SP ON G.id = SP.goods_id LEFT JOIN `shop` AS S ON G.shopId = S.id LEFT JOIN `goods_check` AS GC ON G.id = GC.goodsId WHERE G.platform = '1' AND G.status <> 5 {$map} ORDER BY G.id DESC " . $this->db->buildLimit($page, $perpage);
        $info = $this->db->fetchAll($sql);

        foreach ($info as $k => $v) {
            $info[$k]['thumb'] = Service::getInstance('goods')->getGoodsOneImg($v['id']);
            $info[$k]['shopName'] = Service::getInstance('shop')->getShopNameById($v['shopId']);
            $cids = $this->getChannelByGoodsId($v['id']);
            $ids = array();
            if (!empty($cids)) {
                foreach ($cids as $kk => $cid) {
                    $ids[] = $cid['channel'];
                }
            }
            $info[$k]['cname'] = !empty($ids) ? $this->getChannelNameById($ids) : '';
            if ($v['checkResult']) {
                if ($v['status'] == 1) {
                    $status = '正常销售';
                } elseif ($v['status'] == 2) {
                    $status = '商品售罄';
                } elseif ($v['status'] == 3) {
                    $status = '暂停销售';
                } else {
                    $status = '其他';
                }
                if ($v['checkResult'] == 1) {
                    $action = 'p-0-sp-n';
                } elseif ($v['checkResult'] == 2) {
                    $action = 'p-1-sp-n';
                }
            } else {
                //if( $v['platform'] == 1 ){
                //if( !$v['sp_id'] ){
                if ($v['fromWhere'] == 2) {
                    if ($v['check_status'] != '') {
                        if ($v['check_status'] == 0) {
                            $status = '任务中心';
                        } elseif ($v['check_status'] == 1) {
                            $status = '审核中心';
                        } elseif ($v['check_status'] == 2) {
                            $status = '二审通过';
                        } else {
                            $status = '其他';
                        }
                    } else {
                        $status = '特殊';
                    }
                    $action = 'p-1-sp-y';
                } else {
                    if (!$v['follow']) {
                        if (!$v['command_id']) {
                            $status = '商家上传';
                        } else {
                            $status = '照片拍摄';
                        }
                    } else {
                        if ($v['follow'] == 1) {
                            $status = '文案丰富';
                        } elseif ($v['follow'] == 2) {
                            $status = '等待上架';
                        } else {
                            $status = '其他';
                        }
                    }
                    $action = 'p-1-sp-n';
                }
                /* }else{
                  //$status = '早期未审核进平台商家商品';
                  if( $v['status'] == 1 ){
                  $status = '正常销售';
                  }elseif($v['status'] == 2){
                  $status = '商品售罄';
                  }elseif($v['status'] == 3){
                  $status = '暂停销售';
                  }else{
                  $status = '其他';
                  }
                  $action = 'p-0-sp-n';
                  } */
                $shop = Service::getInstance('shop')->getShopinfo($v['shopId']);
                if ($v['fromWhere'] == 1) {
                    $info[$k]['uploader'] = $shop['name'] ? $shop['name'] : '';
                } else {
                    $name = Service::getInstance('developers')->getNameById($v['uploader']);
                    $info[$k]['uploader'] = $name ? $name : '';
                }
                if ($v['editor']) {
                    $editor = explode('-', $v['editor']);
                    if ($editor[0] == 's') {
                        $info[$k]['editor'] = Service::getInstance('shop')->getShopNameById($editor[1]);
                    } else {
                        $info[$k]['editor'] = Service::getInstance('developers')->getNameById($editor[1]);
                    }
                } else {
                    $info[$k]['editor'] = $info[$k]['uploader'];
                    $info[$k]['updateTime'] = $info[$k]['createTime'];
                }
            }
            $info[$k]['sp_status'] = $status;
            $info[$k]['action'] = $action;
        }
        $data['list'] = $info;

        $sql = " SELECT count(*) FROM `goods` AS G LEFT JOIN `shop_push` AS SP ON G.id = SP.goods_id LEFT JOIN `shop` AS S ON G.shopId = S.id LEFT JOIN `goods_check` AS GC ON G.id = GC.goodsId WHERE G.platform = '1' AND G.status <> 5 {$map} ";
        $data['total'] = $this->db->fetchOne($sql);

        return $data;
    }

    public function addShopPush($data) {
        return $this->db->insert('shop_push', $data);
    }

    public function delShopPush($id) {
        return $this->db->delete('shop_push', 'id = ' . $id);
    }

    public function addPush($data) {
        return $this->db->insert('push', $data);
    }

    public function getIsPushGoods($goodsId) {
        return $this->db->fetchOne(' SELECT count(*) FROM shop_push WHERE goods_id = ' . $goodsId);
    }

    public function getOnePush($goodsId) {
        return $this->db->fetchOne(" SELECT status FROM push WHERE goodsId = {$goodsId} ORDER BY status DESC LIMIT 1 ");
    }

    public function getChannelByGoodsId($goodsId) {
        return $this->db->fetchAll(' SELECT channel FROM push WHERE goodsId = ' . $goodsId);
    }

    public function getChannelNameById($cids) {
        $ids = implode(',', $cids);
        return $this->db->fetchAll(" SELECT name FROM channel WHERE id IN ( " . $ids . " ) ");
    }

    public function getExportGoods() {
        $sql = "SELECT G.id,G.name,G.code,G.goodsNo,G.price,G.platfPrice,G.purchPrice,G.attribute,G.createTime,G.updateTime,G.status,G.remark,G.uploader,G.recommend,G.intro,G.shopId,G.freight,G.delflg,G.category1,G.category2,G.category3,G.orderTime,G.content,G.showPrice,G.groups,G.platform,G.checkResult,G.goodsStock,G.fromWhere FROM `goods` AS G LEFT JOIN `goods_check` AS C ON G.id = C.goodsId WHERE G.status = 1 AND G.platform = 1 AND C.id IS NULL ";
        return $this->db->fetchAll($sql);
    }

}
