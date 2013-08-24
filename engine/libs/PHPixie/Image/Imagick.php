<?php

namespace PHPixie\Image;

/**
 * Imagick Image driver.
 *
 * @package  Image
 */
class Imagick extends Driver{
	
	/**
	 * Imagick image object
	 * @var \Imagick
	 */
	public $image;
	
	/**
	 * Image class to initialize
	 * @var string
	 */
	protected $image_class = '\Imagick';
	
	/**
	 * Draw class to initialize
	 * @var string
	 */
	protected $draw_class  = '\ImagickDraw';
	
	/**
	 * Composition mode
	 * @var int
	 */
	protected $composition_mode =  \Imagick::COMPOSITE_OVER;
	
	public function create($width, $height, $color = 0xffffff, $opacity = 0) {
		$this->image = new $this->image_class();
		$this->image->newImage($width, $height, $this->get_color($color, $opacity));
		$this->update_size($width, $height);
		return $this;
	}
	
	public function read($file) {
		$this->image = new $this->image_class($file);
		$this->update_size($this->image->getImageWidth(), $this->image->getImageHeight());
		return $this;
	}
	
	/**
	 * Updates size properties
	 *
	 * @param int $width  Image width
	 * @param int $height Image height
	 */
	protected function update_size($width, $height) {
		$this->width = $width;
		$this->height = $height;
	}
	
	protected function get_color($color, $opacity) {
		$color = str_pad(dechex($color), 6, '0', \STR_PAD_LEFT);
		$opacity = str_pad(dechex(floor(255 * $opacity)), 2, '0', \STR_PAD_LEFT);
		return '#'.$color.$opacity;
	}
	
	public function get_pixel($x, $y) {
		$pixel = $this->image-> getImagePixelColor($x, $y);
		$color = $pixel->getColor();
		$normalized_color = $pixel->getColor(true);
		return array(
			'color' => ($color['r'] << 16) + ($color['g'] << 8) + $color['b'],
			'opacity' => $normalized_color['a']
		);
	}
	
	protected function jpg_bg() {
		$bg = new $this->image_class();
		$bg->newImage($this->width, $this->height, $this->get_color(0xffffff, 1));
		$bg->compositeImage($this->image, $this->composition_mode, 0, 0);
		$bg->setImageFormat('jpeg');
		return $bg;
	}
	
	public function render($format = 'png', $die = true, $quality = 90) {
		$image = $this->image;
		
		switch($format) {
			case 'png':
			case 'gif':
				header('Content-Type: image/'.$format);
				$image->setImageFormat($format);
				break;
			case 'jpeg':
				header('Content-Type: image/jpeg');
				$image = $this->jpg_bg($this->image);
				break;
			default:
				throw new \Exception("Type must be either png, jpeg or gif");
		}
		$image->setImageCompressionQuality($quality); 
		echo $image;
		
		if($die){
			die;
		}
		
		if ($format == 'jpeg')
			$image->destroy();
	}
	
	public function save($file, $format = null, $quality = 90) {
		$image = $this->image;
		if ($format == null)
			$format = $this->get_extension($file);
		switch($format) {
			case 'png':
			case 'gif':
				$image->setImageFormat($format);
				break;
			case 'jpeg':
				$image = $this->jpg_bg($this->image);
				break;
			default:
				throw new \Exception("Type must be either png, jpeg or gif");
		}
		
		$image->setImageCompressionQuality($quality); 
		$image->writeImage($file);
		
		if ($format == 'jpeg')
			$image->destroy();
			
		return $this;
	}
	
	public function destroy() {
		$this->image->destroy();
	}
	
	public function crop($width, $height, $x = 0, $y = 0) {
		if ($width > ($maxwidth = $this->width-$x))
			$width = $maxwidth;
		
		if ($height > ($maxheight = $this->height-$y))
			$height = $maxheight;
			
		$this->image->cropImage($width, $height, $x, $y);
		$this->update_size($width, $height);
		
		return $this;
	}
	
	public function scale($scale){
		$width = ceil($this->width*$scale);
		$height = ceil($this->height*$scale);
		
		$this->image->scaleImage($width, $height, true);
		$this->update_size($width, $height);
		return $this;
	}
	
	public function rotate($angle, $bg_color = 0xffffff, $bg_opacity = 0) {
		$this->image->rotateImage($this->get_color($bg_color, $bg_opacity), -$angle);
		$this->update_size($this->image->getImageWidth(), $this->image->getImageHeight());
		return $this;
	}
	
	public function flip($flip_x = false, $flip_y = false) {
		if ($flip_x)
			$this->image->flopImage();
		if ($flip_y)
			$this->image->flipImage();
			
		return $this;
	}
	
	public function overlay($layer, $x = 0, $y = 0) {
		$layer_cs = $layer->image->getImageColorspace();
		$layer->image->setImageColorspace($this->image->getImageColorspace() ); 
		$this->image->compositeImage($layer->image, $this->composition_mode, $x, $y);
		$layer->image->setImageColorspace($layer_cs);
		
		return $this;
	}
	
	protected function draw_text($text, $size, $font_file, $x, $y, $color, $opacity, $angle) {

		$draw = new $this->draw_class();
		$draw->setFont($font_file);
		$draw->setFontSize($size);
		$draw->setFillColor($this->get_color($color, $opacity));
		$this->image-> annotateImage($draw, $x, $y, -$angle, $text);
		return $this;
	}
	
	public function text_metrics($text, $size, $font_file) {
		$draw = new $this->draw_class();
		$draw->setFont($font_file);
		$draw->setFontSize($size);
		$metrics = $this->image-> queryFontMetrics($draw, $text, true);
		return array(
			'ascender'  => floor($metrics['boundingBox']['y2']),
			'descender' => floor(-$metrics['boundingBox']['y1']),
			'width'     => floor($metrics['textWidth']),
			'height'    => floor($metrics['boundingBox']['y2'] - $metrics['boundingBox']['y1']),
		);
	}
}