<?php
/**
 * @package       WT Content Image gallery
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

/**
 * Note! This is a demo layout that shows how to access the list of images in PHP.
 * It is not intended for use on websites and serves simply as a sample.
 *
 * @var $context  string  Like 'com_content.article'
 * @var $images   array Images array
 * @var $iterator int regex iterator - 0, 1, 2 etc. Shortcode number
 *
 * For your images gallery layouts your can make unique id or data-attributes
 * combining $context and $iterator.
 * For example: <img src="'.$image['img_src'].'" id="'.$context.'-'.$iterator.'" />
 * You will receive: <img src="images/you-image.webp" id="com_content.article-0" />
 */

//echo '<pre>';
//print_r($images);
//echo '</pre>';

/**
 * @var $unique string Unique string for unique id.
 */
$unique = str_replace(['-','.'],'_',$context).'_' . $iterator;
?>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5">
	<?php foreach ($images as $image) :?>
		<div class="col mb-3">
			<?php
				$img_attribs = [
					'class' => 'img-fluid',
					//'data-title' => 'You can specify any other image attribute like array key and value',
					'id' => $context.'-'.$iterator
				];
				echo HTMLHelper::image($image['img_src'], $image['img_alt'], $img_attribs);
			?>
		</div>
	<?php endforeach; ?>
</div>
