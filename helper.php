<?php
/*------------------------------------------------------------------------

# TZ Portfolio Extension

# ------------------------------------------------------------------------

# author    DuongTVTemPlaza

# copyright Copyright (C) 2012 templaza.com. All Rights Reserved.

# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL

# Websites: http://www.templaza.com

# Technical Support:  Forum - http://templaza.com/Forum

-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die;
require_once JPATH_SITE.'/components/com_content/helpers/route.php';


jimport('joomla.application.component.model');
$url = JURI::base();

abstract class modTzMultiSlideshowHelper
{
    public static function getItemsSlideshow(&$params){
        $db     = JFactory::getDbo();
        $content = $params->get('content_manager');
        $catids = $params->get('categories');
        $catid = implode(",",$catids);
        $order = $params->get('order');
        $orderby = $params->get('orderby');
        $limit = $params->get('limit');
        $img = $params->get('image');
        $imgthumb = $params->get('image_thumb');
        $item_type = $params->get('item-type','image');
        if($content == 'tz_portfolio'){
            require_once JPATH_SITE.'/components/com_tz_portfolio/helpers/route.php';
            $query =
                "SELECT *, ct.id as arid, ct.title as artitle, ct.alias as aralias, cat.alias as category_alias, tzx.images as tzimages,
          tzx.gallery as tzgallery, tzx.video as tzvideo
           FROM #__content ct LEFT JOIN #__tz_portfolio_xref_content tzx ON(ct.id = tzx.contentid)
           LEFT JOIN #__categories cat ON(ct.catid = cat.id)
          WHERE ct.catid IN($catid) AND ct.state = 1 AND tzx.type = '$item_type' ORDER BY ct.$orderby $order  LIMIT $limit
          ";
        }
//        var_dump($query); die();
        if($content == 'joomla_content'){
            $query =
                "SELECT *, ct.title as artitle, ct.alias as aralias, cat.alias as category_alias
            FROM #__content ct LEFT JOIN #__categories cat ON(ct.catid = cat.id)
            WHERE ct.catid IN($catid) AND ct.state = 1  ORDER BY ct.$orderby $order LIMIT $limit
            ";
        }

        $db -> setQuery($query);
        $items = $db->loadObjectList();
		

//        var_dump($items); die();
        if($items){
            if($content == 'tz_portfolio'){
                foreach($items as $item){
                    $item->title = $item->artitle;
                    $item->intro = modTzMultiSlideshowHelper::rip_tags($item->introtext);
                    $item->slug = $item->arid.':'.$item->aralias;
                    $item->catslug = $item->catid.':'.$item->category_alias;
                    $item->link = JRoute::_(TZ_PortfolioHelperRoute::getArticleRoute($item->slug, $item->catslug));

                    if($item->tzimages){
                        $images = $item->tzimages;
                        $nameimg = JFile::getExt($images);
                        $count = strlen($nameimg);
                        $image_name = substr($images, 0, -($count + 1));

                        $item->image = $image_name.'_'.$img.'.'.$nameimg;
                        $item->image_thumb = $image_name.'_'.$imgthumb.'.'.$nameimg;

                    }
                    if($item->tzgallery){
                        $images = $item->tzgallery;
                        $arrimages = explode("///",$images);
                        if($arrimages[0]){

                        }
                    }
                    if($item->tzvideo){

                        $images = $item->videothumb;
                        $nameimg = JFile::getExt($images);
                        $count = strlen($nameimg);
                        $image_name = substr($images, 0, -($count + 1));

                        $item->image_video = $image_name.'_'.$img.'.'.$nameimg;
                        $item->image_thumb_video = $image_name.'_'.$imgthumb.'.'.$nameimg;

                        $videos = $item->video;
                        $arrvideos = explode(":",$videos,2);
                        if($arrvideos[0]){
                            $item->video_type =  strtoupper($arrvideos[0]);
                        }
                                               if($arrvideos[1]){
                            $item->video_id =  $arrvideos[1];
                        }


                    }

                }
                return $items;
            }
            if($content == 'joomla_content'){
                foreach($items as $item){
                    $item->title = $item->artitle;
                    $item->intro = $item->introtext;
                    $item->slug = $item->arid.':'.$item->aralias;
                    $item->catslug = $item->catid.':'.$item->category_alias;

                    $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));

                    if($item->images){
                        $images = new JRegistry;
                        $images->loadString($item->images);
                        $item->images;
                        $images = json_decode($item->images);
                        $item->image_intro = $images->image_intro;
                        $item->image = $images->image_fulltext;

//                        $item->image = modTzNewsHelper::tz_resizeImgcrop($images, $width, $height,$crop );
                    }
                }
                return $items;
            }
        }
        return false;
    }


   public static function rip_tags($string) {

        // ----- remove HTML TAGs -----
        $string = preg_replace ('/<[^>]*>/', ' ', $string);

        // ----- remove control characters -----
        $string = str_replace("\r", '', $string);    // --- replace with empty space
        $string = str_replace("\n", ' ', $string);   // --- replace with space
        $string = str_replace("\t", ' ', $string);   // --- replace with space

        // ----- remove multiple spaces -----
        $string = trim(preg_replace('/ {2,}/', ' ', $string));

        return $string;

    }



}
?>
