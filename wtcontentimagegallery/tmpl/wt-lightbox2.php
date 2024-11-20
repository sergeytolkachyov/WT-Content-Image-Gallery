<?php
/**
 * @package       WT Content Image gallery
 * @version       1.2.2
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filter\OutputFilter;

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
 * @var $wa \Joomla\CMS\WebAsset\WebAssetManager
 */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
/**
 * You can download lightbox2 js for Joomla
 * @link https://web-tolk.ru/dev/joomla-plugins/wt-lightbox2-js
 * @link https://lokeshdhakar.com/projects/lightbox2/#examples
 *       or include lightbox2.js any other way you want
 */
$wa->usePreset('lightbox2')->useScript('jquery');

// Will be like "com_content_article_0"
$unique = str_replace(['-', '.'], '_', $context) . '_' . $iterator;

?>
<div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5">
	<?php foreach ($images as $image) :

		if ($image['type'] == 'image') :
			?>
            <a href="<?php echo $image['img_src']; ?>" data-lightbox="<?php echo $unique; ?>" class="col mb-3">
				<?php
				$img_attribs = [
					'class' => 'img-fluid',
					//'data-title' => 'You can specify any other image attribute like array key and value',
					//'id' => ''
				];
				if(!empty($image['img_title'])){
					$img_attribs['title'] = $image['img_title'];
				}
				echo HTMLHelper::image($image['img_src'], $image['img_alt'], $img_attribs);
				?>
            </a>
		<?php
        elseif ($image['type'] == 'video') : ?>
            <a href="<?php echo $image['img_src']; ?>" data-lightbox="<?php echo $unique; ?>" class="col mb-3">
                <video class="img-fluid" controls="controls" muted="muted" loop="loop" autoplay="autoplay"
                       src="<?php echo $image['video_src']; ?>" poster="<?php echo $image['video_poster']; ?>"/>
            </a>
		<?php endif;
	endforeach; ?>
</div>
