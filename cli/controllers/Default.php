<?php

class DefaultController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    public function defaultAction()
    {
        $this->setResponse(array('wawa' => 'sdfds'));
    }

    public function touchAction()
    {
        $this->db->query('SHOW STATUS;');
    }
}