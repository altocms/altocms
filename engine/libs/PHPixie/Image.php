<?php
namespace PHPixie;

/**
 * Image Module for PHPixie.
 * You can use this module to resize, crop and overlay images.
 * Drawing text is also supported, together with automatic text wrapping.
 * Implemented drivers are GD, Imagick and Gmagick.
 *
 * This module is not included by default, install it using Composer
 * by adding
 * <code>
 * 		"phpixie/image": "2.*@dev"
 * </code>
 * to your requirement definition. Or download it from
 * https://github.com/dracony/PHPixie-Image
 * 
 * To enable it add it to your Pixie class' modules array:
 * <code>
 * 		protected $modules = array(
 * 			//Other modules ...
 * 			'image' => '\PHPixie\Image',
 * 		);
 * </code>
 *
 *
 * @link https://github.com/dracony/PHPixie-Image Download this module from Github
 * @package    Image
 */
class Image {
	
	/**
	 * Pixie Dependancy Container
	 * @var \PHPixie\Pixie
	 */
	public $pixie;
	
	/**
	 * Initializes the Image module
	 * 
	 * @param \PHPixie\Pixie $pixie Pixie dependency container
	 */
	public function __construct($pixie) {
		$this->pixie = $pixie;
	}
	
	/**
	 * Reads image from file.
	 *
	 * @param   string  $file  Image file
	 * @param   string  $config Configuration name.
	 *                        Defaults to  'default'.
	 * @return  \PHPixie\Image\Driver Initialized Image
	 */
	public function read($file, $config = 'default') {
		$driver = $this->pixie->config->get("image.{$config}.driver");
		$driver = "\\PHPixie\\Image\\{$driver}";
		$image  = new $driver; 
		$image->read($file);
		return $image;
	}

	/**
	 * Reads image from a byte string.
	 * You can use this method to load images that you downloaded using file_get_contents() or CURL.
	 *
	 * @param   string  $bytes  Bytestring containing image data
	 * @param   string  $config Configuration name.
	 *                        Defaults to  'default'.
	 * @return  \PHPixie\Image\Driver Initialized Image
	 */
	public function load($bytes, $config = 'default') {
		$driver = $this->pixie->config->get("image.{$config}.driver");
		$driver = "\\PHPixie\\Image\\{$driver}";
		$image  = new $driver; 
		$image->load($bytes);
		return $image;
	}
	
	/**
	 * Creates an image filled with a single color.
	 *
	 * @param   int  $width  Image width
	 * @param   int  $height Image height
	 * @param   int  $color  Hex representation of fill color (e.g 0xffffff)
	 * @param   float  $color  Fill opacity (between 0 and 1)
	 * @param   string  $config Configuration name.
	 *                        Defaults to  'default'.
	 * @return  \PHPixie\Image\Driver Initialized Image
	 */
	public function create($width, $height, $color = 0xffffff, $opacity = 0, $config = 'default') {
		$driver = $this->pixie->config->get("image.{$config}.driver");
		$driver = "\\PHPixie\\Image\\{$driver}";
		$image  = new $driver; 
		$image->create($width, $height, $color, $opacity);
		return $image;
	}

}
