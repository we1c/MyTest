<?php
class ResponsePlugin extends Yaf_Plugin_Abstract
{
    public function preDispatch( Yaf_Request_Abstract $request, Yaf_Response_Cli $response)
    {
        $response->data = array();
        $response->messages = array();
        $response->pushMessages = array();
        $response->code = 200;
        $response->error = '';
    }

    public function postDispatch( Yaf_Request_Abstract $request, Yaf_Response_Cli $response)
    {
        //
    }
}
?>