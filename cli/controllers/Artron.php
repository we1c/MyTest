<?php

define("URL",  'http://ebookadmin.artron.net:8387');


class ArtronController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    public function defaultAction()
    {
        $this->setResponse(array('wawa' => 'sdfds'));
    }

    public function testAction(){
        $sql = "SELECT * FROM goods LIMIT 0,5";
        $result = $this->db->fetchAll($sql);
        var_dump($result);
    }

    public function mysql1redis1pushAction(){

        while(1){
            //出列
            $data=json_decode($this->redis->rpop('time_list'),true);
            //如果队列为空，终止此次循环，进行下次
            if(empty($data)) continue;
            //判断有没有对该信息操作过
            $key = md5($data['id']);
            if($this->redis->exists($key)){
                $new = json_decode($this->redis->get($key),true);
                if($new['type'] == 'del'){
                    continue;
                }else if($new['type'] == 'upd'){
                    foreach($new as $k => $v){
                        $data[$k] = $v;
                    }
                }
                $this->redis->del($key);
            }
            //根据不同的键值 'do'=>'完成事件' ,做相应的时间逻辑处理
            if( $data['do'] == 'pre_goods' ){
                $startTime = $data['startTime'];
                $endTime = $data['endTime'];
                $now_time = time();
                $start_time = strtotime($startTime);
                $end_time   = strtotime($endTime);
                
                if( $now_time >= $start_time && $now_time<$end_time ){
                    if($data['start'] == 'no'){
                        $data = $this->_doStart( $data );
                        var_dump(json_encode($data));
                    }
                }elseif( $now_time >= $end_time){
                    $res = $this->_doEnd( $data );
                    if($res) continue;
                }
            }else {
                //暂时没有
            }
            
            $this->redis->lpush('time_list',json_encode($data));
            unset($data);
        }
    }

    private function _doStart( $data ){

        $gid = $data['gid'];
        $puid = $data['id'];

        $res_g = $this->db->update( 'goods', array('status'=>6),' id = '. $gid );
        $res_p = $this->db->update('push', array('status'=>2),' id = '. $puid );

        if( $res_g >= 0 && $res_p >= 0 ){
            //锁定成功，推送消息：
            $data['action'] = $data['cname'].' 锁定商品';

            $info = $this->_getPushInfoByGid( $gid );

            $data = array_merge( $data,$info );
            $res=Service::getInstance('weichat') -> doPushSend( $data );
            if($res){
                $data['start'] = 'yes';
            }
            return $data;
        }
    }

    private function _getPushInfoByGid( $gid ){

        $sql = " SELECT devId,channel FROM push WHERE goodsId = {$gid} ";
        $result = $this->db->fetchAll( $sql );

        $ids = array();
        $channel = array();
        foreach( $result as $row ){
            $ids[] = $row['devId'];
            $channel[] = $row['channel'];
        }

        $devIds = implode(',',$ids);
        $sql = " SELECT openId FROM developers WHERE id IN ( ".$devIds." ) ";
        $openIds = $this->db->fetchAll( $sql );

        $data['openIds'] = array();
        foreach( $openIds as $row ){
            $data['openIds'][] = $row['openId'];
        }

        $channels = implode(',',$channel);
        $sql = " SELECT name FROM channel WHERE id IN ( ".$channels." ) ";
        $channels = $this->db->fetchAll( $sql );

        $cnames = '';
        foreach( $channels as $row ){
            $cnames .= $row['name'].' | ';
        }
        $data['cnames'] = trim($cnames,' | ');

        return $data;
    }

    private function _doEnd( $data ){
        $gid = $data['gid'];
        $puid = $data['id'];
        $preid = $data['preId'];

        $sql = " SELECT status FROM goods WHERE id = {$gid}";
        $status=$this->db->fetchOne( $sql );
        //这里暂时实现，后面可以用事务进行控制这些逻辑同时成立
        if( $status == 6 ){
            //未购买，仍然是锁定状态，修改商品表和推送表状态，记录流拍次数
            $res1 = $this->db->update( 'goods',array('status'=>1),' id = '.$gid );
            $res2 = $this->db->update( 'push',array('status'=>0),' id = '.$puid );
            if( $res1>=0 && $res2>=0 ){
                //删除预售记录：
                $res3 = $this->db->delete( 'presell' ,' id = '.intval($preid) );
                if( $res3 ){
                    $data['action'] = $data['cname'].' 商品流拍';
                }
            }
        }
        
        if( $status == 2 ){
            $data['action'] = $data['cname'].' 售出商品';
        }

        $res =Service::getInstance('weichat') -> doPushSend( $data );
        if( $res ) return true;
        return false;
    }



    public function touchAction()
    {
        $this->db->query('SHOW STATUS;');
    }

    public function mongo2mysqlAction()
    {

        $table = Yaf_Registry::get('table');
        $mdb = $this->mongo->paicang;
        
        $tables = array( 
            'base_ebooks' => 1 , 
            'base_goods'  => 1, 
            'base_series'  => 1, 
            'data_ebooks_composition' => 1,
            'data_special'  => 1
        );
        

        if ( !isset( $tables[$table] ) ) return;

        $data = $mdb->$table->find(  );
        
        foreach ( $data as $document ) 
        {
            unset( $document['_id'] );
            //print_r( $document );
            foreach ( $document as $key => $value ) {
                echo( $table . " : ".$key . "\n");
                //$this->addfields( $table , $key , $value );
            }
            $this->db->insert( $table , $document);
            //exit;
        }
    }

    public function createTable( $name ) 
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$name.'` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1' ;
        return $this->db->query( $sql );
    }

    public function addfields( $table , $key , $v )
    {
        //$this->createTable( $table );
        $fields = $this->db->desc( $table );
        if ( isset( $fields[$key] ) ) return ;
        $sql = "";

        if ( $key == "" ) return ;

        if ( is_float( $v ) )
        {
            $sql = 'ALTER TABLE  `'.$table.'` ADD  `'.$key.'` FLOAT( 8 ) UNSIGNED NOT NULL';
        }
        else if ( intval( $v ) )
        {
            $sql = 'ALTER TABLE  `'.$table.'` ADD  `'.$key.'` INT( 10 ) UNSIGNED NOT NULL';
        }
        else if ( is_int( $v ) )
        {
            $sql = 'ALTER TABLE  `'.$table.'` ADD  `'.$key.'` INT( 10 ) UNSIGNED NOT NULL';
        }
        else
        {
            $sql = 'ALTER TABLE  `'.$table.'` ADD  `'.$key.'` TEXT CHARACTER 
            SET utf8 COLLATE utf8_general_ci NOT NULL';
        }

        $this->db->query( $sql );

    }


    public function mysql2redis4errAction( )
    {
        exit("sss");
        $perpage = 1000;
        $total = $this->db->fetchOne("SELECT COUNT(*) FROM files_err6 ");
        $p = ceil( $total/$perpage );

        $i = 1;
        for ( $page = 1 ; $page <=  $p ; $page++ )
        {
            $sql = "SELECT url FROM files_err6  ORDER BY id desc  ";
            $query = $this->db->query( $sql . $this->db->buildLimit( intval( $page ), intval( $perpage) ) );
            
            while ($row = $query->fetch()) 
            {
                if ( $row['url'] )
                {
                    $this->addRedis( $row['url'] );
                }

                echo ("number: ".$i ."\n");
                $i++;
            }            
        }

        echo "ok.\n";

    }

    public function mysql2redisAction()
    {

        $perpage = 1000;
        $total = $this->db->fetchOne("SELECT COUNT(*) FROM base_goods ");
        $p = ceil( $total/$perpage );

        $i = 1;
        for ( $page = 1 ; $page <=  $p ; $page++ )
        {
            $sql = "SELECT logourl,picurl FROM base_goods  ORDER BY id desc  ";
            $query = $this->db->query( $sql . $this->db->buildLimit( intval( $page ), intval( $perpage) ) );
            
            while ($row = $query->fetch()) 
            {
                if ( $row['logourl'] )
                {
                    $this->addRedis( $row['logourl'] );
                }
                if ( $row['picurl'] )
                {
                    $this->addRedis( $row['picurl'] );
                }
                echo ("number: ".$i ."\n");
                $i++;
            }            
        }

        echo "ok.\n";


    }

    public function mongo2redisAction()
    {

        $db = $this->mongo->paicang;
        $data = $db->base_goods->find(  )->sort( array( '_id'=>-1 ) );
        foreach ( $data as $document ) 
        {
            if ( $document['logourl'])
            {
                $this->addRedis( $document['logourl'] );
            }
            if ($document['picurl'])
            {
                $this->addRedis( $document['picurl'] );
            }
        }


    }

    public function addRedis( $data )
    {
        try
        {
            $this->redis->LPUSH( 'new-pic-url-get-s-data-mysqls-tts-625',$data ); 
            echo( $data . "\n" );
        }
        catch(Exception $e)
        { 
            //echo $e->getMessage(); 
        } 
    }

    public function downloadAction(  )
    {
        //exit( $this->randip() );
        $row =  $this->redis->LPOP( 'new-pic-url-get-s-data-mysqls-tts-625' ) ; 
        
        while ( $row ) 
        {
            echo ( $row . "\n" );
            $hash = md5( $row );
            $path = $this->getDir( DATA_PATH . "/data" , $hash ) ;
            
            $file = $path . $hash.".jpg";
            //echo $file."\n";
            if ( !file_exists( $file ) )
            {
                $co = $this->getfile( $row );
                //exit("-000000\n");
                file_put_contents( $path . $hash.".jpg" , $co );
                if ( $co )
                {
                    $res = array('hash'=>$hash);
                    $this->db->insert('files_tmp',$res);
                    echo "ok\n";
                    //exit;
                }
                else
                {
                    $rs = array('url'=>$row);
                    $this->db->insert('files_err7',$rs);
                    //exit;
                }
            }
            else
            {
                $filesize = abs( filesize( $file ) );
                //echo("-000000\n");
                //echo( "====".$filesize . "\n" );
                //exit;
                if ( !$filesize )
                {
                    $co = $this->getfile( $row );
                    file_put_contents( $path . $hash.".jpg" , $co );
                    if ( $co )
                    {
                        $res = array('hash'=>$hash);
                        $this->db->insert('files_tmp',$res);
                        echo "ok\n";
                        //exit;
                    }
                    else
                    {
                        $rs = array('url'=>$row);
                        $this->db->insert('files_err7',$rs);
                        //exit;
                    }                    
                }
            }
            
            $row =  $this->redis->LPOP( 'new-pic-url-get-s-data-mysqls-tts-625' ) ; 
        }        
    }


    public function randip()
    {
        $s = file_get_contents( ROOT_PATH . "/ip" );
        $arr = explode("\n", $s);
        $key = array_rand( $arr , 1 );
        echo( $arr[$key]."\n" );
        //exit;
        return $arr[$key];
    }

    public function getfile( $url )
    {
        $p = array( 
            'http' => array(
                        'timeout' => 5, 
                        'proxy' => 'tcp://'.$this->randip(), 
                        'request_fulluri' => True,
                        ) 
        );
        $ctx = stream_context_create( $p ); 
        $result = file_get_contents($url, False, $ctx);
        return $result;
    }

    public function makedir($dir, $mode = 0755) 
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return true;
        if (!$this->makedir(dirname($dir), $mode)) return true;
        return @mkdir($dir, $mode);
    }

    public function getDir( $base , $hash )
    {
        if ( !$hash ) return ;
        $path = $base . "/{$hash[0]}$hash[1]/{$hash[2]}{$hash[3]}/";
        if ( !file_exists( $path ) ) 
        {
            $this->makedir($path ,0777);
        }
        return $path;
    }

    public function auctionAction()
    {
    	$companys = $this->getCompanyList();

    	foreach ( $companys as $key => $value ) 
    	{
    		$this->auction( $value['id'] );
    	}
    }

    public function ebooksAction()
    {
        $db = $this->mongo->paicang;
        $data = $db->base_series->find()->sort( array( 'id'=>1 ) );
        try
        {
	        foreach ( $data as $document ) 
	        {
	            for ( $i=1; $i < 6; $i++ ) 
	    		{ 
	    			echo("id:".$document['id']."\n");
	            	$datas = $this->SearchEbooksBySerie( $document['id'], $i );
		    		$datas = json_decode( $datas , true );
		    		if ( !isset( $datas['datas'] ) ) break;

		    		foreach ( $datas['datas'] as $key => $rows ) 
		    		{
		    			foreach ( $rows as $k => $row ) 
		    			{
		    				$this->add( $key , $row );
		    			}
		    		}
	        	}
	        }
        }
        catch(Exception $e) 
        {
        	echo ("err \n");
        }

    }

    public function goodsAction()
    {
        $db = $this->mongo->paicang;
        $where=array('_id'=>array('$ne'=>0,'$lt'=>50,'$exists'=>true));
        $data = $db->base_ebooks->find()->sort( array( '_id'=>-1 ) );
        try
        {
	        foreach ( $data as $document ) 
	        {
	            for ( $i=1; $i < 6; $i++ ) 
	    		{ 
	    			echo("id:".$document['id']."\n");
	            	$datas = $this->SearchGoodsByEbook( $document['id'], $i );
		    		$datas = json_decode( $datas , true );
		    		if ( !isset( $datas['datas'] ) ) break;

		    		foreach ( $datas['datas'] as $key => $rows ) 
		    		{
		    			foreach ( $rows as $k => $row ) 
		    			{
		    				$this->add( $key , $row );
		    			}
		    		}
	        	}
	        }
        }
        catch(Exception $e) 
        {
        	echo ("err \n");
        }

    }

    public function goods2(  )
    {
        $perpage = 1000;
        $where = " where id <  12509";
        $total = $this->db->fetchOne("SELECT COUNT(*) FROM base_ebooks  " . $where);
        $p = ceil( $total/$perpage );

        for ( $page = 1 ; $page <=  $p ; $page++ )
        {
            $sql = "SELECT id FROM base_ebooks ".$where." ORDER BY id desc  ";
            $query = $this->db->query( $sql . $this->db->buildLimit( intval( $page ), intval( $perpage) ) );
            
            while ($document = $query->fetch()) 
            {
                for ( $i=1; $i < 6; $i++ ) 
                { 
                    echo("id:".$document['id']."\n");
                    $datas = $this->SearchGoodsByEbook( $document['id'], $i );
                    $datas = json_decode( $datas , true );

                    if ( !isset( $datas['datas']['base_goods'] ) ) break;
                    
                    foreach ( $datas['datas']['base_goods'] as $key => $rows ) 
                    {   
                        $rows['ebooksid'] = intval( $document['id'] );
                        $this->addMysql( 'base_goods' , $rows );
                    }
                    
                    foreach ( $datas['datas']['data_ebooks_composition'] as $key => $rows ) 
                    {   
                        $rows['ebooksid'] = intval( $document['id'] );
                        $this->addMysql( 'data_ebooks_composition' , $rows );
                    }
                }
            }            
        }

    }

    public function goods2mysqlAction( )
    {

        $this->goods2();
        return;
        $db = $this->mongo->paicang;
        $where=array('_id'=>array('$ne'=>0,'$lt'=>12509,'$exists'=>true));        
        $data = $db->base_ebooks->find( $where )->sort( array( '_id'=>-1 ) );
        try
        {
            foreach ( $data as $document ) 
            {
                for ( $i=1; $i < 6; $i++ ) 
                { 
                    echo("id:".$document['id']."\n");
                    $datas = $this->SearchGoodsByEbook( $document['id'], $i );
                    $datas = json_decode( $datas , true );

                    if ( !isset( $datas['datas']['base_goods'] ) ) break;
                    
                    foreach ( $datas['datas']['base_goods'] as $key => $rows ) 
                    {   
                        $rows['ebooksid'] = intval( $document['id'] );
                        $this->addMysql( 'base_goods' , $rows );
                    }
                    
                    foreach ( $datas['datas']['data_ebooks_composition'] as $key => $rows ) 
                    {   
                        $rows['ebooksid'] = intval( $document['id'] );
                        $this->addMysql( 'data_ebooks_composition' , $rows );
                    }
                }
            }
        }
        catch(Exception $e) 
        {
            print_r( $e );
        }
    }

    public function auction( $companyid )
    {
    	for ( $i=1; $i < 10; $i++ ) 
    	{ 
    		$data = $this->SearchSeries( $companyid , $i );
    		$data = json_decode( $data , true );
    		if ( !isset( $data['datas'] ) ) break;

    		foreach ( $data['datas'] as $key => $rows ) 
    		{
    			foreach ( $rows as $k => $row ) 
    			{
    				$this->add( $key , $row );
    			}
    		}
  
    	}
    }

    public function addMysql( $table , $row )
    {
        $id = $this->db->fetchOne( "SELECT id FROM  " . $table . " where id = " . intval( $row['id'] )  );
        if ( $id ) return ;

        $this->db->insert( $table , $row );
    }

    public function add( $table , $row )
    {
    	if ( !isset( $row['id']  ) )
    	{
    		if ( !isset( $row['itemid'] ) ) return ;
    	} 
    	echo(" add {$table} \n");
    	$row['_id'] = isset($row['id']) ? $row['id'] : $row['itemid'];
    	$this->mongo->paicang->$table->insert( $row );

    }

    public function addAuction( $value )
    {

        $fields = $this->db->desc( 'artron_auction' );

        if ( !isset( $value['id'] ) ) continue;
        
        foreach ( $value as $k => $v ) 
        {                
            if ( $v  == "" ) unset( $value[$k] );
            if ( !isset( $fields[$k] ) ) unset( $value[$k] );
        }
        $co = $this->db->fetchRow('SELECT * FROM artron_auction WHERE id = ?', $value['id']);
        if ( !$co )
        {
        	echo "add artron_auction ... \n ";
            $this->db->insert('artron_auction', $value);
        }

    }


    public function getCompanyList()
    {
    	return $this->db->fetchAll('SELECT * FROM artron_company ');
    }

    public function SearchSeries( $companyid = '' , $pg = 1 , $appcode = 'tulu' )
    {
    	$url = URL.'/'.$appcode.'/SearchSeries.ashx?';
    	$url .= 'appcode='.$appcode.'&opt=0&keywords=&companyid='.$companyid;
    	$url .= '&city=&bdate=&edate=&bpredate=&epredate=&sorts=-1&pg='.$pg.'&sz=100000';

    	return file_get_contents( $url );
    }

    public function SearchEbooksBySerie( $sid = '' , $pg = 1 , $appcode = 'tulu' )
    {
		$url = URL.'/'.$appcode.'/SearchEbooksBySerie.ashx?';
		$url .= 'appcode='.$appcode.'&keywords=&serieId='.$sid.'&sorts=1&pg='.$pg.'&sz=21';
		return file_get_contents( $url );
    }

    public function SearchGoodsByEbook( $bookid = '' , $pg = 1 , $appcode = 'tulu' )
    {
 		$url = URL.'/'.$appcode.'/SearchGoodsByEbook.ashx?';
 		$url .= '?appcode='.$appcode.'&keywords=&ebooksId='.$bookid.'&sorts=1&pg='.$pg.'&sz=21';
 		return file_get_contents( $url );
    }

}