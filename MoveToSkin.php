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

class MoveToSkin {

	public static $content = array();

	public static function parserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'movetoskin', 'MoveToSkin::parserFunction' );

		return true;
	}

	public static function languageGetMagic( &$magicWords ) {
		$magicWords[ 'movetoskin' ] = array( 0, 'movetoskin' );

		return true;
	}

	public static function parserFunction( $parser, $name = '', $content = '' ) {
		// we have to wrap the inner content within <p> tags, because MW screws up otherwise by
		// placing a <p> tag before and after with related closing and opening tags within php's
		// DOM library doesn't like that and will swap the order of the first closing </p> and the
		// closing </movetoskin> - stranding everything after that outside the <movetoskin> block. Lame.
		// $content = $parser->recursiveTagParse($content);
		$content = '<ins data-type="movetoskin" data-name="' . $name . '">' . $content . '</ins>';

		return [ $content, 'noparse' => false, 'isHTML' => false ];
	}

	public static function moveContent( &$out, &$html ) {
		if ( empty( $html ) ) {
			return $html;
		}
		if ( preg_match_all( '~<ins data-type="movetoskin" data-name="([\w:-]+)">([\S\s]*?)<\/ins>~m', $html, $matches, PREG_SET_ORDER ) ) {
			foreach ( $matches as $match ) {
				if ( !isset( self::$content[ $match[1] ] ) ) {
					self::$content[ $match[1] ] = [];
				}
				array_push( self::$content[ $match[1] ], $match[2] );
				$html = str_replace( $match[0], '', $html );
			}
		}
		return true;
	}

	public static function hasContent( $target ) {
		if ( isset( self::$content[ $target ] ) ) {
			return true;
		}

		return false;
	}

	public static function getContent( $target = null ) {
		if ( $target !== null ) {
			if ( self::hasContent( $target ) ) {
				return self::$content[ $target ];
			}

			return array();
		} else {
			return self::$content;
		}
	}

}
