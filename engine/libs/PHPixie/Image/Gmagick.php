<?php

namespace PHPixie\Image;

/**
 * Gmagick Image driver.
 *
 * @package  Image
 */
class Gmagick extends Imagick{

	/**
	 * Imagick image object
	 * @var \Gmagick
	 */
	public $image;

	/**
	 * Image class to initialize
	 * @var string
	 */
	protected $image_class = '\Gmagick';

	/**
	 * Draw class to initialize
	 * @var string
	 */
	protected $draw_class  = '\GmagickDraw';

	protected $resize_filter = \Imagick::FILTER_LANCZOS;

	/**
	 * Composition mode
	 * @var int
	 */
	protected $composition_mode =  \Gmagick::COMPOSITE_OVER;
    
    protected function set_quality($quality) {
        $this->image->setCompressionQuality($quality);
    }
}