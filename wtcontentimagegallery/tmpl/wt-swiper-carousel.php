<?php
/**
 * @package       WT Content Image gallery
 * @version       1.2.1
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

use Joomla\CMS\Factory;
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
 * @var $wa \Joomla\CMS\WebAsset\WebAssetManager
 */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
/**
 * You can download swiper js for Joomla
 * @link https://web-tolk.ru/en/dev/joomla-plugins/wt-jswiper
 *       or include swiper.js from CDN or other way you want
 */
$wa->useScript('swiper-bundle')->useStyle('swiper-bundle');

$unique = str_replace(['-', '.'], '_', $context) . '_' . $iterator;

?>
<script>
    let swiper_options<?php echo $unique;?> = {
        "speed": 400,
        "spaceBetween": 100,
        "allowTouchMove": 1,
        "autoHeight": 0,
        "direction": "horizontal",
        "loop": true,
        "allowSlideNext": 1,
        "allowSlidePrev": 1,
        "navigation": {
            "nextEl": ".swiper-button-next_<?php echo $unique;?>",
            "prevEl": ".swiper-button-prev_<?php echo $unique;?>"
        },
        "pagination": {
            "el": ".swiper-pagination_<?php echo $unique;?>",
            "dynamicBullets": 1,
            "dynamicMainBullets": 5,
            "type": "bullets"
        },
        "breakpoints": {
            "320": {
                "slidesPerView": 1,
                "spaceBetween": 2
            },
            "768": {
                "slidesPerView": 2,
                "spaceBetween": 2
            },
            "982": {
                "slidesPerView": 4,
                "spaceBetween": 2
            },
            "1200": {
                "slidesPerView": 5,
                "spaceBetween": 2
            }
        },
        "scrollbar": false,
        "autoplay": false
    };

    if (document.readyState != 'loading') {
        loadWTJSwiper<?php echo $unique;?>();
    } else {
        document.addEventListener('DOMContentLoaded', loadWTJSwiper<?php echo $unique;?>);
    }

    function loadWTJSwiper<?php echo $unique;?>() {
        const swiper<?php echo $unique;?> = new Swiper('.swiper<?php echo $unique;?>', swiper_options<?php echo $unique;?>);
    }

</script>
<!-- Slider main container -->
<div class="swiper<?php echo $unique; ?> swiper">
    <!-- Additional required wrapper -->
    <div class="swiper-wrapper">
		<?php foreach ($images as $image) : ?>
            <div class="swiper-slide">
				<?php

				if ($image['type'] == 'image') :
					$img_attribs = [
						'class' => 'img-fluid',
						//'data-title' => 'You can specify any other image attribute like array key and value',
						//'id' => ''
					];
					if(!empty($image['img_title'])){
						$img_attribs['title'] = $image['img_title'];
					}
					echo HTMLHelper::image($image['img_src'], $image['img_alt'], $img_attribs);
                elseif ($image['type'] == 'video') :
					?>
                    <video class="img-fluid" controls="controls" muted="muted" loop="loop" autoplay="autoplay"
                           src="<?php echo $image['video_src']; ?>" poster="<?php echo $image['video_poster']; ?>"/>
				<?php endif; ?>
            </div>
		<?php endforeach; ?>
    </div>
    <!-- If we need pagination -->
    <div class="swiper-pagination swiper-pagination_<?php echo $unique; ?>"></div>

    <!-- If we need navigation buttons -->
    <div class="swiper-button-prev swiper-button-prev_<?php echo $unique; ?>"></div>
    <div class="swiper-button-next swiper-button-next_<?php echo $unique; ?>"></div>

    <!-- If we need scrollbar -->
    <div class="swiper-scrollbar swiper-scrollbar_<?php echo $unique; ?>"></div>
</div>
