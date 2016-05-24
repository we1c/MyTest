<?php

class GoodsdetailsController extends BaseController {

    public function init() {
        parent::init();
        //Yaf_Dispatcher::getInstance()->disableView();
    }

    public function indexAction() {
        $goods_id = $this->getQuery('goods_id');
        $goodsInfo = Service::getInstance('goods')->getGoodsById($goods_id);
        $goodsInfo['attribute'] = json_decode($goodsInfo['attribute'], true) ? json_decode($goodsInfo['attribute'], true) : null;
        $templet = Service::getInstance('goodstemplet')->getTempletByCatId($goodsInfo['category3']);
        $goodsImgs = Service::getInstance('goods')->getGoodsImgsByGoodsIds($goods_id, '1');
        $html = '<link href="/css/goodsModul/swiper.min.css" rel="stylesheet" type="text/css" /><link href="/css/goodsModul/style.css" rel="stylesheet" type="text/css" />';
        $html .= '';
        $html .= '<div class="gooddescribe mt20">';
        if (!empty($templet)) {
            foreach ($templet as $tempk => $tempv) {

                if ($tempv['main_title']) {
                    $html .= '<span class="gooddesTit">' . $tempv['main_title'] . '</span>';
                }
                if ($tempv['small_title']) {
                    $html .= '<p class="english">' . $tempv['small_title'] . '</p>';
                }
                if ($tempv['is_spec']) {
                    $html .= '<ul class="gooddesul">';
                    if ($goodsInfo['attribute']) {
                        foreach ($goodsInfo['attribute'] as $goodsk => $goodsv) {
                            $str = $goodsv['key']['name'];
                            if (mb_strlen($str, 'utf-8') == 1) {
                                $change = '【</em>' . $str . '<em class="fr">】';
                            } elseif (mb_strlen($str, 'utf-8') == 2) {
                                $change = '【' . mb_substr($str, 0, 1, 'utf-8') . '</em><em class="fr">' . mb_substr($str, 1, 1, 'utf-8') . '】';
                            } elseif (mb_strlen($str, 'utf-8') == 3) {
                                $change = '【' . mb_substr($str, 0, 1, 'utf-8') . '</em>' . mb_substr($str, 1, 1, 'utf-8') . '<em class="fr">' . mb_substr($str, 2, 1, 'utf-8') . '】';
                            } else {
                                $change = '【' . mb_substr($str, 0, 1, 'utf-8') . '</em>' . mb_substr($str, 1, 2, 'utf-8') . '<em class="fr">' . mb_substr($str, 3, 1, 'utf-8') . '】';
                            }
                            $html .= '<li><span class="fl descripan"><em class="fl">' . $change . '</em></span><div class="descridiv"><p>' . $goodsv['value']['name'] . '</p></div></li>';
                        }
                    }

                    $html .= '</ul>';
                }
                if ($tempv['is_img']) {
                    if ($goodsImgs) {
                        foreach ($goodsImgs as $ginfok => $ginfov) {
                            $html .= '<img src="' . $ginfov['imgurl'] . '" width="100%"/>';
                        }
                    }
                }
                $templet_images = Service::getInstance('goodstemplet')->getTempletImageByTempId($tempv['id']);
                if ($templet_images) {
                    foreach ($templet_images as $tempimgk => $tempimgv) {
                        $html .= '<img src="/upload/images/' . $tempimgv['image'] . '_image.jpg" width="100%" />';
                    }
                }
                if ($tempv['info']) {
                    $html .= '<div class="productdetailpro mt20"><p class="detpro">' . $tempv['info'] . '</p></div>';
                }
            }
        } else {
            $html .= '<span class="gooddesTit">没有找到相关预览</span>';
        }
        $html .= '</div>';
        $this->_view->html = $html;
    }

}
