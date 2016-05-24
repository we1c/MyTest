<?php

// 未开启catch exception的情况下不会调用此controller

class ErrorController extends BaseController {

    public function errorAction($exception) {

        switch($exception->getCode()) {
            case YAF_ERR_NOTFOUND_CONTROLLER:
            case YAF_ERR_NOTFOUND_MODULE:
            case YAF_ERR_NOTFOUND_ACTION:

            $this->setError(404, 'Not Found');
            break;

            default:
                $this->setError(500, $e->getMessage());
            break;
        }
    }
}