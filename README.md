# WT Content Image Gallery for Joomla
Image gallery plugins package for insertion into Joomla articles and modules, as well as wherever the content group plugins work. Create your own image gallery layouts.
[More info on developer site](https://web-tolk.ru/en/dev/joomla-plugins/wt-content-image-gallery)
# Shortcode
`{gallery}...{/gallery}` - layout `default`
`{gallery tmpl=tmplname}...{/gallery}` - layout `tmplName.php` in `plugins/content/wtcontentimagegallery/tmpl`
# Inserting images
## Image path
`{gallery tmpl=tmplName}images/path/to/images{/gallery}`
## Comma-separated list of file paths
`{gallery tmpl=tmplName}images/photo_1.webp, images/folder1/photo_2.webp, images/folder4/folder16/photo_3.webp{/gallery}`
## HTML code between tags {gallery}...{/gallery}
`{gallery tmpl=tmplName}<p> <img src="images/photo_1.webp" alt="Alt attribute"/> </p> <ul> <li><img src="images/folder_3/photo_114.webp" alt="Alt attribute"/> </li></ul>{/gallery}`
# Alt and title attributes
If you insert the path to the directory with images, then you can specify the `alt` and `title` attributes for each image in the file `labels.txt`, which should be placed in the directory near with the images.
The contents of the file must contain information in the following form: `file_name.webp | alt attribute | title attribute`. For each image, the information is contained in a separate line. There is no need to put the `|` character at the end of the line. 
`Title` for images can be omitted, then the string will look like `image_2.webp|alt for image_2`.
## labels.txt example
```
image_1.webp|alt for image_1|title for image 1
image_2.webp|alt for image_2
image_3.webp|alt for image_3|title for image 3
```