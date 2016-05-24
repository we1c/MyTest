<?php

class ShopmarketController extends BaseController{

	public function init(){
		parent::init();
	}

	public function indexAction(){
		$showType = $this->getQuery('showType','list');
		$showPage = $this->getQuery('showPage',6 );
		$page = $this->getQuery( 'page',1 );
		$perpage = $this->getQuery( 'perpage',100 );
		$keyword = $this->getQuery( 'keyword','');
		$minPrice = $this->getQuery('minPrice', '');
        $maxPrice = $this->getQuery('maxPrice', '');
        $searchType = $this->getQuery('searchType', '1');
        $developPlan = $this->getQuery('developPlan', '');
		$data = Service::getInstance('Shopmarket')->getMarketList( $page,$perpage,$keyword,$minPrice,$maxPrice,$searchType,$developPlan );
		$myRole = $this->_developer['role'];
        $myDis = $this->_developer['disId'];
		$dis = Service::getInstance('distributor')->getMyDis($myDis,$myRole);
		$this->_view->showType = $showType;
		$this->_view->keyword = $keyword;
		$this->_view->page = $page;
		$this->_view->perpage = $perpage;
		$this->_view->minPrice = $minPrice;
		$this->_view->maxPrice = $maxPrice;
		$this->_view->developPlan = $developPlan;
		$this->_view->searchType = $searchType;
		$this->_view->list = $data['list'];
		$this->_view->dis = $dis;
		$this->_view->total = $data['total'];

		$page = new Page( $data['total'],$perpage,$showPage,$page,'',array('shopmarket','index','showType'=>$showType,'showPage'=>$showPage,'perpage'=>$perpage,'keyword'=>$keyword,'searchType'=>$searchType,'developPlan'=>$developPlan,'minPrice'=>$minPrice,'maxPrice'=>$maxPrice) );
		$this->_view->pagebar = $page->showPage(  );
	}

	public function pushGoodsAction(){
		if( $this->isPost() ){
			$gid = $this->getPost('gid') + 0;
			$cid = $this->getPost('cid') + 0;
			$action = explode( '-', trim( $this->getPost('action') ) );
			$cname = trim( $this->getPost('cname') );
			if( !$gid || !$cid ){
				$this->respon( 0, '参数错误' );
			}

			$is_push = Service::getInstance('goods')->getIsPushGoods( $gid, $cid );
			if( $is_push ){
				$this->respon( 0, '商品已经推送给'.$cname.'渠道,不能重复推送！' );
			}

			$flag = true;
			if( $action[3] == 'y' ){
				$info = array(
					'goods_id'	=>$gid,
					'push_time'	=>time()
					);
				$flag = Service::getInstance('shopmarket')->addShopPush( $info );
			}

			if( $flag ){
				$data = array(
					'goodsId'		=>$gid,
					'createTime'	=>time(),
					'status'		=>$action[1],
					'channel' 		=>$cid,
					'devId'			=>Yaf_Registry::get('uid'),
					'fromWhere'		=>1
					);
				
				$res = Service::getInstance('shopmarket')->addPush( $data );
				if( $res ){
					$this->respon( 1,'推送成功' );
				}else{
					$this->respon( 0,'推送失败' );
				}
			}else{
				$this->respon( 0 , '推送失败,请重新尝试' );
			}
		}
	}

	public function batchPushAction(){
		if( $this->isPost() ){

			$goodsId = $this->getPost('goodsId');
			$cids = $this->getPost('cids') + 0;

			$ago_push = array();
			$fail_push = array();
			$success_push = array();

			foreach( $goodsId as $k => $gid ){
				$is_push = Service::getInstance('goods')->getIsPushGoods( $gid, $cids );
				if( !$is_push ){

					$flag = true;
					$is_shop_push = Service::getInstance('shopmarket')->getIsPushGoods( $gid );
					if( !$is_shop_push ){
						$info = array(
							'goods_id'	=>$gid,
							'push_time'	=>time()
							);
						$flag = Service::getInstance('shopmarket')->addShopPush( $info );
					}
					if( $flag ){
						$shopPushId = $this->db->lastInsertId();
						$ago_status = Service::getInstance('shopmarket')->getOnePush( $gid );
						if( $ago_status == '2' || $ago_status === '0' ){
							$status = 0;
						}elseif( $ago_status == '1' ){
							$status = 1;
						}else{
							$status = 1;
						}
						$data = array(
							'goodsId'		=>$gid,
							'createTime'	=>time(),
							'status'		=>$status,
							'channel' 		=>$cids,
							'devId'			=>Yaf_Registry::get('uid'),
							'fromWhere'		=>1
							);
						if( Service::getInstance('shopmarket')->addPush( $data ) ){
							$success_push[] = $gid;
						}else{
							$fail_push[] = $gid;
							Service::getInstance('shopmarket')->delShopPush( $shopPushId );
						}
					}else{
						$fail_push[] = $gid;
					}
				}else{
					$ago_push[] = $gid;
				}
			}

			$result['ago'] = $ago_push;
			$result['fail'] = $fail_push;
			$result['success'] = $success_push;

			$this->respon(1,$result);
		}
		
	}

	public function exportAction(){

		$data = Service::getInstance('shopmarket')->getExportGoods();

		$cell = range('A','Z');

		$head = array('商品ID','商品名称','商品货号','商品SKU','商品上传者','商品来源');
		$relation = array('id','name','goodsNo','code','uploader','fromWhere');

		$objPHPExcel = new PHPExcel();
		//获得当前活动单元格
		$objSheet=$objPHPExcel->getActiveSheet();
		//设置excel文件默认水平垂直方向居中
		$objSheet->getDefaultStyle()->getAlignment()->setVertical(Plugins_PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(Plugins_PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		//设置默认字体大小和格式
		$objSheet->getDefaultStyle()->getFont()->setSize(10)->setName("宋体");
		//设置第二行字体大小和加粗
		$objSheet->getStyle("A1:Z1")->getFont()->setSize(11)->setBold(true);
		$objSheet->getDefaultRowDimension()->setRowHeight(20);//设置默认行高
		/*$objSheet->getRowDimension(2)->setRowHeight(30);//设置第二行行高
		$objSheet->getRowDimension(3)->setRowHeight(30);//设置第三行行高*/

		$count = count( $head );
		for( $i=0;$i<$count;$i++ ){
			$objSheet->setCellValue( $cell[$i].'1', $head[$i] );
		}
		$len = count( $data );
		for( $i=0;$i<$len;$i++ ){
			$row = $i+2;
			foreach( $relation as $k => $v ){
				$objSheet->setCellValue( $cell[$k].$row , $data[$i][$v] );
			}
		}

		$objWriter=Plugins_PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel5');//生成excel文件
		header('Content-Type: application/vnd.ms-excel');//告诉浏览器将要输出excel03文件\
		//告诉浏览器将输出文件的名称
		header('Content-Disposition: attachment;filename=shopGoods.xls');
		header('Cache-Control: max-age=0');//禁止缓存	
		$objWriter->save("php://output");

	}


	public function editAction(){
	    if($this->isPost()) {
            $gid = intval($this->getPost('id'));
            $imgs = $this->getPost('imgs');
            if ( !$gid ) {
                $this->flash('/platf/index','参数错误');
                return false;
            }

            $file = $_FILES['file'];
            if( !empty($file) ){
                 
                //获取删除新增图片的记录
                $delImg = $this->getPost('delImg');
                $record = array();
                if( !empty($delImg) ){
                    foreach( $delImg as $num => $del ){
                        foreach( $del as $index => $value ){
                            $record[] = $num.'-'.$index;
                        }
                    }
                }
                //获取新增加图片的索引记录
                $imgSort = $this->getPost('imgSort');
                $dir = Yaf_Application::app()->getConfig()->get('image')->get('dir');
                if( !empty($file['name']) ){
                    foreach( $file['name'] as $num => $img ){
                        if( !empty($img) ){
                            foreach( $img as $index => $name ){
                                $flag = $num.'-'.$index;
                                if( !$file['error'][$num][$index] && !in_array( $flag,$record ) ){
                                    $avatar = $file['tmp_name'][$num][$index];
                                    $hash = md5( $avatar );
                                    $original = Util::getDir( $dir,$hash ).$hash.'_image.jpg';
                                    if( move_uploaded_file( $avatar , $original ) ){
                                        //对新增加的图片做页面相对应的索引记录
                                        $sortIndex = $imgSort[$num][$index];
                                        $imgs[$sortIndex] = $hash;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //对图片所有hash值按页面索引进行排序
            if( !empty($imgs) ) ksort( $imgs );
            //对头图进行判断是否有头图压缩
            if( isset($imgs[0]) ){
	            $original = Util::getDir( $dir,$imgs[0] ).$imgs[0].'_image.jpg';
	            $oneThumb = str_replace('_image.','_thumb_400x400.',$original);
	            if( !file_exists( $oneThumb ) ){
	                Service::getInstance('superadmin')->makeThumbFac( $original ,array( '400x400' ) );
	            }
            }
            //获取删除已经保存的记录
            $delOldImg = $this->getPost('delOldImg');
            if( !empty($delOldImg) ){
                foreach( $delOldImg as $k => $hash ){
                    $imgType = array('_image','_thumb_100x100','_thumb_400x400','_thumb_800x800');
                    foreach( $imgType as $i => $t ){
                        $file = Util::getDir( $imgDir,$hash ).$hash.$t.'.jpg';
                        if( file_exists( $file ) ){
                            @unlink($file);
                        }
                    }
                }
            }
            if( is_array( $imgs ) ) {
                $sql = "INSERT INTO `goods_image` (`goodsId`, `image`, `sort`) VALUES";
                foreach ($imgs as $k=>$v){
                    $temp = intval($k+1);
                    $sql .="('{$gid}', '{$v}', $temp ),";
                }
                $sql = substr($sql, 0,-1);
                Service::getInstance('goods')->editgoods_images($sql,$gid);
            }else{
                Service::getInstance('goods')->delgoods_images( $gid );
            }
            $this->flash('/shopmarket/index','编辑成功');
	    } else {
	       $id = $this->getQuery('id',0);
	       $Info = Service::getInstance('goods')->getGoodsInfoById($id);
	       $category1 = Service::getInstance('goods')->getGoodsCategory();
	       if($Info['category1'] && $Info['category2']) {
	           $category2 = Service::getInstance('goods')->getCat($Info['category1']);
	           $category3 = Service::getInstance('goods')->getCat($Info['category2']);
	       } else {
	           $category2 = array();
	           $category3 = array();
	       }
    	   $shop = Service::getInstance('shop')->getList();
	       $arr = $Info['attribute'];
	       if ( is_array($arr) ) {
	           foreach ( $arr as $k=>$v ) {
	               if( $v['value']['id'] ) {
	                   $data = Service::getInstance('goods')->getParamByPid($v['key']['id']);
	                   $arr[$k]['value']['param'] = $data;
	               }
	           }
	       }
	       $Info['attribute'] = $arr ? $arr : '';
    	   $this->_view->shop = $shop;
	       $this->_view->Info = $Info;
	       $this->_view->category1 = $category1;
	       $this->_view->category2 = $category2;
	       $this->_view->category3 = $category3;
	    }
	}

}