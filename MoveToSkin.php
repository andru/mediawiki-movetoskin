<?php
/**
 * MoveToSkin
 * A simple plugin which allows you to move content from a wiki article 
 * to predefined areas in your skin.
 * Intended for MediaWiki Skin designers.
 * By Andru Vallance - andru@tinymighty.com
 *
 * License: GPL - http://www.gnu.org/copyleft/gpl.html
 *
 */

$wgHooks['ParserFirstCallInit'][] = 'MoveToSkin::parserFirstCallInit';
$wgHooks['LanguageGetMagic'][] = 'MoveToSkin::languageGetMagic';
/* This was hooked to ParserAfterTidy, but that isn't called when the parse skep
is skipped by retrieving from the parser cache */
$wgHooks['OutputPageBeforeHTML'][] = 'MoveToSkin::moveContent';

$wgExtensionCredits['parserhook'][] = array(
   'path' => __FILE__,
   'name' => 'Move To Skin',
   'description' => 'Move content from the article to the skin.',
   'version' => '0.1.2', 
   'author' => 'Andru Vallance',
   'url' => 'https://www.mediawiki.org/wiki/Extension:MoveToSkin'
);

class MoveToSkin{
  
  public static $content = array();
  
  public static function parserFirstCallInit(&$parser){
    $parser->setFunctionHook('movetoskin', 'MoveToSkin::parserFunction');
    return true;
  }
  
  public static function languageGetMagic(&$magicWords){
    $magicWords['movetoskin'] = array(0,'movetoskin');
    return true;
  }
  
  public static function parserFunction($parser, $name='', $content=''){
    //we have to wrap the inner content within <p> tags, because MW screws up otherwise by placing a <p> tag before and after with related closing and opening tags within
    //php's DOM library doesn't like that and will swap the order of the first closing </p> and the closing </movetoskin> - stranding everything after that outside the <movetoskin> block. Lame.
    //$content = $parser->recursiveTagParse($content);
    $content = '<div class="movetoskin" data-target="'.$name.'">'.$content.'</div>';
    return array( $content, 'noparse' => false, 'isHTML' => false );
  }
  
  public static function moveContent(&$out, &$html){
   if(empty($html))
     return true;
    //not sure why, but we have to UTF8 decode the html output... DOM Document is UTF8, so not sure why this is necessary, but without it UTF8 entities are garbled
    $html = utf8_decode($html);
    $doc = @DOMDocument::loadHTML( $html );
    //$doc->encoding = 'utf8';
    $xpath = new DomXPath($doc);
    $nodes = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' movetoskin ')]");
    //$movetoskins = $doc->getElementsByTagName('movetoskin');
    
    if($nodes->length > 0){
      foreach($nodes as $move){
        if($move->hasAttribute('data-target')){
          $target = $move->getAttribute('data-target');
          $inner_content = '';
          
          $move->removeAttribute('class');
          $move->removeAttribute('data-target');
          
          $inner_content = trim($doc->saveHTML( $move ));
          //chop off the starting <div> and ending </div> ... it's easier than trying to grab the contents via dom
          $inner_content = substr($inner_content, 5, count($inner_content)-7);
                    
          if(isset( self::$content[ $target ] )){
            array_push( self::$content[ $target ] , $inner_content );
          }else{
            self::$content[ $target ] = array(
              $inner_content 
            );
          }
          $parent = $move->parentNode;
          $parent->removeChild($move);
        }
      }
      
      $htmldoc = $doc->saveHTML( $doc->getElementsByTagName('body')->item(0) );
      if(preg_match('~<body>(.*?)<\/body>~si', $htmldoc, $match)){
        $html = $match[1];
      }
      $html = $html;
    }

    return true;
  }
    
  public static function hasContent($target){
    if(isset(self::$content[$target]))
      return true;
    return false;
  }
  
  public static function getContent($target=null){
    if($target!==null){
      if( self::hasContent($target) )
        return self::$content[$target];
      return array();
    }else{
      return self::$content;
    }
  }

}