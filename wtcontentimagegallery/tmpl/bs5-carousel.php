<?php
/**
 * @package       WT Content Image gallery
 * @version       1.0.0
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
$wa->useScript('bootstrap.carousel');

$unique = str_replace(['-','.'],'_',$context).'_' . $iterator;
$inline_js = "const carousel = new bootstrap.Carousel('#wt_bs5_carousel_$unique')";
$wa->addInlineStyle($inline_js)
?>
<div id="wt_bs5_carousel_<?php echo $unique;?>" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
		<?php for ($i = 0; $i < count($images); $i++)
		{
			echo '<button type="button" data-bs-target="#wt_bs5_carousel_'. $unique . '" data-bs-slide-to="' . $i . '" ' . (($i + 1 == 1) ? 'class="active"' : '') . ' aria-label="Slide ' . $i . '"></button>';
		} ?>
    </div>
    <div class="carousel-inner">
	    <?php
	    $k = 0;
        foreach ($images as $image) :?>

            <div class="carousel-item <?php echo (($k + 1) == 1) ? "active" : ''; ?>">
	            <?php
                    $img_attribs = [
                        'class' => 'd-block w-100',
                        //'data-title' => 'You can specify any other image attribute like array key and value',
                        'id' => $context.'-'.$iterator
                    ];
                    echo HTMLHelper::image($image['img_src'], $image['img_alt'], $img_attribs);
	            ?>
            </div>

	    <?php
        $k++;
        endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#wt_bs5_carousel_<?php echo $unique; ?>" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#wt_bs5_carousel_<?php echo $unique; ?>" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
