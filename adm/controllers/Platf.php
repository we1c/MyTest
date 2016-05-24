<?php

class PlatfController extends BaseController {

    public function init() {
        parent::init();
    }

    public function indexAction() {
        $showType = trim($this->getQuery('showType', 'list'));
        $perpage = $this->getQuery('perpage', 100);
        $showpage = 8;
        $page = $this->getQuery('page', 1);
        $keyword = trim($this->getQuery('keyword', ''));
        $status = $this->getQuery('status', '');
        $minPrice = $this->getQuery('minPrice', '');
        $maxPrice = $this->getQuery('maxPrice', '');
        $checkResult = $this->getQuery('checkResult', '');
        $searchType = $this->getQuery('searchType', '1');
        $showList = $this->getQuery('showList', 'platf');
        $developPlan = 6;//商家上传的参数
        $stop = " AND G.status <> 2 AND G.status <> 3 ";//除了停止销售
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
        foreach ($data['list'] as $k => $v){
            $data['list'][$k]['shopScore'] = Service::getInstance('shop')->getShopScoreToday($v['shopId']);
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
        $pageObj = new Page($total, $perpage, $showpage, $page, '', array('platf', 'index', 'keyword' => $keyword, 'status' => $status, 'perpage' => $perpage, 'minPrice' => $minPrice, 'maxPrice' => $maxPrice, 'showType' => $showType, 'checkResult' => $checkResult, 'searchType' => $searchType, 'showList' => $showList));
        $this->_view->pagebar = $pageObj->showPage();
    }

    public function addAction() {
        if ($this->isPost()) {
            $uid = Yaf_Registry::get('uid');
            $goodArr = array();
            $goodArr['name'] = trim($this->getPost('name'));
            $goodArr['shopId'] = intval($this->getPost('shopId'));
            $goodArr['code'] = Service::getInstance('goods')->getGoodsCode($goodArr['shopId']);
            $goodArr['purchPrice'] = trim($this->getPost('price'));
            //  $imageS = $this->getPost('imgs');
            $goodArr['uploader'] = $uid;
            $goodArr['createTime'] = time();
            $goodArr['intro'] = htmlspecialchars($this->getPost('intro'));
            $goodArr['category1'] = trim($this->getPost('category1'));
            $goodArr['category2'] = trim($this->getPost('category2'));
            $goodArr['category3'] = trim($this->getPost('category3'));
            $goodArr['content'] = htmlspecialchars($this->getPost('content'));
            $goodArr['showPrice'] = $this->getPost('showPrice');
            $goodArr['goodsNo'] = trim($this->getPost('goodsNo'));
            $goodArr['platform'] = 1;
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
            if (!$goodArr['purchPrice']) {
                $this->error('请填写商品价格');
                $category1 = Service::getInstance('goods')->getGoodsCategory();
                $shop = Service::getInstance('shop')->getList();
                $this->_view->shop = $shop;
                $this->_view->category1 = $category1;
            }
            $key = $this->getPost('key');
            $value = $this->getPost('value');

            //参数处理
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
            $shop = Service::getInstance('shop')->getShopinfo(intval($goodArr['shopId']));
            $goodArr['price'] = round($goodArr['purchPrice'] * $shop['mtimes']);
            $goodId = Service::getInstance('goods')->add($goodArr);
            if ($goodId) {
                $result = Service::getInstance('goods')->addGoodsCheck($goodId);
                if (!$result) {
                    Service::getInstance('goods')->delGoods($goodId);
                    $this->flash('/platf/index', '添加失败');
                }
                unset($goodArr);
                $file = $_FILES['file'];
                $count = count($file['name']);
                $imageS = '';
                if ($count) {
                    for ($i = 0; $i < $count; $i++) {
                        if (!$file['error'][$i]) {
                            $avatar = $file['tmp_name'][$i];
                            $hash = md5($avatar);
                            $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                            if (move_uploaded_file($avatar, Util::getDir($dir, $hash) . $hash . "_image.jpg")) {
                                $imageS[] = $hash;
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
                $this->flash('/platf/index', '添加成功');
            } else {
                $this->flash('/platf/index', '添加失败');
            }
        }
        $category1 = Service::getInstance('goods')->getGoodsCategory();
        $shop = Service::getInstance('shop')->getList();
        $this->_view->shop = $shop;
        $this->_view->category1 = $category1;
    }

    public function editAction() {
        if ($this->isPost()) {
            $gid = intval($this->getPost('id'));
            $imgs = $this->getPost('imgs');
            if (!$gid) {
                $this->flash('/platf/index', '参数错误');
                return false;
            }

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
                $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                if (!empty($file['name'])) {
                    foreach ($file['name'] as $num => $img) {
                        if (!empty($img)) {
                            foreach ($img as $index => $name) {
                                $flag = $num . '-' . $index;
                                if (!$file['error'][$num][$index] && !in_array($flag, $record)) {
                                    $avatar = $file['tmp_name'][$num][$index];
                                    $hash = md5($avatar);
                                    if (move_uploaded_file($avatar, Util::getDir($dir, $hash) . $hash . '_image.jpg')) {
                                        //对新增加的图片做页面相对应的索引记录
                                        $sortIndex = $imgSort[$num][$index];
                                        $imgs[$sortIndex] = $hash;
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
            //头图缩略
            if (isset($imgs[0])) {
                $original = Util::getDir($dir, $imgs[0]) . $imgs[0] . '_image.jpg';
                $oneThumb = str_replace('_image.', '_thumb_400x400.', $original);
                if (!file_exists($oneThumb)) {
                    Service::getInstance('superadmin')->makeThumbFac($original, array('400x400'));
                }
            }
            //获取删除已经保存的记录
            $delOldImg = $this->getPost('delOldImg');
            if (!empty($delOldImg)) {
                foreach ($delOldImg as $k => $hash) {
                    $imgType = array('_image', '_thumb_100x100', '_thumb_400x400', '_thumb_800x800');
                    foreach ($imgType as $i => $t) {
                        $file = Util::getDir($imgDir, $hash) . $hash . $t . '.jpg';
                        if (file_exists($file)) {
                            @unlink($file);
                        }
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
            } else {
                Service::getInstance('goods')->delgoods_images($gid);
            }
            $this->flash('/platf/index', '编辑成功');
        } else {
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
                    if ($v['value']['id']) {
                        $data = Service::getInstance('goods')->getParamByPid($v['key']['id']);
                        $arr[$k]['value']['param'] = $data;
                    }
                }
            }
            $Info['attribute'] = $arr ? $arr : '';
            $this->_view->shop = $shop;
            $this->_view->Info = $Info;
            $this->_view->category1 = $category1;
            $this->_view->category2 = $category2;
            $this->_view->category3 = $category3;
        }
    }

    public function categoryAction() {
        $id = $this->getPost('pid');
        if ($id === '') {
            $this->respon(0, array());
        }
        $category = Service::getInstance('goods')->getGoodsCategory($id);
        $this->respon(1, $category);
        return false;
    }

    public function changeAction() {
        $id = $this->getQuery('id');
        $status = $this->getQuery('status');
        if ($status == 1) {
            $data = array('status' => 3);
        } elseif ($status == 2) {
            $data = array('status' => 1);
        } elseif ($status == 3) {
            $data = array('status' => 5);
        } else {
            $this->flash("/platf/index/", "参数错误");
        }
        if (Service::getInstance('goods')->change($id, $data) >= 0) {
            $this->flash("/platf/index/", "操作成功");
        } else {
            $this->flash("/platf/index/", "操作失败");
        }
    }

    //删除 （停用）
    public function delAction() {
        $id = $this->getQuery('id');
        if (Service::getInstance('goods')->delGoods($id)) {
            $this->flash("/goods/index/", "删除成功");
        } else {
            $this->flash("/goods/index/", "删除失败");
        }
    }

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

    public function getParaAction() {
        $pid = $this->getPost('pid');
        $data = Service::getInstance('goods')->getPar($pid);
        $this->respon(1, $data);
    }

    //推送商品
    public function pushGoodsAction() {
        if ( $this->isPost() ) {

            $channels = $this->getPost('channels');
            $goodsId = $this->getPost('goodsId');

            $goods = Service::getInstance('goods')->getGoodsById( $goodsId );
            if ( $goods['status'] != '1' ) {
                $this->respon( 0, "该商品不符合推送条件，请重新选择！" );
            }

            $result['exist']      = array();
            $result['sqlSuccess'] = array();
            $result['sqlError']   = array();
            $result['notAccess']  = array();
            $result['apiSuccess'] = array();
            $result['apiFailed']  = array();
            foreach( $channels as $cid ){
                $exist = Service::getInstance('goods')->getIsPushGoods( $goodsId, $cid );
                $channel = Service::getInstance('distributor')->getInfoById( $cid );
                if( !empty($exist) ){
                    $result['exist'][] = $channel['name'];
                    continue;
                }else{
                    if ( $goods['checkResult'] == 1 ) {
                            $status = 0;
                    } else {
                            $status = 1;
                    }
                    $data = array(
                        'goodsId'    =>$goodsId,
                        'channel'    =>$cid,
                        'createTime' =>time(),
                        'status'     =>$status,
                        'devId'      =>$this->_devid,
                        'fromWhere'  =>2
                    );

                    if ( Service::getInstance('goods')->addGoods( $data ) ){
                        if( $channel['apiType'] == 2 ){
                            if( $goods['checkResult'] == 1 ){
                                $push = array( 'channel'=>$cid,'goodsId'=>$goodsId,'action'=>'pushGoods' );
                                if( $this->redis->lpush( 'channel_goods',json_encode($push) ) ){
                                    $result['apiSuccess'][] = $channel['name'];
                                }else{
                                    $result['apiFailed'][] = $channel['name'];
                                }
                            }else{
                                $result['notAccess'][] = $channel['name'];
                            }
                        }else{
                            $result['sqlSuccess'][] = $channel['name'];
                        }
                    }else{
                        $result['sqlError'][] = $channel['name'];
                    }

                }

            }

            $this->respon( 1,$result );
            
        }
    }

    public function sendImgToChannel($gid, $channel) {
        $imgs = Service::getInstance('goods')->getImageDirByGoodsId($gid);
        $data = array(
            'channel' => 'c' . $channel,
            'action'  => 'sendImg',
            'gid'     => $gid,
            'param'   => $imgs,
        );

        $res = Service::getInstance('apipushgoods')->dispatch(json_encode($data));

        if ($res) return true;

        return false;
    }

    public function redisPushGoods($gid, $channel) {
        $info = Service::getInstance('goods')->getRedisPushGoodsById($gid);
        $imgUrl = Service::getInstance('goods')->getPushImgByGidCid($gid, $channel);
        $imgs = array();
        foreach ($imgUrl as $v) {
            $imgs[] = $v['imgUrl'];
        }
        $intro = preg_replace('/<\/?[^>]+>/i', '', htmlspecialchars_decode($info['intro']));
        $intro = preg_replace('/&nbsp;|&amp;|&#039;/', '', $intro);
        $data = array(
            'channel' => 'c' . $channel,
            'gid' => $gid,
            'action' => 'push',
            'param' => array(
                "goodsName" => $info['name'],
                "categoryID" => "1",
                "memo" => $intro,
                "price" => round($info['purchPrice'] * $info['ptimes']),
                "expressFee" => 0,
                "province" => "北京市",
                "city" => "北京",
                "district" => "东城区",
                "lng" => "116.391445",
                "lat" => "39.920791",
                "sortFlag" => "1",
                "num" => $info['goodsStock'],
                "marketPrice" => ($info['purchPrice'] * $info['mtimes']),
                "goodsPhotoList" => $imgs
            ),
        );
        $res = $this->redis->lpush('push_goods', json_encode($data));
        return $res;
    }

    //上架或下架
    public function updateGoodsAction() {
        $id = $this->getPost('id');
        $action = trim($this->getPost('action'));
        $relation = array(
            'down' => 3,
            'up' => 1
        );
        $data = array('status' => $relation[$action]);
        if (Service::getInstance('goods')->change($id, $data) >= 0) {
            $this->respon(1, "操作成功");
        } else {
            $this->respon(0, "操作失败");
        }
    }

    //删除
    public function delGoodsAction() {
        $id = $this->getPost('id');
        $data = array('status' => 5);
        if (Service::getInstance('goods')->change($id, $data) >= 0) {
            $this->respon(1, "操作成功");
        } else {
            $this->respon(0, "操作失败");
        }
    }

    //批量推送
    public function batchPushAction() {
        $cid = $this->getPost('cids');
        if (!$cid) {
            $this->respon(0, '请选择分销商');
            return false;
        }
        $gids = $this->getPost('goodsIds');
        if (empty($gids)) {
            $this->respon(0, '请选择推送商品');
            return false;
        }

        $channel = Service::getInstance('distributor')->getInfoById( $cid );
        $result['exist']      = array();
        $result['notAccess']  = array();
        $result['sqlError']   = array();
        $result['sqlSuccess'] = array();
        $result['apiSuccess'] = array();
        $result['apiFailed']  = array();

        foreach ($gids as $gid) {
            $exist = Service::getInstance('goods')->getIsPushGoods( $gid, $cid );
            $goods = Service::getInstance('goods')->getGoodsById( $gid );
            if( $exist ){
                $result['exist'][] = $goods['code'];
            }else{
                if ($goods['checkResult'] == 1) {
                    $status = 0;
                } else {
                    $status = 1;
                }
                $data = array(
                    'goodsId'    => $gid,
                    'channel'    => $cid,
                    'createTime' => time(),
                    'status'     => $status,
                    'devId'      => $this->_devid
                );
                if( Service::getInstance('goods')->addGoods($data) ){
                    if( $channel['apiType'] == 2 ){
                        if( $goods['checkResult'] == 1 ){
                            $param = array( 'channel'=>$cid,'goodsId'=>$gid,'action'=>'pushGoods' );
                            if ( $this->redis->lpush( 'channel_goods',json_encode( $param ) ) ){
                                $result['apiSuccess'][$gid] = $goods['code'];
                            }else{
                                $result['apiFailed'][] = $goods['code'];
                            }
                        }else{
                            $result['notAccess'][] = $goods['code'];
                        }
                    }else{
                        $result['sqlSuccess'][$gid] = $goods['code'];
                    }
                }else{
                    $result['sqlError'][] = $goods['code'];
                }
            }
        }
        
        $this->respon( 1, $result );
    }

    public function upGoodsImgAction() {
        $this->respon(1, $_FILES['file']['tmp_name']);
    }

    public function getgoodsimgbyidAction() {
        if ($this->isPost()) {
            $gid = $this->getPost('gid', 0);
            $img = Service::getInstance('goods')->getGoodsImgsByGoodsIds($gid);
            if (!empty($img)) {
                $this->respon(1, $img);
            } else {
                $this->respon(0, ' The goods without pictures !');
            }
        }
    }

    /**
     * 将类目和参数生成txt
     */
    /*
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
}
