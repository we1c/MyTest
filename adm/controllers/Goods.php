<?php

class GoodsController extends BaseController {

    public function init() {
        parent::init();
    }

    //商品列表
    public function indexAction() {
        $showType = trim($this->getQuery('showType', 'list'));
        $perpage = $this->getQuery('perpage', 100);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $keyword = trim($this->getQuery('keyword', ''));
        $status = $this->getQuery('status', '');
        $minPrice = $this->getQuery('minPrice', '');
        $maxPrice = $this->getQuery('maxPrice', '');
        $checkResult = $this->getQuery('checkResult', '');
        $searchType = $this->getQuery('searchType', '1');
        $showList = $this->getQuery('showList', 'platf');
        $developPlan = 6;//商家上传的参数 
        $stop = " AND G.status <> 2 AND G.status <> 3 ";//停止销售的参数
        $isCount = 1;//$isCount为1表示只获取数量
        $allTotal = Service::getInstance('goods')->goodsAdmList($page, $perpage, $keyword, '', $searchType, '', $checkResult, $minPrice, $maxPrice,$isCount);
        $recommendTotal = Service::getInstance('goods')->goodsAdmList($page, $perpage, $keyword, '', $searchType, 1, $checkResult, $minPrice, $maxPrice,$isCount);
        $platfTotal = Service::getInstance('goods')->goodsPlatList($page, $perpage, $keyword, 1, $minPrice, $maxPrice, $checkResult, $searchType,$isCount);
        $uploadTotal = Service::getInstance('Shopmarket')->getMarketList($page, $perpage, $keyword, $minPrice, $maxPrice, $searchType, $developPlan, $checkResult, $stop,$isCount);
        $stopTotal = Service::getInstance('goods')->goodsAdmList($page, $perpage, $keyword, array(2, 3), $searchType, '', $checkResult, $minPrice, $maxPrice,$isCount);
        if ($showList == 'recommend') {//推荐商品
            $data = Service::getInstance('goods')->goodsAdmList($page, $perpage, $keyword, '', $searchType, 1, $checkResult, $minPrice, $maxPrice);
            $active = 'recommend';
        } elseif ($showList == 'platf') {//正常销售
            $data = Service::getInstance('goods')->goodsPlatList($page, $perpage, $keyword, 1, $minPrice, $maxPrice, $checkResult, $searchType);
            $active = 'platf';
        } elseif ($showList == 'upload') {//商家上传
            $data = Service::getInstance('Shopmarket')->getMarketList($page, $perpage, $keyword, $minPrice, $maxPrice, $searchType, $developPlan, $checkResult, $stop);
            $active = 'upload';
        } elseif ($showList == 'stop') {//停止销售
            $data = Service::getInstance('goods')->goodsAdmList($page, $perpage, $keyword, array(2, 3), $searchType, '', $checkResult, $minPrice, $maxPrice);
            $active = 'stop';
        } elseif ($showList == 'all') {//全部
            $data = Service::getInstance('goods')->goodsAdmList($page, $perpage, $keyword, '', $searchType, '', $checkResult, $minPrice, $maxPrice);
            $active = 'all';
        }        
        $myRole = $this->_developer['role'];
        $myDis = $this->_developer['disId'];
        $dis = Service::getInstance('distributor')->getMyDis($myDis, $myRole);
        $this->_view->list = $data['list'];
        $this->_view->allTotal = $allTotal;
        $this->_view->recommendTotal = $recommendTotal;
        $this->_view->platfTotal = $platfTotal;
        $this->_view->uploadTotal = $uploadTotal;
        $this->_view->stopTotal = $stopTotal;
        $this->_view->active = $active;
        $this->_view->status = $status;
        $this->_view->dis = $dis;
        $this->_view->keyword = $keyword;
        $this->_view->minPrice = $minPrice;
        $this->_view->maxPrice = $maxPrice;
        $this->_view->perpage = $perpage;
        $this->_view->page = $page;
        $this->_view->showType = $showType;
        $this->_view->checkResult = $checkResult;
        $this->_view->searchType = $searchType;
        $this->_view->home = Yaf_Application::app()->getConfig()->get('home')->get('gapi')->url;
        $total = $showList . 'Total';
        $total = $$total;
        $pageObj = new Page($total, $perpage, $showpage, $page, '', array('goods', 'index', 'keyword' => $keyword, 'status' => $status, 'perpage' => $perpage, 'minPrice' => $minPrice, 'maxPrice' => $maxPrice, 'showType' => $showType, 'checkResult' => $checkResult, 'searchType' => $searchType, 'showList' => $showList));
        $this->_view->pagebar = $pageObj->showPage();
    }

    //添加商品
    public function addAction() {
        if ($this->isPost()) {
            $uid = Yaf_Registry::get('uid');
            $goodArr = array();
            $goodArr['name'] = trim($this->getPost('name'));
            $goodArr['shopId'] = intval($this->getPost('shopId'));
            $goodArr['goodsNo'] = trim($this->getPost('goodsNo'));
            $goodArr['code'] = Service::getInstance('goods')->getGoodsCode($goodArr['shopId']);
            $goodArr['price'] = trim($this->getPost('price'));
            $goodArr['goodsStock'] = intval($this->getPost('stock', 0));
            // $imageS = $this->getPost('imgs');
            $goodArr['uploader'] = 'd-' . $uid;
            $goodArr['createTime'] = time();
            $goodArr['intro'] = htmlspecialchars($this->getPost('intro'));
            $goodArr['category1'] = trim($this->getPost('category1'));
            $goodArr['category2'] = trim($this->getPost('category2'));
            $goodArr['category3'] = trim($this->getPost('category3'));
            $goodArr['content'] = htmlspecialchars($this->getPost('content'));
            $goodArr['showPrice'] = $this->getPost('showPrice');
            if (!$goodArr['shopId']) {
                $this->error('请选择店铺');
                $category1 = Service::getInstance('goods')->getGoodsCategory();
                $shop = Service::getInstance('shop')->getList();
                $this->_view->shop = $shop;
                $this->_view->category1 = $category1;
                return;
            }
            if (!$goodArr['name']) {
                $this->error('请填写商品名称');
                $category1 = Service::getInstance('goods')->getGoodsCategory();
                $shop = Service::getInstance('shop')->getList();
                $this->_view->shop = $shop;
                $this->_view->category1 = $category1;
            }
            if (!$goodArr['price']) {
                $this->error('请填写商品价格');
                $category1 = Service::getInstance('goods')->getGoodsCategory();
                $shop = Service::getInstance('shop')->getList();
                $this->_view->shop = $shop;
                $this->_view->category1 = $category1;
            }
            $key = $this->getPost('key');
            $value = $this->getPost('value');
            $arra = '';
            if ($key) {
                foreach ($key as $k => $v) {
                    if (trim($v) != '' && trim($value[$k]) != '') {
                        $a = explode('-', trim($v));
                        $b = explode('-', trim($value[$k]));
                        if (!isset($a[1]))
                            $a[1] = '0';
                        if (!isset($b[1]))
                            $b[1] = '0';
                        $arra[] = '{"key":{"id":' . $a[1] . ',"name":"' . $a[0] . '"},"value":{"id":' . $b[1] . ',"name":"' . $b[0] . '"}}';
                    }
                }
            }

            if ($arra) {
                $attribute = '[' . implode(',', $arra) . ']';
            } else {
                $attribute = '';
            }
            $goodArr['attribute'] = $attribute;
            $goodId = Service::getInstance('goods')->add($goodArr);
            if ($goodId) {
                unset($goodArr);
                $file = $_FILES['file'];
                $count = count($file['name']);
                if ($count) {
                    $oneTime = true;
                    for ($i = 0; $i < $count; $i++) {
                        if (!$file['error'][$i]) {
                            $avatar = $file['tmp_name'][$i];
                            $hash = md5($avatar);
                            $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                            $original = Util::getDir($dir, $hash) . $hash . "_image.jpg";
                            if (move_uploaded_file($avatar, $original)) {
                                $imageS[] = $hash;
                                $size = array('100x100', '800x800');
                                if ($oneTime) {
                                    $size[] = '400x400';
                                    $oneTime = false;
                                }
                                Service::getInstance('superadmin')->makeThumbFac($original, $size);
                            } else {
                                continue;
                            }
                        }
                    }
                }
                if ($imageS) {
                    $sql = "INSERT INTO `goods_image` (`goodsId`, `image`, `sort`) VALUES";
                    foreach ($imageS as $k => $v) {
                        $temp = intval($k + 1);
                        $sql .="('{$goodId}', '{$v}', $temp ),";
                    }
                    $sql = substr($sql, 0, -1);
                    Service::getInstance('goods')->addgoods_images($sql);
                }
                $this->flash('/goods/index', '添加成功');
            } else {
                $this->flash('/goods/index', '添加失败');
            }
        }
        $category1 = Service::getInstance('goods')->getGoodsCategory();
        $shop = Service::getInstance('shop')->getList();
        $this->_view->shop = $shop;
        $this->_view->category1 = $category1;
    }

    //编辑商品
    public function editAction() {
        if ($this->isPost()) {
            $keyword = $this->getPost('keyword', '');
            $status = $this->getPost('status', '');
            $page = intval($this->getPost('page'));
            $gid = intval($this->getPost('id'));
            $shopId = intval($this->getPost('shopId'));
            $goodsNo = trim($this->getPost('goodsNo'));
            $name = trim($this->getPost('name'));
            //$purchrice = trim($this->getPost('purchPrice'));
            $price = trim($this->getPost('price'));
            $stock = intval($this->getPost('stock', 0));
            $category1 = trim($this->getPost('category1'));
            $category2 = trim($this->getPost('category2'));
            $category3 = trim($this->getPost('category3'));
            $taobaoSpecial = trim($this->getPost('taobaoSpecial',''));
            $showPrice = 1;
            $key = $this->getPost('key');
            $value = $this->getPost('value');
            $intro = htmlspecialchars($this->getPost('intro'));
            $imgs = $this->getPost('imgs');
            $content = htmlspecialchars($this->getPost('content'));

            if (!$gid) {
                $this->flash('/goods/index', '参数错误');
                return false;
            }
            if (!$name) {
                $this->error('商品名称不能为空');
                $Info = Service::getInstance('goods')->getGoodsInfoById($gid);
                $category1 = Service::getInstance('goods')->getGoodsCategory();
                $this->_view->Info = $Info;
                $this->_view->category1 = $category1;
                return;
            }

            $arra = '';
            if ($key) {
                foreach ($key as $k => $v) {
                    if (trim($v) != '' && trim($value[$k]) != '') {
                        $a = explode('-', trim($v));
                        $b = explode('-', trim($value[$k]));
                        if (!isset($a[1]))
                            $a[1] = '0';
                        if (!isset($b[1]))
                            $b[1] = '0';
                        $arra[] = '{"key":{"id":' . $a[1] . ',"name":"' . $a[0] . '"},"value":{"id":' . $b[1] . ',"name":"' . $b[0] . '"}}';
                    }
                }
            }

            if ($arra) {
                $attributes = '[' . implode(',', $arra) . ']';
            } else {
                $attributes = '';
            }

            $data = array(
                'name' => $name,
                'shopId' => $shopId,
                'goodsNo' => $goodsNo,
                //'purchPrice'=>$purchrice,
                'price' => $price,
                'goodsStock' => $stock,
                'showPrice' => $showPrice,
                'category1' => $category1,
                'category2' => $category2,
                'category3' => $category3,
                'intro' => $intro,
                'content' => $content,
                'attribute' => $attributes,
                'editor' => 'd-' . Yaf_Registry::get('uid'),
                'updateTime' => time(),
                'taobao_special' => $taobaoSpecial
            );
            if ($stock == 0) {
                $data['status'] = 2;
                $data['platform'] = 1;
            }
            $goodsStatus = Service::getInstance('goods')->getStatus($gid);
            if ($stock > 0 && $goodsStatus == 2) {
                $data['status'] = 1;
            }


            // 执行基本数据编辑写入
            $res = Service::getInstance('goods')->edit($data, $gid);

            if ($res >= 0) {
                $file = $_FILES['file'];
                if (!empty($file)) {
                    //获取删除新增图片的记录
                    $delImg = $this->getPost('delImg');
                    $record = array();
                    if (!empty($delImg)) {
                        foreach ($delImg as $num => $del) {
                            foreach ($del as $index => $value) {
                                $record[] = $num . '-' . $index;
                            }
                        }
                    }
                    //获取新增加图片的索引记录
                    $imgSort = $this->getPost('imgSort');
                    $imgDir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                    if (!empty($file['name'])) {
                        foreach ($file['name'] as $num => $img) {
                            if (!empty($img)) {
                                foreach ($img as $index => $name) {
                                    $flag = $num . '-' . $index;
                                    if (!$file['error'][$num][$index] && !in_array($flag, $record)) {
                                        $avatar = $file['tmp_name'][$num][$index];
                                        $hash = md5($avatar);
                                        $original = Util::getDir($imgDir, $hash) . $hash . '_image.jpg';
                                        if (move_uploaded_file($avatar, $original)) {
                                            //对新增加的图片做页面相对应的索引记录
                                            $sortIndex = $imgSort[$num][$index];
                                            $imgs[$sortIndex] = $hash;
                                            //对新增加的图片做普通缩略处理,排序后做头图处理
                                            $size = array('100x100', '800x800');
                                            Service::getInstance('superadmin')->makeThumbFac($original, $size);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //对图片所有hash值按页面索引进行排序
                if (!empty($imgs))
                    ksort($imgs);
                //对头图做缩略处理
                $original = Util::getDir($imgDir, $imgs[0]) . $imgs[0] . '_image.jpg';
                $oneThumb = str_replace('_image.', '_thumb_400x400.', $original);
                if (!file_exists($oneThumb)) {
                    Service::getInstance('superadmin')->makeThumbFac($original, array('400x400'));
                }

                //获取删除已经保存的记录
                $delOldImg = $this->getPost('delOldImg');
                if (!empty($delOldImg)) {
                    foreach ($delOldImg as $k => $hash) {
                        $file = Util::getDir($imgDir, $hash) . $hash . '_image.jpg';
                        if (file_exists($file)) {
                            @unlink($file);
                        }
                    }
                }

                if (is_array($imgs)) {
                    $sql = "INSERT INTO `goods_image` (`goodsId`, `image`, `sort`) VALUES";
                    foreach ($imgs as $k => $v) {
                        $temp = intval($k + 1);
                        $sql .="('{$gid}', '{$v}', $temp ),";
                    }
                    $sql = substr($sql, 0, -1);
                    Service::getInstance('goods')->editgoods_images($sql, $gid);
                }
                $this->flash("/goods/index?keyword={$keyword}&status={$status}&page={$page}", '编辑成功');
                return false;
            } else {
                $this->flash("/goods/index?keyword={$keyword}&status={$status}&page={$page}", '编辑失败');
                return false;
            }
        } else {
            $keyword = $this->getQuery('keyword', '');
            $status = $this->getQuery('status', '');
            $page = $this->getQuery('page', 1);
            $id = $this->getQuery('id', 0);
            $Info = Service::getInstance('goods')->getGoodsInfoById($id);
            $category1 = Service::getInstance('goods')->getGoodsCategory();
            if ($Info['category1'] && $Info['category2']) {
                $category2 = Service::getInstance('goods')->getCat($Info['category1']);
                $category3 = Service::getInstance('goods')->getCat($Info['category2']);
            } else {
                $category2 = array();
                $category3 = array();
            }
            $shop = Service::getInstance('shop')->getList();
            $arr = $Info['attribute'];
            if (is_array($arr)) {
                foreach ($arr as $k => $v) {
                    if ($v['key']['id']) {
                        $data = Service::getInstance('goods')->getAttrValueByAttrId($v['key']['id']);
                        $arr[$k]['value']['param'] = $data;
                    }
                }
            }
            $Info['attribute'] = $arr ? $arr : '';
            $this->_view->shop = $shop;
            $this->_view->Info = $Info;
            $this->_view->keyword = $keyword;
            $this->_view->status = $status;
            $this->_view->page = $page;
            $this->_view->category1 = $category1;
            $this->_view->category2 = $category2;
            $this->_view->category3 = $category3;
        }
    }

    //推荐
    public function recommendAction() {
        if ($this->isPost()) {
            $id = $this->getPost('id');
            if (!$id)
                $this->respon(0, '数据异常');
            $res = Service::getInstance('goods')->edit(array('recommend' => 1), $id);
            if ($res) {
                $this->respon(1, $res);
            } else {
                $this->respon(0, '失败');
            }
        } else {
            $this->respon(0, '失败');
        }
    }

    //取消推荐
    public function unrecommendAction() {
        if ($this->isPost()) {
            $id = $this->getPost('id');
            if (!$id)
                $this->respon(0, '数据异常');
            $res = Service::getInstance('goods')->edit(array('recommend' => 0), $id);
            if ($res) {
                $this->respon(1, $res);
            } else {
                $this->respon(0, '失败');
            }
        } else {
            $this->respon(0, '失败');
        }
    }

    //停售
    public function pauseAction() {
        if ($this->isPost()) {
            $id = $this->getPost('id');
            if (!$id)
                $this->respon(0, '数据异常');
            $res = Service::getInstance('goods')->edit(array("status" => 3, "platform" => 1), $id);
            if ($res) {
                Service::getInstance('apigoods')->downGoodsByGid( $id );
                Service::getInstance('push')->editByWhere( array("status" =>5 ),'goodsId = '.$id );
                $this->respon(1, $res);
            } else {
                $this->respon(0, '失败');
            }
        } else {
            $this->respon(0, '失败');
        }
    }

    //上架
    public function restoreAction() {
        if ($this->isPost()) {
            $id = $this->getPost('id');
            if (!$id)
                $this->respon(0, '数据异常');
            $goods = Service::getInstance('goods')->getGoodsById($id);
            if ($goods['goodsStock'] == 0)
                $this->respon(0, "库存为0，不能上架");
            $res = Service::getInstance('goods')->edit(array("status" => 1, "platform" => 2), $id);
            if ($res) {
                $this->respon(1, $res);
            } else {
                $this->respon(0, '失败');
            }
        } else {
            $this->respon(0, '失败');
        }
    }

    //售出
    public function delStockAction() {
        if ($this->isPost()) {
            $id = $this->getPost('goodsId');
            $number = $this->getPost('number');
            if (!$id)
                $this->respon(0, '数据异常');
            if (!$number)
                $this->respon(0, '请输入数量');
            $res = Service::getInstance('goods')->sellGoods($id, $number);
            if ($res) {
                $this->respon(1, $res);
            } else {
                $this->respon(0, '失败');
            }
        } else {
            $this->respon(0, '失败');
        }
    }

    //补货
    public function addStockAction() {
        if ($this->isPost()) {
            $id = $this->getPost('goodsId');
            $number = $this->getPost('number');
            if (!$id)
                $this->respon(0, '数据异常');
            if (!$number)
                $this->respon(0, '请输入数量');
            $res = Service::getInstance('goods')->addStock($id, $number);
            if ($res) {
                $this->respon(1, $res);
            } else {
                $this->respon(0, '失败');
            }
        } else {
            $this->respon(0, '失败');
        }
    }

    //编辑价格
    public function editPriceAction() {
        if ($this->isPost()) {
            $id = $this->getPost('goodsId');
            $purchPrice = $this->getPost('purchPrice');
            if (!$id)
                $this->respon(0, '数据异常');
            if (!$purchPrice)
                $this->respon(0, '请输入价格');


            // 获取到原价
            $sql = "SELECT purchPrice FROM goods where id={$id}";
            $oldPurchPrice = $this->db->fetchOne($sql);
            //判断purchPrice是否变动，如果变动，记录原purchPrice到historyPrice表，并在goods表中改变当前商品status为3,platform为1
            if ($oldPurchPrice != $purchPrice) {
                // 建立商品历史价格相关记录数组
                $priceInfo = array();
                // 进货价格
                $priceInfo['purchPrice'] = $purchPrice;
                // 时间
                $priceInfo['updateTime'] = time();
                // 商品id
                $priceInfo['goodsId'] = $id;
                // 操作者id
                $priceInfo['devId'] = $this->_developer['id'];

                // 以上信息insert到historyPrice表（字段包括id,purchPrice,updateTime,goodsId,devId）中

                $res = $this->db->insert('goods_price_history', $priceInfo);
                // 获取当前商品推送状态,0是正常，1是审核中，2是锁定,3是价格异常
                $sql = "SELECT status FROM push where goodsId={$id}";
                $pushStatus = $this->db->fetchOne($sql);

                // 如果当前商品在正常推送中，则改变状态为价格异常
                if ($pushStatus == 0) {
                    // 改变推送状态
                    $res = $this->db->update('push', array('status' => 3), 'goodsId=' . $id);
                }
            }

            $result = Service::getInstance('goods')->editPrice($id, $purchPrice);
            if ($result) {
                $this->respon(1, $result);
            } else {
                $this->respon(0, '失败');
            }
        } else {
            $this->respon(0, '失败');
        }
    }

    //类目
    public function categoryAction() {
        $id = $this->getPost('pid');
        if ($id === '') {
            $this->respon(0, array());
        }
        $category = Service::getInstance('goods')->getGoodsCategory($id);
        $this->respon(1, $category);
        return false;
    }

    //改变商品状态
    public function changeAction() {
        $id = $this->getQuery('id');
        $status = $this->getQuery('status');
        $keyword = $this->getQuery('keyword');
        if ($status == 1) {
            $data = array('status' => 3);
        } elseif ($status == 2) {
            $g = Service::getInstance('goods')->getGoodsById($id);
            if ($g['goodsStock'] != '0') {
                $data = array('status' => 1);
            } else {
                echo "<script>alert('库存不足，上架失败');window.location.href=document.referrer</script>";
            }
        } elseif ($status == 3) {
            $data = array('status' => 5);
        } else {
            $this->flash("/goods/index/", "参数错误");
        }
        if (Service::getInstance('goods')->change($id, $data) >= 0) {
            if ($status == 2) {
                $userLog = array(
                    'devUid' => Yaf_Registry::get('uid'),
                    'shop' => $g['shopId'],
                    'gid' => $id,
                    'action' => $this->_actName,
                    'controller' => $this->_conName,
                    'name' => '上架商品'
                );
                Service::getInstance('blog')->add_user_log($userLog);
            }
            //$this->flash("/goods/index/?status=".$status."&keyword=".$keyword,"操作成功");
            echo "<script>alert('操作成功');window.location.href=document.referrer</script>";
        } else {
            //$this->flash("/goods/index/?status=".$status."&keyword=".$keyword,"操作失败");
            echo "<script>alert('操作失败');window.location.href=document.referrer</script>";
        }
    }

    //下架
    public function downGoodsAction() {
        $id = $this->getPost('id');
        $data = array('status' => 2);
        if (Service::getInstance('goods')->change($id, $data) >= 0) {
            Service::getInstance('apigoods')->downGoodsByGid($id);
            $g = Service::getInstance('goods')->getGoodsById($id);
            $userLog = array(
                'devUid' => Yaf_Registry::get('uid'),
                'shop' => $g['shopId'],
                'gid' => $id,
                'action' => $this->_actName,
                'controller' => $this->_conName,
                'name' => '下架商品'
            );
            Service::getInstance('blog')->add_user_log($userLog);
            $this->respon(1, "操作成功");
        } else {
            $this->respon(0, "操作失败");
        }
    }

    //删除
    public function delGoodsAction() {
        if ($this->isPost()) {
            $id = $this->getPost('id');
            $data = array('status' => 5);
            if (Service::getInstance('goods')->change($id, $data) >= 0) {
                Service::getInstance('push')->editByWhere( array("status" =>6 ),'goodsId = '.$id );
                Service::getInstance('apigoods')->downGoodsByGid( $id );
                $this->respon(1, "操作成功");
            } else {
                $this->respon(0, "操作失败");
            }
        }
        return false;
    }

    //删除商品
    public function delAction() {
        return false;
        $id = $this->getQuery('id');
        if (Service::getInstance('goods')->delGoods($id)) {
            $this->flash("/goods/index/", "删除成功");
        } else {
            $this->flash("/goods/index/", "删除失败");
        }
    }

    //删除商品已经存在的图片
    public function delOldImgAction() {
        $imgId = intval($this->getPost('imgId'));
        $hash = trim($this->getPost('hash'));

        $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
        $file = Util::getDir($dir, $hash) . $hash . '_image.jpg';
        if (unlink($file)) {
            $res = Service::getInstance('goods')->delGoodsImgById($imgId);
        }
        if ($res) {
            $this->respon(1, '删除图片成功！');
        } else {
            $this->respon(1, '删除图片失败！');
        }
    }

    //申请分销
    public function applyAction() {
        if ($this->isPost()) {
            $id = $this->getPost('id');
            $check = Service::getInstance('goods')->getCheckGoodsByGoodsId($id);
            if ($check) {
                $this->respon(0, "该商品已经申请过，不能重复申请");
            }
            if (Service::getInstance('goods')->addGoodsCheck($id)) {
                $this->respon(1, "申请成功");
            } else {
                $this->respon(0, "申请失败");
            }
        }
        return false;
    }

    //上传商品图片（暂取消）
    public function upfileAction() {
        if (!$_FILES['file']['error']) {
            $avatar = $_FILES['file']['tmp_name'];
            $hash = md5($avatar);
            $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
            if (move_uploaded_file($avatar, Util::getDir($dir, $hash) . $hash . "_image.jpg")) {
                $Res = array();
                $Res = array(
                    'imgurl' => Service::getInstance('goods')->getAvata($hash),
                    'path' => $hash
                );

                $this->respon(1, $Res);
            } else {
                $this->respon(0, "上传失败");
            }
        }
        $this->respon(0, $_FILES['file']['error'] . "请选择您要上传的图片!");
        return false;
    }

    //商品参数
    public function getParaAction() {
        $pid = $this->getPost('pid');
        $data = Service::getInstance('goods')->getPar($pid);
        $this->respon(1, $data);
    }

    //商品参数
    public function setpriceAction() {
        $data = Service::getInstance('goods')->getPrice();
        return false;
    }

    public function expcountAction() {
        $keyword = $this->getPost('keyword', '');
        $status = $this->getPost('status', '');
        $rows = Service::getInstance('goods')->getGoodsRows($keyword, $status);
        if ($rows) {
            $this->respon(1, $rows);
        } else {
            $this->respon(0, $rows);
        }
    }

    //导出数据
    public function exportgImgAction() {
        ini_set('memory_limit', '256M');
        $times = $this->getPost('times', 1);
        $size = $this->getPost('size', 100);
        $keyword = $this->getPost('keyword', '');
        $status = $this->getPost('status', '');

        $excel = new PHPExcel();
        $letter = range('A', 'Z');
        //表头数组
        $tableheader = array('ID', '商品图片', '商品名称', 'SKU', '货号', '进货价', '库存', '参数', '商品描述', '状态', '店铺Id', '上传者', '商品来源');
        //填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        $data = Service::getInstance('goods')->getAllPushGoodsNoC($times, $size, $keyword, $status, true);

        for ($i = 2; $i <= count($data) + 1; $i++) {
            //设置行的高度：
            $excel->getActiveSheet()->getRowDimension($i)->setRowHeight(80);
            //设置图片列宽度：
            $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objDrawing = new Plugins_PHPExcel_Worksheet_Drawing();
            //设置每一列的内容：
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                if ($j == 1) {
                    $objDrawing->setPath($value);
                    $objDrawing->setCoordinates("$letter[$j]$i");
                    $objDrawing->getShadow()->setVisible(true);
                    $objDrawing->getShadow()->setDirection(50);
                    $objDrawing->setOffsetX(15);
                    $objDrawing->setOffsetY(5);
                    $objDrawing->setRotation(18);
                    $objDrawing->setWidth(100);
                    $objDrawing->setHeight(100);
                } else {
                    $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                }
                $j++;
            }
            $objDrawing->setWorksheet($excel->getActiveSheet());
        }

        $write = new Plugins_PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=Goods-" . $status . '-' . $keyword . '-' . $times . ".xls");
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    //导出
    public function exportgAction() {

        $keyword = $this->getQuery('keyword', '');
        $status = $this->getQuery('status', '');
        $excel = new PHPExcel();
        $letter = range('A', 'Z');
        //表头数组
        $tableheader = array('ID', '商品名称', 'SKU', '货号', '进货价', '库存', '参数', '商品描述', '状态', '店铺Id', '上传者', '商品来源');
        //填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }

        $data = Service::getInstance('goods')->getAllPushGoodsNoC($times = '', $size = '', $keyword, $status);

        for ($i = 2; $i <= count($data) + 1; $i++) {
            //设置每一列的内容：
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }

        $write = new Plugins_PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=Goods-" . $status . '-' . $keyword . '-' . $times . ".xls");
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    public function exportallgoodsAction() {
        ini_set('memory_limit', '256M');
        $keyword = $this->getQuery('keyword', '');
        $status = $this->getQuery('status', '');
        $excel = new PHPExcel();
        $letter = range('A', 'Z');
        //表头数组
        $tableheader = array('ID', '商品名称', 'SKU', '货号', '进货价', '库存', '参数', '商品描述', '状态', '店铺Id', '上传者', '商品来源');
        //填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }

        $data = Service::getInstance('goods')->getExportAllGoods($keyword, $status);

        for ($i = 2; $i <= count($data) + 1; $i++) {
            //设置每一列的内容：
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }

        $write = new Plugins_PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=allGoods-" . $status . '-' . $keyword . ".xls");
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    public function delSellGoodsAction() {
        Service::getInstance('goods')->delSellGoods();
    }

    public function changeGoodsAction() {
        Service::getInstance('goods')->setGoodsToShop();
    }

    //测试
    public function upGoodsImgAction() {
        $this->respon(1, $_FILES['file']['tmp_name']);
    }

    //平台商品进审核：先把平台商品变商家商品,然后添加到审核表中,先取500
    public function goodsToCheckAction() {
        $res = Service::getInstance('goods')->goodsToCheck();
        exit('操作完成');
    }

    /**
     * 将类目和参数生成txt
     */
    /**
      public function catelistAction() {
      $list = Service::getInstance('goods')->getAttr();
      Log::simplewrite('cateArr', json_encode($list));
      exit;
      }
      public function paramlistAction() {
      $list = Service::getInstance('goods')->getParam();
      Log::simplewrite('paramArr', json_encode($list));
      exit;
      }
     */
    public function downloadImgByGidAction( $gid = 0 ){

        if (!extension_loaded('zip')) {
            exit('not exists zip library');
        }

        $goodsId = $this->getQuery('gid',0)+0;
        $code    = trim( $this->getQuery('code') );

        if( !$goodsId ) return false;

        $imgPath = Service::getInstance('goods')->getImageDirByGoodsId( $goodsId,'800' );

        if( empty($imgPath) ) $imgPath[] = Service::getInstance('goods')->getAvataExp( '' );

        $target  = APP_PATH.'/public/data/'.$code.'.zip';

        $res = $this->makeZip( $imgPath,$target,array( 'code'=>$code ) );

        if( !$res ) return false;

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        header('Content-disposition: attachment; filename='.$code.'.zip');
        header('Content-Length: '. filesize($target));
        @readfile($target);
    }
 
    private function makeZip( $files,$target,$param ){

        if( !$files || !is_array($files) ) return false;

        $zip = new ZipArchive();

        if( $zip->open( $target,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE ) === true ){
            
            foreach( $files as $k => $file ){
                $sort = $k + 1;
                if( $sort < 10 ) $sort = '0'.$sort;
                $final = './'.$param['code'].'/'.$param['code'].'_'.$sort.'.jpg';
                $zip->addFile( $file,$final );
            }
        }
        @$zip->close();
        if( file_exists( $target ) ){
            return true;
        }else{
            return false;
        }
    }
    
    //添加商品评论
    public function addGoodsCommentAction(){
        $comment_arr = array();
        $comment_arr['goods_id'] = intval($this->getPost('goodsId'));
        $comment_arr['content'] = htmlspecialchars($this->getPost('content'));
        $comment_arr['commentaor_id'] = Yaf_Registry::get('uid');
        $comment_arr['comment_time'] = time();
        $comment_arr['content_type'] = 2;
        $res = Service::getInstance('goods')->addGoodsCommentLog($comment_arr);
        if($res){
            $this->respon(1,'收到');
        }else{
            $this->respon(0,'失败');
        }
        
    }


}
