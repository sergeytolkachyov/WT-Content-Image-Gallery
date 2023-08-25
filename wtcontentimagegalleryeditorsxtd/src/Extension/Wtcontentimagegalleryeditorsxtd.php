<?php
/**
 * @package       WT Content Image gallery
 * @version       1.0.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\EditorsXtd\Wtcontentimagegalleryeditorsxtd\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Editor Article button
 *
 * @since  1.5
 */
final class Wtcontentimagegalleryeditorsxtd extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	protected $allowLegacyListeners = false;

	/**
	 * Display the button
	 *
	 * @param   string  $name  The name of the button to add
	 *
	 * @return  CMSObject  The button options as CMSObject
	 *
	 * @since   1.5
	 */
	public function onDisplay($name)
	{


		if (!empty(PluginHelper::getPlugin('content', 'wtcontentimagegallery')))
		{
			$app = $this->getApplication();
			if (!$app->isClient('administrator'))
			{
				return;
			}

			$user = $app->getIdentity();

			// Can create in any category (component permission) or at least in one category
			$canCreateRecords = $user->authorise('core.create', 'com_content')
				|| count($user->getAuthorisedCategories('com_content', 'core.create')) > 0;

			// Instead of checking edit on all records, we can use **same** check as the form editing view
			$values           = (array) Factory::getApplication()->getUserState('com_content.edit.article.id');
			$isEditingRecords = count($values);

			// This ACL check is probably a double-check (form view already performed checks)
			$hasAccess = $canCreateRecords || $isEditingRecords;
			if (!$hasAccess)
			{
				return;
			}

			$link = 'index.php?option=com_ajax&amp;plugin=wtcontentimagegalleryeditorsxtd&amp;group=editors-xtd&amp;format=html&amp;tmpl=component&amp;' . Session::getFormToken() . '=1&amp;editor=' . $name;

			$button          = new CMSObject;
			$button->modal   = true;
			$button->class   = 'btn';
			$button->link    = $link;
			$button->text    = '{Gallery}';
			$button->name    = 'pictures';
			$button->options = [
				'height'     => '400px',
				'width'      => '800px',
				'modalWidth' => '40',
			];

			return $button;
		}

		return;
	}

	/**
	 * Method working with Joomla com_ajax. Return a HTML form for product selection
	 * @return string product selection HTML form
	 * @throws Exception
	 */
	public function onAjaxWtcontentimagegalleryeditorsxtd()
	{
		$app = $this->getApplication();

		$lang = $app->getLanguage();
		$lang->load('plg_editors-xtd_wtcontentimagegalleryeditorsxtd', JPATH_ADMINISTRATOR);
		$doc = $app->getDocument();
		$doc->getWebAssetManager()
			->useScript('core')
			->registerAndUseScript(
				'wtcontentimagegalleryeditorsxtd', 'plg_editors-xtd_wtcontentimagegalleryeditorsxtd/wtcontentimagegalleryeditorsxtd.js'
			);

		$editor                             = $app->getInput()->get('editor', '');
		$wt_wtcontentimagegalleryeditorsxtd = Folder::files(JPATH_SITE . "/plugins/content/wtcontentimagegallery/tmpl");
		$layout_options                     = array();
		foreach ($wt_wtcontentimagegalleryeditorsxtd as $file)
		{
			if (File::getExt($file) == "php")
			{
				$wt_layout        = File::stripExt($file);
				$layout_options[] = HTMLHelper::_('select.option', $wt_layout, $wt_layout);
			}
		}

		if (!empty($editor))
		{

			$doc->addScriptOptions('xtd-wtcontentimagegalleryeditorsxtd', array('editor' => $editor));
		}


		?>

        <?php if (!PluginHelper::isEnabled('content','wtcontentimagegallery')): ?>
            <div class="alert alert-danger">
                <h4><?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_CONTENT_PLUGIN_NOT_ENABLED');?></h4>
            </div>
        <?php else: ?>
            <?php
                $content_plugin = PluginHelper::getPlugin('content','wtcontentimagegallery');
                $content_plugin_params = new Registry($content_plugin->params);
				$layout = (!empty($content_plugin_params->get('default_layout_for_default','default')) ? $content_plugin_params->get('default_layout_for_default','default') : 'default');
            ?>
        <p class="text-success fw-bold"><?php echo Text::sprintf('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_DEFAULT_LAYOUT_FOR_DEFAULT', $layout);?></strong></p>
        <?php endif; ?>

        <div class="input-group mb-3">
            <label for="wtcontentimagegalleryeditorsxtd_layout" class="input-group-text">
                <strong>tmpl</strong>
            </label>
			<?php
			$attribs = [
				'class'      => 'form-select',
				'aria-label' => 'Choose layout'
			];

			echo HTMLHelper::_("select.genericlist", $layout_options, $name = "wtcontentimagegalleryeditorsxtd_layout", $attribs, $key = 'value', $text = 'text', $selected = "default");

			?>
        </div>

        <div class="mb-3">
            <textarea class="form-control" id="wtcontentimagegalleryeditorsxtd_images" name="images" placeholder="<?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_IMAGES_TEXTAREA_PLACEHOLDER');?>"></textarea>
        </div>

            <details>
                <summary><?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_SHORTCODE_LABEL'); ?></summary>
	            <?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_SHORTCODE_DESC'); ?>
            </details>

        <details class="mb-5">
            <summary><?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_INSERT_IMAGE_LABEL'); ?></summary>
	        <?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_INSERT_IMAGE_DESC'); ?>
        </details>
<p></p>
<p></p>
<p></p>


        <div class="fixed-bottom bg-white shadow-sm border-top">
            <div class="d-flex flex-column justify-content-center align-items-center py-2">
                <button class="btn btn-lg btn-primary w-75" id="wtcontentimagegalleryeditorsxtd_insert_btn" type="button"><?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_INSERT_BTN_LABEL');?></button>
                <span class="">
                        <a href="https://web-tolk.ru" target="_blank"
                           style="display: inline-flex; align-items: center;">
                                <svg width="85" height="18" xmlns="http://www.w3.org/2000/svg">
                                     <g>
                                      <title>Go to https://web-tolk.ru</title>
                                      <text font-weight="bold" xml:space="preserve" text-anchor="start"
                                            font-family="Helvetica, Arial, sans-serif" font-size="18" id="svg_3" y="18"
                                            x="8.152073" stroke-opacity="null" stroke-width="0" stroke="#000"
                                            fill="#0fa2e6">Web</text>
                                      <text font-weight="bold" xml:space="preserve" text-anchor="start"
                                            font-family="Helvetica, Arial, sans-serif" font-size="18" id="svg_4" y="18"
                                            x="45" stroke-opacity="null" stroke-width="0" stroke="#000"
                                            fill="#384148">Tolk</text>
                                     </g>
                                </svg>
                        </a>
                    </span>
            </div>
        </div>
        </div>
		<?php
	}
}
