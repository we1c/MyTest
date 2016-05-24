<?php

class CopywritertempletController extends BaseController {

    public function init() {
        parent::init();
    }
    //展示文案模板列表
    public function indexAction() {
        $perpage = $this->getQuery('perpage', 10);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword', '');
        $searchType = $this->getQuery('searchType', '1');
        $copywriterTemplets = Service::getInstance('copywritertemplet')->getCopywriterTempletAll($page, $perpage, $keyword, $searchType);
        $total = $copywriterTemplets['total'];
        unset($copywriterTemplets['total']);
        $this->_view->copywriterTempletList = $copywriterTemplets;
        $this->_view->perpage = $perpage;
        $this->_view->page = $page;
        $this->_view->total = $total;
        $this->_view->keyword = $keyword;
        $this->_view->searchType = $searchType;
        $pageObj = new Page($total, $perpage, $showpage, $page, '', array('copywritertemplet', 'index', 'perpage' => $perpage, 'keyword'=>$keyword, 'searchType'=>$searchType));
        $this->_view->pagebar = $pageObj->showPage();
    }

    //验证该类目是否已创建模板
    public function checkIsHasTempletAction() {
        $cat_id = intval($this->getPost('cat_id'));
        $templet = Service::getInstance('copywritertemplet')->getCopywriterTempletByCatId($cat_id);
        $html = '';
        if ($templet) {
            echo '<h4 class="tit ">你选择的类目已有模板，请到编辑修改</h4><hr />';
            exit;
        } else {
            $html .= '<li class="neworder-info modul"><h4 class="tit">模块信息</h4><hr />';
            //主标题
            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><span>引入标题</span></div></div><div class="col-sm-6 mb10">是<input type="radio" name="is_title" value="1" checked="checked"/>否<input type="radio" name="is_title" value="0"/></div></div>';
            //引入规格
            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><span>引入规格</span></div></div><div class="col-sm-6 mb10">是<input type="radio" name="is_spec" value="1" checked="checked"/>否<input type="radio" name="is_spec" value="0"/></div></div>';
            //文案描述
            $html .='<div class="row childpad"><div class="col-lg-2 col-md-3 mb10"><div class="checkbox-group"><span>文案描述</span></div></div><div id="" class="col-sm-9 mb10"><script id="intro" type="text/plain" name="intro" style="height:200px;"></script></div></div>';

            $html .='<div><input type="hidden" id="checkSelect" value="selected"></div></li>';
            echo $html;
            exit;
        }
    }

    //创建文案模板
    public function addAction() {
        if ($this->isPost()) {
            $data['name'] = $this->getPost('cat_name');
            $data['is_title'] = intval($this->getPost('is_title'));
            $data['is_spec'] = intval($this->getPost('is_spec'));
            $data['intro'] = htmlspecialchars($this->getPost('intro'));
            $data['cat_id'] = $this->getPost('cat_id');
            $data['createtime'] = time();
            $data['creator'] = $this->_developer['account'];
            $res = Service::getInstance('copywritertemplet')->add($data);
            if ($res) {
                $this->flash('/copywritertemplet/index', '保存成功', 0.5);
            } else {
                $this->flash('/copywritertemplet/index', '保存失败', 1);
            }
        }
        $category1 = Service::getInstance('goods')->getGoodsCategory();
        $this->_view->category1 = $category1;
    }

    //编辑文案模板
    public function editAction() {

        if ($this->isPost()) {
            $id = $this->getPost('id');
            $data['is_title'] = intval($this->getPost('is_title'));
            $data['is_spec'] = intval($this->getPost('is_spec'));
            $data['intro'] = htmlspecialchars($this->getPost('intro'));
            $data['edittime'] = time();
            $data['editor'] = $this->_developer['account'];
            $res = Service::getInstance('copywritertemplet')->edit($data, $id);
            if ($res) {
                $this->flash('/copywritertemplet/index', '保存成功', 0.5);
            } else {
                $this->flash('/copywritertemplet/index', '保存失败', 1);
            }
        }
        $id = intval($this->getQuery('id'));
        $tempList = Service::getInstance('copywritertemplet')->getCopywriterTempletById($id);
        $this->_view->id = $id;
        $this->_view->tempList = $tempList;
    }

    //删除文案模板
    public function delAction() {
        $id = $this->getQuery('id');
        $res = Service::getInstance('copywritertemplet')->del($id);
        if ($res) {
            $this->flash('/copywritertemplet/index', '删除成功', 0.5);
        } else {
            $this->flash('/copywritertemplet/index', '删除失败', 1);
        }
    }

    //创建文案
    public function createCopywriterAction() {
        $cat_id = $this->getPost('cat_id');
        $goods_name = $this->getPost('goods_name');
        $parStr = $this->getPost('parStr');
        $copywriterTemplet = Service::getInstance('copywritertemplet')->getCopywriterTempletByCatId($cat_id);
        $copywriterinfo = '';
        if (!empty($copywriterTemplet)) {
            if ($copywriterTemplet['is_title'] == 1) {
                $copywriterinfo = $goods_name . '，';
            }
            if ($copywriterTemplet['is_spec'] == 1) {
                $copywriterinfo .= $parStr . '<br/>';
            }
            if ($copywriterTemplet['intro']) {
                $copywriterinfo .= htmlspecialchars_decode($copywriterTemplet['intro']);
            }
        } else {
            $copywriterinfo .= 'nothing';
        }
        echo $copywriterinfo;
        exit;
    }

}
