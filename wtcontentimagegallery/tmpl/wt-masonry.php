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
$wa->useScript('wtmasonryjs.local')->useScript('imagesloaded.local');

$inlinestyle = '
    .grid-item { width: 200px; }
    .grid-item--width2 { width: 400px; }
    .grid-item--height2 { height: 400px; }
    ';
$wa->addInlineStyle($inlinestyle);

/**
 * @var $unique string Unique string for unique id.
 */
$unique = str_replace(['-', '.'], '_', $context) . '_' . $iterator;
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const masonryGrid<?php echo $unique;?> = document.getElementById('masonry<?php echo $unique;?>');
        var msnry<?php echo $unique;?> = new Masonry(masonryGrid<?php echo $unique;?>, {
            itemSelector: '.grid-item',
            columnWidth: 200,
            percentPosition: true
        });

        imagesLoaded(masonryGrid<?php echo $unique;?> ).on('progress', function () {
            // layout Masonry after each image loads
            msnry<?php echo $unique;?>.layout();
        });
    });

</script>

<div id="masonry<?php echo $unique; ?>">
    <?php

    $i = 0;
    // Reorder images and videos array in random order
    shuffle($images);

    foreach ($images as $image) :?>
    <?php
	    /**
	     * echo((($i % 5) == 0) - проверяем делится ли порядковый номер элемента сетки на 5 без остатка.
         * Таким образом класс grid-item--width2 назначается каждому 5-му элементу.
         * Это число можно менять по своему усмотрению.
         * Не забудьте перед редактированием этого файла скопировать и переименовать его.
	     */
        ?>
        <div class="grid-item p-2 rounded <?php echo((($i % 5) == 0) ? 'grid-item--width2' : ''); ?>">
            <?php
            if ($image['type'] == 'image') {
                $img_attribs = [
                    'class' => '',
//                    'style' => 'border-radius:3rem',
                    //'data-title' => 'You can specify any other image attribute like array key and value',
                    'id' => $context . '-' . $iterator
                ];

	            if(!empty($image['img_title'])){
		            $img_attribs['title'] = $image['img_title'];
	            }

                if ($i > 9) {
                    $img_attribs['loading'] = 'lazy';
                }

                echo HTMLHelper::image($image['img_src'], $image['img_alt'], $img_attribs);
            } elseif ($image['type'] == 'video') {
                ?>
                <video class="img-fluid" controls="controls" muted="muted" loop="loop" autoplay="autoplay"
                       src="<?php echo $image['video_src']; ?>" poster="<?php echo $image['video_poster']; ?>"/>
                <?php
            }
            ?>
        </div>
        <?php
        $i++;
    endforeach; ?>
</div>