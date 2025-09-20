# silverstripe-svgenius

First-class SVG handling for Silverstripe CMS &amp; front-end.

Requires Silverstripe 6+.

- SVGs can be uploaded into Asset Admin just like images
- Thumbnail of SVG is displayed on Asset Admin grid view
- Asset CMS fields adjusted to hide focuspoint field for SVGs when jonom/focuspoint is installed
- Retrieve height, width, orientation, and apply resizing by ratio, width or height using existing manipulation methods (Fit, FitMax, ScaleWidth, ScaleMaxWidth, ScaleHeight, ScaleMaxHeight)
- Can embed in front-end as an <img> tag, or embed the svg file contents <svg>
- SVG contents are passed through santisation library [enshrined\svgSanitize](https://github.com/darylldoyle/svg-sanitizer)

Note, this behaviour is not included out-of-the-box in Silverstripe due to the security risks inherent with user-supplied SVG files. Per above, this module does process SVGs through a sanitisation process, but this needs to be combined with your own strategies and in consideration of your use case to reach acceptable security.
