<?php
class PushController extends BaseController
{
    public function init()
    {
        parent::init();
    }
    
    /**
     * APP启动极光SDK初始化
     */
    public function indexAction()
    {
        if($this->isPost() || true)
        {
            $registrationId = trim($this->getPost('registrationId'));
            $alias = trim($this->getPost('alias'));
            $tag = trim($this->getPost('tag'));

            $registrationId = '1a0018970aa5c21fe44';
            $alias = '18614079673';
            $tag = '3';

            if(empty($registrationId))
            {
                $this->respon(0, '没有正确的id');
            }

            if(empty($alias) || empty($tag))
            {
                $this->respon(0, '别名或tag为空');
            }

            $AppDevice = new AppDevice();

            $aliasResult = $AppDevice->updateDevice($registrationId, $alias);
            $tagResult = $AppDevice->updateTag($tag, array($registrationId));
            //var_dump($aliasResult);
            //var_dump($tagResult);

            $this->respon(1, $aliasResult);
        }

        return false;
    }



}