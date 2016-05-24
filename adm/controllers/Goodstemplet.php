<?php

class GoodsTempletController extends BaseController {

    public function init() {
        parent::init();
    }
    //展示详情模板列表
    public function indexAction() {
        $perpage = $this->getQuery('perpage', 10);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword', '');
        $searchType = $this->getQuery('searchType', '1');
        $goodsTemplets = Service::getInstance('goodstemplet')->getTempletAll($page, $perpage, $keyword, $searchType);
        $total = $goodsTemplets['total'];
        unset($goodsTemplets['total']);
        $this->_view->goodsTempletList = $goodsTemplets;
        $this->_view->perpage = $perpage;
        $this->_view->page = $page;
        $this->_view->total = $total;
        $this->_view->keyword = $keyword;
        $this->_view->searchType = $searchType;
        $pageObj = new Page($total, $perpage, $showpage, $page, '', array('goodstemplet', 'index', 'keyword' => $keyword, 'perpage' => $perpage, 'searchType'=>$searchType));
        $this->_view->pagebar = $pageObj->showPage();
    }
    //通过提交的类目id验证是否有模板
    public function checkIsHasTempletAction() {
        $cat_id = intval($this->getPost('cat_id'));
        $templet = Service::getInstance('goodstemplet')->getTempletByCatId($cat_id);
        $html = '';
        if ($templet) {
            echo '<h4 class="tit ">你选择的类目已有模板，请到编辑修改</h4><hr />';
        } else {
            $html .= '<li class="neworder-info modul"><h4 class="tit">模块信息</h4><hr />';
            //主标题
            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox"><span>主标题</span></div></div><div class="col-sm-6 mb10"><input type="text" class="form-control" name="main_title[]" value="" /></div></div>';
            //副标题

            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox"><span>副标题</span></div></div><div class="col-sm-6 mb10"><input type="text" class="form-control" name="small_title[]" value="" /></div></div>';
            //引入图片

            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox" onclick="changeState(this)"><input type="hidden" name="is_img[]" value="0"><span>引入图片</span></div></div></div>';
            //引入规格
            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox" onclick="changeState(this)"><input type="hidden" name="is_spec[]" value="0"><span>引入规格</span></div></div></div>';
            //添加描述
            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox"><span>添加描述</span></div></div><div class="col-sm-6 mb10"><textarea name="info[]"></textarea></div></div>';
            //添加图片
            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox"><span>添加图片</span></div></div><div class="col-md-10 col-lg-10"><ul class="row detailsimg details-group"><li><img class="img-thumbnail" src="/img/newimg.gif"/><input class="fileInput" onchange="addImg(this)" type="file" num="0" name="file[]" multiple="multiple" /></li></ul><ul class="dragUl row detailsimg details-group"></ul></div></div>';
            $html .='<div class="row childpad text-right modul-info"><span onclick="deleteThisModul(this)" class="btn btn-default">删除</span></div><div><input type="hidden" class="sort" name="sort[]" value="1"><input type="hidden" class="sort" name="cat_id" value="' . $cat_id . '"><input type="hidden" class="sort" name="cat_value" value=""></div></li>';
        }
        echo $html;
        exit;
    }
    //获取模板列表，并拼接html代码
    public function getGoodsTemplet($cat_id) {
        $templet = Service::getInstance('goodstemplet')->getTempletByCatId($cat_id);
        $html = '';
        if ($templet) {
            foreach ($templet as $k => $v) {
                //类目模板id
                $temp_id = $v['id'];
                //获取模板图片数据
                $showImages = '';
                $templet_images = Service::getInstance('goodstemplet')->getTempletImageByTempId($temp_id);
                if (!empty($templet_images)) {

                    foreach ($templet_images as $key => $value) {
                        if (!empty($value)) {
                            $showImages .= '<li><img class="img-thumbnail" src="/upload/images/' . $value['image'] . '_image.jpg"/><div hash="' . $value['image'] . '" class="imgdel"><span class="fr btn btn-primary imgdelOld">删除</span><span class="fl btn btn-primary" onclick="showImg(JSsiblings(this.parentNode))">查看原图</span></div><input type="hidden" class="oldimg" oldimgid="' . $value['id'] . '" name="oldimgsort[][]" value=""></li>';
                        }
                    }
                }
                $is_img = '';
                $is_spec = '';
                $is_img_value = 0;
                $is_spec_value = 0;
                if ($v['is_img'] == 1) {
                    $is_img = 'checked="checked"';
                    $is_img_value = 1;
                }
                if ($v['is_spec'] == 1) {
                    $is_spec = 'checked="checked"';
                    $is_spec_value = 1;
                }
                $html .= '<li class="neworder-info modul edit_info"><h4 class="tit">模块信息</h4><hr />';
                //主标题
                $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox"><input type="hidden" class="temp_id" name="temp_id[]" value="' . $v['id'] . '"><span>主标题</span></div></div><div class="col-sm-6 mb10"><input type="text" class="form-control" name="main_title[]" value="' . $v['main_title'] . '" /></div></div>';
                //副标题

                $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox"><span>副标题</span></div></div><div class="col-sm-6 mb10"><input type="text" class="form-control" name="small_title[]" value="' . $v['small_title'] . '" /></div></div>';
                //引入图片

                $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox" ' . $is_img . ' onclick="changeState(this)"><input type="hidden" name="is_img[]" value="' . $is_img_value . '"><span>引入图片</span></div></div></div>';
                //引入规格
                $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox" ' . $is_spec . ' onclick="changeState(this)"><input type="hidden" name="is_spec[]" value="' . $is_spec_value . '"><span>引入规格</span></div></div></div>';
                //添加描述
                $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox"><span>添加描述</span></div></div><div class="col-sm-6 mb10"><textarea name="info[]">' . $v['info'] . '</textarea></div></div>';
                //添加图片
                $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><input type="checkbox"><span>添加图片</span></div></div><div class="col-md-10 col-lg-10"><ul class="row detailsimg details-group"><li><img class="img-thumbnail" src="/img/newimg.gif"/><input class="fileInput" onchange="addImg(this)" type="file" num="0" name="file[]" multiple="multiple" /></li></ul>';
                //图片展示
                $html .='<ul class="dragUl row detailsimg details-group">' . $showImages . '</ul></div></div>';
                $html .='<div class="row childpad text-right modul-info"><span onclick="deleteThisModul(this)" class="btn btn-default">删除</span></div><div><input type="hidden" class="sort" name="sort[]" value="' . $v['sort'] . '"</div></li>';
            }
        }
        return $html;
    }
    //将提交的表单数组数据按模块顺序重新排序
    private function arr_tree($data) {
        $a = array();
        foreach ($data as $k1 => $v1) {
            if (is_array($v1)) {
                foreach ($v1 as $k2 => $v2) {
                    $a[$k2][$k1] = $v2;
                }
            }
        }
        return $a;
    }
    //添加详情模板
    public function addAction() {
        //$category = Service::getInstance('category')->getCategoryTree( );
        $category1 = Service::getInstance('goods')->getGoodsCategory();
        $this->_view->category1 = $category1;
    }
    //编辑详情模板
    public function editAction() {
        $cat_id = $this->getQuery('cat_id');
        $html = $this->getGoodsTemplet($cat_id);
        $this->_view->cat_id = $cat_id;
        $this->_view->html = $html;
    }
    //保存详情模板
    public function saveAction() {

        $init = $_FILES['file'];
        foreach ($init as $k => $v) {
            foreach ($v as $k2 => $v2) {
                $file[$k][] = $v2;
            }
        }
        $cat_id = $this->getPost('cat_id');
        $cat_name = $this->getPost('cat_name');
        $data['is_img'] = $this->getPost('is_img');
        $data['is_spec'] = $this->getPost('is_spec');
        $data['main_title'] = $this->getPost('main_title');
        $data['small_title'] = $this->getPost('small_title');
        $data['info'] = $this->getPost('info');
        $data['sort'] = $this->getPost('sort');
        $data['temp_id'] = $this->getPost('temp_id');
        if ($this->getPost('delOldImg')) {
            $data['delOldImg'] = $this->getPost('delOldImg');
        }
        if ($this->getPost('oldimgsort')) {
            $data['oldimgsort'] = $this->getPost('oldimgsort');
        }
        if ($this->getPost('imgSort')) {
            $data['imgSort'] = $this->getPost('imgSort');
        }

        if ($this->getPost('delImg')) {
            $data['delImg'] = $this->getPost('delImg');
        }
        //删除模块
        if ($this->getPost('deltempid')) {
            $deltempid = $this->getPost('deltempid');
            foreach ($deltempid as $deltempk => $deltempv) {
                $res = Service::getInstance('goodstemplet')->delTempletById($deltempv);
                $imageName = Service::getInstance('goodstemplet')->getTempletImagesName($deltempv);
                $sql = "delete from `goods_templet_image` where `temp_id` = {$deltempv}";
                $res = Service::getInstance('goodstemplet')->delTempletImages($sql);
                foreach ($imageName as $imnamek => $imnamev) {
                    $imgfiles[] = APP_PATH . '/public/upload/images/' . $imnamev['image'] . '_image.jpg';
                    $imgfiles[] = APP_PATH . '/public/upload/images/' . $imnamev['image'] . '_thumb_100x100.jpg';
                    $imgfiles[] = APP_PATH . '/public/upload/images/' . $imnamev['image'] . '_thumb_400x400.jpg';
                    $imgfiles[] = APP_PATH . '/public/upload/images/' . $imnamev['image'] . '_thumb_800x800.jpg';
                    foreach ($imgfiles as $imgfilesk => $imgfilesv) {
                        if (file_exists($imgfilesv)) {
                            @unlink($imgfilesv);
                        }
                    }
                }
            }
        }
        //重组表单数据
        $data = $this->arr_tree($data);
        foreach ($data as $k => $v) {
            $v['main_title'] = trim($v['main_title']);
            $v['small_title'] = trim($v['small_title']);
            $v['info'] = htmlspecialchars($v['info']);
            //获取删除的未保存图片索引记录
            if (!empty($v['delImg'])) {
                $delImg = $v['delImg'];
                unset($v['delImg']);
            }
            //获取新增加图片的索引记录
            if (!empty($v['imgSort'])) {
                $imgSort = $v['imgSort'];
                unset($v['imgSort']);
            }
            //通过有无temp_id区分编辑还是插入    		   					
            if (isset($v['temp_id'])) {
                $temp_id = $v['temp_id'];
                //更新已有图片信息
                if (!empty($v['oldimgsort'])) {
                    $oldimgsort = $v['oldimgsort'];
                    foreach ($oldimgsort as $imgk => $imgv) {
                        $imgArr[] = explode('-', $imgv);
                    }
                    foreach ($imgArr as $imSort) {
                        $imgId = intval($imSort[0]);
                        $imgdata['temp_id'] = $temp_id;
                        $imgdata['sort'] = intval($imSort[1]) + 1;
                        $oldRes = Service::getInstance('goodstemplet')->editTempletImages($imgdata, $imgId);
                    }
                    unset($imgArr);
                }
                //删除已有图片
                if (!empty($v['delOldImg'])) {
                    $delOldImg = $v['delOldImg'];
                    foreach ($delOldImg as $delk => $delhash) {
                        $sql = "delete from `goods_templet_image` where `image` = '{$delhash}'";
                        $delRes = Service::getInstance('goodstemplet')->delTempletImages($sql);
                        $imgfiles[] = APP_PATH . '/public/upload/images/' . $delhash . '_image.jpg';
                        $imgfiles[] = APP_PATH . '/public/upload/images/' . $delhash . '_thumb_100x100.jpg';
                        $imgfiles[] = APP_PATH . '/public/upload/images/' . $delhash . '_thumb_400x400.jpg';
                        $imgfiles[] = APP_PATH . '/public/upload/images/' . $delhash . '_thumb_800x800.jpg';
                        foreach ($imgfiles as $imgfilesk => $imgfilesv) {
                            if (file_exists($imgfilesv)) {
                                @unlink($imgfilesv);
                            }
                        }
                    }
                    if (!$delRes) {
                        $this->flash('/goodstemplet/index', '删除已有图片信息失败', 2);
                    }
                }

                unset($v['delOldImg']);
                unset($v['oldimgsort']);
                unset($v['temp_id']);
                $v['edittime'] = time();
                //$v['editor'] = $_COOKIE['email'];
                $v['editor'] = $this->_developer['account'];

                $editRes = Service::getInstance('goodstemplet')->edit($v, $temp_id);
                //添加图片
                if (is_array($file['name'][$k])) {
                    //获取删除新增图片的记录            		
                    $record = array();
                    if (!empty($delImg)) {
                        foreach ($delImg as $num => $del) {
                            foreach ($del as $index => $value) {
                                $record[] = $num . '-' . $index;
                            }
                        }
                    }
                    $dir = APP_PATH . '/public/upload/images/';
                    foreach ($file['name'][$k] as $num => $img) {
                        if (!empty($img)) {
                            foreach ($img as $index => $name) {
                                $flag = $num . '-' . $index;
                                if (!$file['error'][$k][$num][$index] && !in_array($flag, $record)) {
                                    $avatar = $file['tmp_name'][$k][$num][$index];
                                    $hash = md5($avatar);
                                    $original = $dir . $hash . '_image.jpg';
                                    if (move_uploaded_file($avatar, $original)) {
                                        //对新增加的图片做页面相对应的索引记录
                                        $sortIndex = $imgSort[$num][$index];
                                        $imgs[$sortIndex][] = $hash;
                                        $size = array('100x100', '800x800');
                                        if ($sortIndex === '0')
                                            $size[] = '400x400';
                                        Service::getInstance('superadmin')->makeThumbFac($original, $size);
                                    }
                                }
                            }
                        }
                    }
                }
                //对图片所有hash值按页面索引进行排序
                if (!empty($imgs))
                    ksort($imgs);
                if (!empty($imgs)) {
                    $sql = "INSERT INTO `goods_templet_image` (`temp_id`, `image`, `sort`) VALUES";
                    foreach ($imgs as $imgsk => $imgsv) {
                        $temp = intval($imgsk + 1);
                        foreach ($imgsv as $imgsk2 => $imgsv2) {
                            $sql .="('{$temp_id}', '{$imgsv2}', $temp ),";
                        }
                    }
                    $sql = substr($sql, 0, -1);
                    $res = Service::getInstance('goodstemplet')->addTempletImages($sql);
                }
                unset($imgs);
            } else {
                $v['cat_id'] = $cat_id;
                $v['name'] = $cat_name;
                $v['createtime'] = time();
                //$v['creator'] = $_COOKIE['email'];
                $v['creator'] = $this->_developer['account'];
                //获取前一次插入的id
                $res = Service::getInstance('goodstemplet')->add($v);
                $temp_id = $res;
                //添加图片
                if (!empty($file)) {
                    //获取删除新增图片的记录            		
                    $record = array();
                    if (!empty($delImg)) {
                        foreach ($delImg as $num => $del) {
                            foreach ($del as $index => $value) {
                                $record[] = $num . '-' . $index;
                            }
                        }
                    }
                    $dir = APP_PATH . '/public/upload/images/';
                    if (is_array($file['name'][$k])) {
                        foreach ($file['name'][$k] as $num => $img) {
                            if (!empty($img)) {
                                foreach ($img as $index => $name) {
                                    $flag = $num . '-' . $index;
                                    if (!$file['error'][$k][$num][$index] && !in_array($flag, $record)) {
                                        $avatar = $file['tmp_name'][$k][$num][$index];
                                        $hash = md5($avatar);
                                        $original = $dir . $hash . '_image.jpg';
                                        if (move_uploaded_file($avatar, $original)) {
                                            //对新增加的图片做页面相对应的索引记录
                                            $sortIndex = $imgSort[$num][$index];
                                            $imgs[$sortIndex][] = $hash;
                                            $size = array('100x100', '800x800');
                                            if ($sortIndex === '0')
                                                $size[] = '400x400';
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
                if (!empty($imgs)) {
                    $sql = "INSERT INTO `goods_templet_image` (`temp_id`, `image`, `sort`) VALUES";
                    foreach ($imgs as $imgsk => $imgsv) {
                        $temp = intval($imgsk + 1);
                        foreach ($imgsv as $imgsk2 => $imgsv2) {
                            $sql .="('{$temp_id}', '{$imgsv2}', $temp ),";
                        }
                    }
                    $sql = substr($sql, 0, -1);
                    $res = Service::getInstance('goodstemplet')->addTempletImages($sql);
                }
                unset($imgs);
            }
        }
        $this->flash('/goodstemplet/index', '保存成功', 0.5);
        exit;
    }
    //删除该类目id的详情模板
    public function delAction() {
        $cat_id = $this->getQuery('cat_id');
        if (isset($cat_id)) {
            $temp_id = Service::getInstance('goodstemplet')->getTempletIdByCatId($cat_id);
            if (Service::getInstance('goodstemplet')->delTempletByCatId($cat_id)) {
                foreach ($temp_id as $tempk => $tempv) {
                    $imageName = Service::getInstance('goodstemplet')->getTempletImagesName($tempv['id']);
                    $sql = "delete from `goods_templet_image` where `temp_id` = {$tempv['id']}";
                    if (Service::getInstance('goodstemplet')->delTempletImages($sql)) {
                        foreach ($imageName as $imnamek => $imnamev) {
                            $imgfiles[] = APP_PATH . '/public/upload/images/' . $imnamev['image'] . '_image.jpg';
                            $imgfiles[] = APP_PATH . '/public/upload/images/' . $imnamev['image'] . '_thumb_100x100.jpg';
                            $imgfiles[] = APP_PATH . '/public/upload/images/' . $imnamev['image'] . '_thumb_400x400.jpg';
                            $imgfiles[] = APP_PATH . '/public/upload/images/' . $imnamev['image'] . '_thumb_800x800.jpg';
                            foreach ($imgfiles as $imgfilesk => $imgfilesv) {
                                if (file_exists($imgfilesv)) {
                                    @unlink($imgfilesv);
                                }
                            }
                        }
                    }
                }
                $this->flash('/goodstemplet/index', '删除成功', 0.5);
                //$this->respon(1,'删除成功');
            } else {
                $this->flash('/goodstemplet/index', '删除失败', 2);
                //$this->respon(0,'删除失败');
            }
        }
    }

}
