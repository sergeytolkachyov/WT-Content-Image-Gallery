<?php

/**
 * @package       WT Content Image gallery
 * @version       1.2.1
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Content\Wtcontentimagegallery\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;


class Wtcontentimagegallery extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;
    protected $allowLegacyListeners = false;

    public $iterator = 0;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @throws \Exception
     * @since 4.1.0
     *
     */
    public static function getSubscribedEvents(): array
    {

        return [
            'onContentPrepare' => 'onContentPrepare'
        ];
    }


    /**
     *
     * @param ContentPrepareEvent $event
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepare(ContentPrepareEvent $event): void
    {
        /**
         * @param   string    $context  The context of the content being passed to the plugin.
         * @param   object   &$row      The article object.  Note $article->text is also available
         * @param   mixed    &$params   The article params
         * @param   integer   $page     The 'page' number
         */

        // Don't run if in the API Application
        // Don't run this plugin when the content is being indexed
        $context = $event->getContext();
        if ($this->getApplication()->isClient('api') || $context === 'com_finder.indexer') {
            return;
        }

        // Get content item
        $row = $event->getItem();

        // If the item does not have a text property there is nothing to do
        if (!property_exists($row, 'text')) {
            return;
        }

        // expression to search for
        $regex = "#{gallery\s(.*?)}(.*?){/gallery}#is";

        // Expression to search for shortcode without tmpl param
        $regex2 = "#{gallery}(.*?){/gallery}#is";

        // Find all instances of plugin and put in $matches.
        $matches = [];
        $matches2 = [];
        preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER);
        preg_match_all($regex2, $row->text, $matches2, PREG_SET_ORDER);

        /**
         * Process shorcodes with tmpl param
         */
        if (!empty($matches)) {
            $this->processImages($matches, $context, $row, 2);
        }
        /**
         * Process shorcodes without tmpl param
         */
        if (!empty($matches2)) {
            $this->processImages($matches2, $context, $row, 1);
        }
    }

    /**
     * @param $matches array Matches array from preg_match_all
     * @param $context string Context string like <b>com_content.article</b>
     * @param $row object Article object
     * @param $type int Type 1 - shortcode <b>without tmpl</b> param, type 2 - <b>with tmpl</b> param
     *
     *
     * @since 1.0.0
     */
    public function processImages($matches, $context, &$row, $type = 2): void
    {
        $image_file_allowed_extensions = ['bmp', 'gif', 'jpeg', 'jpe', 'jpg', 'png', 'tiff', 'tif', 'webp', 'avif', 'heif', 'heifs', 'heic', 'heics', 'svg'];
        $video_file_allowed_extensions = ['mp4', 'webm', 'ogv'];

        foreach ($matches as $match) {
            $tmpl = $this->params->get('default_layout_for_default', 'default');
            if (strpos($match[1], 'tmpl') !== false) {
                $tmpl_array = explode('=', $match[1]);
                $tmpl = $tmpl_array[1];
            }

            $img_array = [];
            $images = [];
            // Список путей к картинкам вида
            // images/photo1.jpg,images/photo2.jpg,images/photo3.jpg,images/video.mp4,
            // Через запятую. В других вариантах запятых быть не может.

            // Убираем все теги, кроме <img> и <video>
            $match[$type] = strip_tags($match[$type], ['img', 'video']);

            if (strpos($match[$type], ',') !== false && strpos($match[$type], '<img') === false) {
                $img_array = explode(',', trim($match[$type]));
                foreach ($img_array as $img_file_path) {
                    $img_file_path = trim($img_file_path);

                    $img_file_path = !str_starts_with($img_file_path, '/')
                        ? $img_file_path
                        : ltrim($img_file_path, '/'); // если путь к папке начинается со слэша, то удаляем его

                    if (File::exists(JPATH_SITE . '/' . $img_file_path)) {

                        $file_extension = File::getExt(basename($img_file_path));
                        if (in_array($file_extension, $image_file_allowed_extensions)) {
                            // Это картинка
                            $images[] = [
                                'img_src' => trim($img_file_path),
                                'img_alt' => File::stripExt(basename($img_file_path)),
                                'type' => 'image'
                            ];
                        }

                        if (in_array($file_extension, $video_file_allowed_extensions)) {
                            $poster = '';
                            // Ищем poster для этого видео. Имя файла должно быть такое же, как у видеофайла
                            $path = dirname(JPATH_SITE . '/' . $img_file_path);
                            $img_files = Folder::files($path, '^.*\.(' . implode('|', $image_file_allowed_extensions) . ')');
                            foreach ($img_files as $img_file) {
                                $poster_filename = File::stripExt($img_file);
                                if ($poster_filename == File::stripExt(basename($img_file_path))) {
                                    $poster =  dirname($img_file_path) . '/' . $img_file;
                                    break;
                                }
                            }

                            // Это видео
                            $images[] = [
                                'video_src' => trim($img_file_path),
                                'video_poster' => $poster,
                                'type' => 'video'
                            ];
                        }
                    }
                }
            } elseif (strpos($match[$type], '<img') !== false || strpos($match[$type], '<video') !== false) {
                // Картинки вида <img src="" />
                // Видео вида <video src="" poster="" />

                // Создаем объект DOMDocument
                $dom = new \DOMDocument();
                // fix html5/svg errors
                $dom->loadHTML('<?xml encoding="UTF-8">' . $match[$type], LIBXML_NOERROR); // LIBXML_NOERROR , LIBXML_NOWARNING

                if (strpos($match[$type], '<img') !== false) {
                    // Получаем все теги <img>
                    $domImages = $dom->getElementsByTagName('img');

                    foreach ($domImages as $domImage) {
                        $src = $domImage->getAttribute('src');

                        if (empty($src)) {
                            continue;
                        }

                        if (!empty($domImage->getAttribute('alt'))) {
                            $alt = $domImage->getAttribute('alt');
                        } else {
                            $alt = File::stripExt($src);
                        }

                        $images[] = [
                            'img_src' => trim($src),
                            'img_alt' => $alt,
                            'type' => 'image'
                        ];
                    }
                }

                if (strpos($match[$type], '<video') !== false) {
                    $domVideos = $dom->getElementsByTagName('video');

                    foreach ($domVideos as $domVideo) {
                        $src = $domVideo->getAttribute('src');

                        if (empty($src)) {
                            continue;
                        }

                        $poster = '';
                        if (!empty($domVideo->getAttribute('poster'))) {
                            $poster = $domVideo->getAttribute('poster');
                        }

                        $images[] = [
                            'video_src' => trim($src),
                            'video_poster' => $poster,
                            'type' => 'video'
                        ];
                    }
                }
            } else {
                // Указан путь к папке с изображениями.
                $path = trim($match[$type]);

                $path = !str_starts_with($path, '/')
                    ? $path
                    : ltrim($path, '/'); // если путь к папке начинается со слэша, то удаляем его

                if (Folder::exists(JPATH_SITE . '/' . $path)) {
                    $img_files = Folder::files(JPATH_SITE . '/' . $path, '^.*\.(' . implode('|', $image_file_allowed_extensions) . ')');
                    $labels = [];
                    if (
                        file_exists(JPATH_SITE . '/' . $path . '/labels.txt')
                        && filesize(JPATH_SITE . '/' . $path . '/labels.txt') != false
                        && filesize(JPATH_SITE . '/' . $path . '/labels.txt') > 0
                    ) {
                        $lables_txt = file(JPATH_SITE . '/' . $path . '/labels.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                        foreach ($lables_txt as $line) {
                            if (!empty(trim($line))) {
                                // [0] => filename, [1] => alt, [2] => title
                                $image_data = explode('|', $line);
                                $filename = trim($image_data[0]);
                                $alt = '';
                                $title = '';
                                if (isset($image_data[1]) && !empty(trim($image_data[1]))) {
                                    $alt = trim($image_data[1]);
                                }
                                if (isset($image_data[2]) && !empty(trim($image_data[2]))) {
                                    $title = trim($image_data[2]);
                                }
                                $labels[$filename] = [
                                    'alt' => $alt,
                                    'title' => $title,
                                ];
                            } else {
                                continue;
                            }
                        }
                    }

                    foreach ($img_files as $file) {
                        $images[] = [
                            'img_src' => Path::clean($path . '/' . $file),
                            'img_alt' => (array_key_exists($file, $labels) && !empty($labels[$file]['alt']) ? $labels[$file]['alt'] : File::stripExt($file)),
                            'img_title' => (array_key_exists($file, $labels) && !empty($labels[$file]['title']) ? $labels[$file]['title'] : File::stripExt($file)),
                            'type' => 'image'
                        ];
                    }

                    $video_files = Folder::files(JPATH_SITE . '/' . $path, '^.*\.(' . implode('|', $video_file_allowed_extensions) . ')');
                    foreach ($video_files as $file) {


                        $video_filename = File::stripExt($file);
                        $poster = '';
                        // we must find a poster image for video and remove it from images list
                        // poster file must have the same filename as video file

                        foreach ($images as $key => $image) {
                            if (
                                $image['type'] == 'image' &&
                                strpos($image['img_src'], $video_filename) !== false
                            ) {
                                $img_filename = File::stripExt($image['img_src']);
                                $img_file_ext = File::getExt($image['img_src']);
                                $poster = $img_filename . '.' . $img_file_ext;
                                unset($images[$key]);
                            }
                        }

                        $images[] = [
                            'video_src' => Path::clean($path . '/' . $file),
                            'video_poster' => $poster,
                            'type' => 'video'
                        ];
                    }
                }
            }

            $iterator = $this->iterator;
            ob_start();
            if (file_exists(JPATH_SITE . '/plugins/content/wtcontentimagegallery/tmpl/' . $tmpl . '.php')) {
                require JPATH_SITE . '/plugins/content/wtcontentimagegallery/tmpl/' . $tmpl . '.php';
            } else {
                require JPATH_SITE . '/plugins/content/wtcontentimagegallery/tmpl/default.php';
            }
            $html = ob_get_clean();
            $this->iterator++;
            $row->text = str_replace($match[0], $html, $row->text);
        }
    }
}
