/**
 * @package       WT Content Image gallery
 * @version       1.2.0
 * @Author        Sergey Tolkachyov, https://web-tolk.ru
 * @copyright     Copyright (C) 2023 Sergey Tolkachyov
 * @license       GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @since         1.0.0
 */
(() => {
    document.addEventListener('DOMContentLoaded', () => {
        // Get the elements

        let Insert_btn = document.getElementById('wtcontentimagegalleryeditorsxtd_insert_btn');
        // Listen for click event
        Insert_btn.addEventListener('click', event => {

            //const images = target.getAttribute('data-article-id');
            const tmpl = document.getElementById('wtcontentimagegalleryeditorsxtd_layout').value;
            const images = document.getElementById('wtcontentimagegalleryeditorsxtd_images').value;
            console.log(typeof images);
            let short_code = '';
            if(images !== ''){
                short_code = "{gallery tmpl=" + tmpl + "}" + images +"{/gallery}";
            } else {
                short_code = "{gallery tmpl=" + tmpl + "}{/gallery}";
            }

            if (!Joomla.getOptions('xtd-wtcontentimagegalleryeditorsxtd')) {
                // Something went wrong!
                // @TODO Close the modal
                return false;
            }

            const {
                editor
            } = Joomla.getOptions('xtd-wtcontentimagegalleryeditorsxtd');

            window.parent.Joomla.editors.instances[editor].replaceSelection(short_code);

            if (window.parent.Joomla.Modal) {
                window.parent.Joomla.Modal.getCurrent().close();
            }
        });

    });
})();
