<?php
class Queryorder2{
    
    private $EBusinessID = '1256305';
    private $AppKey = '8491664a-a8cc-42cc-87d9-de92412dec8b';
    private $ReqURL = 'http://api.kdniao.cc/Ebusiness/EbusinessOrderHandle.aspx';
    private $redis;

    public function __construct($redis)
    {
        $this->redis = $redis;
    }

    public function orderTracesSubByJson($shipper, $logisticCode, $deadtime)
    {
        $shipperCode = $this->get_com($shipper);
        $key = $shipperCode . $logisticCode;

        $requestData="{'rderCode':'',
                    'ShipperCode':'".$shipperCode."',
                    'LogisticCode':'".$logisticCode."'
                    }";
        
        $datas = array(
            'EBusinessID' => $this->EBusinessID,
            'RequestType' => '1002',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );

        if( $this->redis->exists( $key ) )
        {
            $result = $this->redis->get( $key );
        }
        else
        {
            $datas['DataSign'] = $this->encrypt($requestData, $this->AppKey);
            $result = $this->sendPost($this->ReqURL, $datas);
            $this->redis->setex($key, $deadtime, $result);
        }
        
        return $result;
    }
    
    /**
     *  post提交数据 
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据 
     * @return url响应返回的html
     */
    private function sendPost($url, $datas) 
    {
        $temps = array();   
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);      
        }   
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], 80);
        fwrite($fd, $httpheader);
        $gets = ""; 
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);  
        
        return $gets;
    }

    /**
    * 电商Sign签名生成
    * @param data 内容   
    * @param appkey Appkey
    * @return DataSign签名
    */
    private function encrypt($data, $appkey) 
    {
        return urlencode(base64_encode(md5($data.$appkey)));
    }

    /**
    * 获取快递公司代码
    * @param com 公司名
    * @return 公司代码
    */
    private function get_com($com)
    {
        $arr = array(
            '顺丰快递'   =>'SF',
            '申通快递'   =>'STO',
            'EMS'    =>'EMS',
            'ems国际件' =>'EMS',
            '中国邮政'   =>'YZPY',
            '圆通快递'   =>'YTO',
            '德邦'     =>'DBL',
            '韵达快递'   =>'YD',
            '百世汇通'   =>'HTKY',
            '中通快递'   =>'ZTO',
            '国通快递'   =>'GTO',
        );
        
        if(isset($arr[$com]))
        {
            return $arr[$com];
        }
        else
        {
            return FALSE;
        }
    }

}