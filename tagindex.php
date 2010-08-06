<?php
/*
 Plugin Name: Tagindex
 Plugin URI: http://www.vidisonic.com/wp-plugins/tagindex/
 Description: Tags navigation, useful for blogs with large number of tags, just create a page and use [tagindex] shortcode in the page's content to show the tags index
 Version: 1.0
 Author: Hasan Murod
 Author URI: http://www.vidisonic.com/
*/

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}


function insert_index($content) 
{
  global $wpdb;
  if (substr_count($content, '[tagindex]')>0)
  {
    $url=curPageURL();
    $q=$_GET['q'];
    $p=$_GET['p'];
    $index="";
    if($q=="")
    {
      $terms= $wpdb->get_results(
        "SELECT name, $wpdb->terms.term_id
        FROM $wpdb->terms, $wpdb->term_taxonomy
        WHERE $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id
          AND $wpdb->term_taxonomy.taxonomy='post_tag'");
      $i=0;
      foreach ($terms as $term) 
      {
        $str=strtoupper($term->name);
        $alist[$i]=$str[0];
        $i=$i+1;
      }
      $alist=array_unique($alist);
      asort($alist);
      foreach($alist as $alpha)
      {
         $index=$index.'<strong><a href="'.$url.'?q='.$alpha.'">'.$alpha.'</a></strong>, ';
      }

    }
    else
    {
      if($p=="")
      {
        $terms= $wpdb->get_results(
        "SELECT name, $wpdb->terms.term_id
        FROM $wpdb->terms, $wpdb->term_taxonomy
        WHERE $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id
          AND $wpdb->term_taxonomy.taxonomy='post_tag'
          AND name LIKE '$q%'");
      
        $i=0;
        foreach($terms as $term)
        {  
           $idarray[$i]=$term->term_id;
           $namearray[$i]=strtolower($term->name);
           $temp=explode(' ',$namearray[$i]);
           $firstwordarray[$i]=$temp[0];
           $i++;
        }
        
        $n=0; $dupcount=0;
        for($n=1;$n<$i+1;$n++)
        {
          if($firstwordarray[$n-1]!=$firstwordarray[$n])
          {
            if($dupcount==0)
              $index=$index.'<strong><a href="'.get_tag_link($idarray[$n-1]).'">'.$namearray[$n-1].'</a></strong>, ';
            else $index=$index.'<strong><a href="'.$url.'&p='.$firstwordarray[$n-1].'">'.$firstwordarray[$n-1].'~</a></strong>, ';
            $dupcount=0;
          }
          else $dupcount++;
        }
       
      }
      else 
      {
        $terms= $wpdb->get_results(
        "SELECT name, $wpdb->terms.term_id
        FROM $wpdb->terms, $wpdb->term_taxonomy
        WHERE $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id
          AND $wpdb->term_taxonomy.taxonomy='post_tag'
          AND name LIKE '$p%'"); 

        foreach($terms as $term)
        {  
          $index=$index.'<strong><a href="'.get_tag_link($term->term_id).'">'.$term->name.'</a></strong>, '; 
        }
      }
    }
    $content=str_ireplace('[tagindex]',$index,$content);
  }
  return $content;
}

add_filter('the_content','insert_index',9);

?>