<?php

class UserPlugin extends Yaf_Plugin_Abstract
{
 
    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {        
    
        $whiteList = array(
            'index' => '*',
            'shop' => '*',
            'good' => '*',
            'user' => array('login', 'logout'),
        );

        $controller = strtolower($request->getControllerName());
        $action = $request->getActionName();

        $user = Yaf_Registry::get('user');

        
        foreach ($whiteList as $c => $actions) {
            if ($c == $controller && $actions == '*') {
                return;
            } else if ($c == $controller) {
                if (in_array($action, $actions)) {
                    return;
                }
                break;
            }
        }


        if (!$user) {
            header('Location: /user/login/');
            exit;
        }       


    }
}