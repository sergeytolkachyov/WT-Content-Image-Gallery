<?php
/**
 * @package       WT Content Image gallery
 * @version       1.2.3.1
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */

namespace Joomla\Plugin\EditorsXtd\Wtcontentimagegalleryeditorsxtd\Extension;

use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Event\Plugin\AjaxEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\Uri\Uri;

use function defined;

defined('_JEXEC') or die;

/**
 * Editor Article button
 *
 * @since  1.0.0
 */
final class Wtcontentimagegalleryeditorsxtd extends CMSPlugin implements SubscriberInterface
{

    protected $_name = 'wtcontentimagegalleryeditorsxtd';
    protected $_type = 'editors-xtd';

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return array
	 *
	 * @since   5.2.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onEditorButtonsSetup'                  => 'onEditorButtonsSetup',
			'onAjaxWtcontentimagegalleryeditorsxtd' => 'onAjaxWtcontentimagegalleryeditorsxtd',
		];
	}

	/**
	 * @param  EditorButtonsSetupEvent $event
	 * @return void
	 *
	 * @since   5.2.0
	 */
	public function onEditorButtonsSetup(EditorButtonsSetupEvent $event): void
	{
		$subject  = $event->getButtonsRegistry();
		$disabled = $event->getDisabledButtons();

		if (\in_array($this->_name, $disabled)) {
			return;
		}

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

            $url  =  new Uri('index.php');
			$url->setQuery([
                    'option' => 'com_ajax',
                    'plugin' => 'wtcontentimagegalleryeditorsxtd',
                    'group' => 'editors-xtd',
                    'format' => 'html',
                    'tmpl' => 'component',
                    Session::getFormToken()  => '1',
                    'editor' => $event->getEditorId(),
            ]);
			$button          = new Button(
				$this->_name,
				[
					'action'  => 'modal',
					'link'    => $url->toString(),
					'text'    => '{Gallery}',
					'icon'    => 'pictures',
					'iconSVG' => '<svg width="24" height="24" viewBox="0 0 512 512"><path d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48'
						. ' 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm-6 336H54a6 6 0 0 1-6-6V118a6 6 0 0 1 6-6h404a6 6'
						. ' 0 0 1 6 6v276a6 6 0 0 1-6 6zM128 152c-22.091 0-40 17.909-40 40s17.909 40 40 40 40-17.909 40-40-17.909-40-40-40'
						. 'zM96 352h320v-80l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L192 304l-39.515-39.515c-4.686-4.686-12.284-4'
						. '.686-16.971 0L96 304v48z"></path></svg>',
					'options' => [
						'height'     => '400px',
						'width'      => '800px',
						'modalWidth' => '40',
					],
					// This is whole Plugin name, it is needed for keeping backward compatibility
					'name' => 'editors-xtd_wtcontentimagegalleryeditorsxtd',
				]
			);

			if ($button) {
				$subject->add($button);
			}
		}
	}

	/**
	 * Method working with Joomla com_ajax. Return a HTML form for product selection
	 * @return string product selection HTML form
	 * @throws \Exception
     *
     * @since 1.0.0
	 */
	public function onAjaxWtcontentimagegalleryeditorsxtd(AjaxEvent $event)
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
		$layout_options                     = [];
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

            <details class="mb-3">
                <summary><?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_SHORTCODE_LABEL'); ?></summary>
	            <?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_SHORTCODE_DESC'); ?>
            </details>

        <details class="mb-3">
            <summary><?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_INSERT_IMAGE_LABEL'); ?></summary>
	        <?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_INSERT_IMAGE_DESC'); ?>
        </details>
        <details class="mb-3">
            <summary><?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_IMAGES_ALT_AND_TITLE_FROM_FILE_LABEL'); ?></summary>
	        <?php echo Text::_('PLG_WTCONTENTIMAGEGALLERYEDITORSXTD_IMAGES_ALT_AND_TITLE_FROM_FILE_DESC'); ?>
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
