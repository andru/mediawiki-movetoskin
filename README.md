#Move To Skin (MediaWiki Extension)#
====================

A MediaWiki extension which allows article content to be moved to the skin. Useful for skin designers looking for a way to have control over their skin from a wiki article without resorting to Javascript hacks.

Copy to extensions and include MoveToSkin.php in LocalSettings in the usual way.

##Usage in a wiki article##
In your articles, use the parser function {{#movetoskin:target|content}}.
* The first argument, *target* is a unique name you can use in the skin to show this content.
* The second argument *content* is the content you want to move.
You can use the same target mutiple times.

##Usage in the skin##
Use the static method MoveToSkin::getContent() in your skin to grab an array of all the content, indexed by target name. You can then use this to output the content wherever you choose.

Eg.

	$content = MoveToSkin::getContent();
	if(isset($content['target name'])){
		foreach($content['target name'] as $c){
			echo '<div class="something">'.$c.'</div>';
		}
	}
