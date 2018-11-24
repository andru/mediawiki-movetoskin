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

	public static $content = [];

	/**
	 * @param Parser &$parser
	 *
	 * @throws MWException
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'movetoskin', 'MoveToSkin::parserFunction' );
	}

	/**
	 * @param Parser $parser
	 * @param string $name
	 * @param string $content
	 *
	 * @return array
	 */
	public static function parserFunction( $parser, $name = '', $content = '' ) {
		// we have to wrap the inner content within <p> tags, because MW screws up otherwise by
		// placing a <p> tag before and after with related closing and opening tags within php's
		// DOM library doesn't like that and will swap the order of the first closing </p> and the
		// closing </movetoskin> - stranding everything after that outside the <movetoskin> block. Lame.
		// $content = $parser->recursiveTagParse($content);
		$content = '<ins data-type="movetoskin" data-name="' . $name . '">' . $content . '</ins>';

		return [ $content, 'noparse' => false, 'isHTML' => false ];
	}

	/**
	 * Move content
	 *
	 * @param OutputPage &$out
	 * @param string &$html
	 */
	public static function onOutputPageBeforeHTML( OutputPage &$out, &$html ) {
		if ( !empty( $html ) &&
			 preg_match_all(
				'~<ins data-type="movetoskin" data-name="([\w:-]+)">([\S\s]*?)<\/ins>~m',
				$html, $matches, PREG_SET_ORDER
			 )
		) {
			foreach ( $matches as $match ) {
				if ( !isset( self::$content[ $match[1] ] ) ) {
					self::$content[ $match[1] ] = [];
				}
				array_push( self::$content[ $match[1] ], $match[2] );
				$html = str_replace( $match[0], '', $html );
			}
		}
	}

	/**
	 * @param string $target
	 *
	 * @return bool
	 */
	public static function hasContent( $target ) {
		if ( isset( self::$content[ $target ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param null $target
	 *
	 * @return array|mixed
	 */
	public static function getContent( $target = null ) {
		if ( $target !== null ) {
			if ( self::hasContent( $target ) ) {
				return self::$content[ $target ];
			}

			return [];
		} else {
			return self::$content;
		}
	}

}
