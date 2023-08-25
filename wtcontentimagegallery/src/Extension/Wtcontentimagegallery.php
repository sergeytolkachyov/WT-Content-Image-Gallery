<?php
/**
 * @package       WT Content Image gallery
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\Content\Wtcontentimagegallery\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Utility\Utility;
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
	 * @throws Exception
	 * @since 4.1.0
	 *
	 */
	public static function getSubscribedEvents(): array
	{
		$app = Factory::getApplication();

		$mapping = [];

		if ($app->isClient('site'))
		{
			$mapping['onContentPrepare'] = 'onContentPrepare';

		}

		return $mapping;
	}


	/**
	 * Plugin that adds a pagebreak into the text and truncates text at that point
	 *
	 * @param   string    $context  The context of the content being passed to the plugin.
	 * @param   object   &$row      The article object.  Note $article->text is also available
	 * @param   mixed    &$params   The article params
	 * @param   integer   $page     The 'page' number
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function onContentPrepare(Event $event)
	{

		$context = $event->getArgument(0);
		$row     = $event->getArgument(1);


		if ($context === 'com_finder.indexer') {
			return;
		}

		if (!is_object($row) || !property_exists($row, 'text') || is_null($row->text)) {
			return;
		}

		// expression to search for
		$regex = "#{gallery\s(.*?)}(.*?){/gallery}#is";

		// Expression to search for shortcode without tmpl param
		$regex2 = "#{gallery}(.*?){/gallery}#is";

		// Find all instances of plugin and put in $matches.
		$matches  = [];
		$matches2 = [];
		preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER);
		preg_match_all($regex2, $row->text, $matches2, PREG_SET_ORDER);

		/**
		 * Process shorcodes with tmpl param
		 */
		if (!empty($matches))
		{
			$this->processImages($matches, $context, $row, 2);
		}
		/**
		 * Process shorcodes without tmpl param
		 */
		if (!empty($matches2))
		{
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
	public function processImages($matches, $context, &$row, $type = 2) : void
	{

		foreach ($matches as $match)
		{
			$tmpl = $this->params->get('default_layout_for_default','default');
			if (strpos($match[1], 'tmpl') !== false)
			{
				$tmpl_array = explode('=', $match[1]);
				$tmpl       = $tmpl_array[1];
			}

			$img_array = [];
			$images    = [];
			// Список путей к картинкам вида
			// images/photo1.jpg,images/photo2.jpg,images/photo3.jpg,
			// Через запятую. В других вариантах запятых быть не может.

			$match[$type] = strip_tags($match[$type], ['img']);

			if (strpos($match[$type], ',') !== false && strpos($match[$type], '<img') === false)
			{
				$img_array = explode(',', trim($match[$type]));

				foreach ($img_array as $img_file_path)
				{
					$img_file_path = trim($img_file_path);

					if (File::exists(JPATH_SITE . '/' . $img_file_path))
					{
						$images[] = [
							'img_src' => $img_file_path,
							'img_alt' => File::stripExt(basename($img_file_path))
						];
					}
				}
			}
			elseif (strpos($match[$type], '<img') !== false)
			{
				// Картинки вида <img src="" />

				// Создаем объект DOMDocument
				$dom = new \DOMDocument();
				$dom->loadHTML('<?xml encoding="utf-8" ?>' . $match[$type]);
				// Получаем все теги <img>
				$domImages = $dom->getElementsByTagName('img');

				foreach ($domImages as $domImage)
				{
					$src = $domImage->getAttribute('src');

					if (empty($src))
					{
						continue;
					}

					if (!empty($domImage->getAttribute('alt')))
					{
						$alt = $domImage->getAttribute('alt');
					}
					else
					{
						$alt = File::stripExt($src);
					}

					$images[] = [
						'img_src' => trim($src),
						'img_alt' => $alt
					];
				}
			}
			else
			{
				// Указан путь к папке с изображениями.
				$path = trim($match[$type]);
				if (Folder::exists(JPATH_SITE . '/' . $path))
				{
					$files = Folder::files(JPATH_SITE . '/' . $path, '^.*\.(bmp|gif|jpeg|jpe|jpg|png|tiff|tif|webp|avif|heif|heifs|heic|heics)');
					foreach ($files as $file)
					{
						$images[] = [
							'img_src' => Path::clean($path . '/' . $file),
							'img_alt' => File::stripExt($file)
						];

					}
				}
			}

			$iterator = $this->iterator;
			ob_start();
			if (file_exists(JPATH_SITE . '/plugins/content/wtcontentimagegallery/tmpl/' . $tmpl . '.php'))
			{
				require JPATH_SITE . '/plugins/content/wtcontentimagegallery/tmpl/' . $tmpl . '.php';
			}
			else
			{
				require JPATH_SITE . '/plugins/content/wtcontentimagegallery/tmpl/default.php';
			}
			$html = ob_get_clean();
			$this->iterator++;
			$row->text = str_replace($match[0], $html, $row->text);
		}
	}

}
