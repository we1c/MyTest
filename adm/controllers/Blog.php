<?php
class BlogController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    { 
        $perpage = $this->getQuery('perpage',15);
        $showpage = 5;
        $page = $this->getQuery('page', 1);
        $keyword = $this->getQuery('keyword', '');
        $data = Service::getInstance('Blog')->getList($page,$perpage,$keyword);
        $this->_view->list = $data['list'];
        $this->_view->total = $data['total'];
        $this->_view->keyword = $keyword;
        $this->_view->perpage = $perpage;
        $pageObj = new Page( $data['total'],$perpage,$showpage,$page,'',array('blog','index','keyword'=>$keyword,'perpage'=>$perpage));
        $this->_view->pagebar = $pageObj -> showPage();
        // $url = $keyword ? '/blog/index?page=__page__&keyword='.$keyword : '/blog/index?page=__page__';
        // $this->_view->pagebar = Util::buildPagebar( $list['total'], $perpage, $page, $url );
    }
}