<?php

namespace PHPixie\Image;

/**
 * Base image driiver.
 * Actual drivers have to extend this class.
 *
 * @package  Image
 */
abstract class Driver {

	/**
	 * Image width
	 * @var int
	 */
	public $width;
	
	/**
	 * Image height
	 * @var int
	 */
	public $height;
	
	/**
	 * Image format
	 * @var int
	 */
	public $format;
	
	/**
	 * Resizes the image to either fit specified dimensions or to fill them (based on the $fit parameter).
	 *
	 * If only the width or height is provided the image will be resized according to that single dimension.
	 *
	 * If both height and width are present this function will behave according to the $fit parameter.
	 * E.g Provided the image is 400x200 and the dimensions given were 100x100 the image will be resized to
	 * 100x50 if $fit is true, or to 200x100 if it is false.
	 *
	 * @param int $width  Width to fit or fill
	 * @param int $height Height to fit or fill
	 * @param bool $fit   Whether to fit or fill the dimensions.
	 * 
	 * @return \PHPixie\Image\Driver Returns self
	 * @throws \Exception If neither width or height is set
	 */
	public function resize($width = null, $height = null, $fit = true) {
		if ($width && $height) {
			$wscale = $width / $this->width;
			$hscale = $height / $this-> height;
			$scale = $fit ? min($wscale, $hscale) : max($wscale, $hscale);
		}elseif($width) {
			$scale = $width/$this->width;
		}elseif($height) {
			$scale = $height/$this->height;
		}else {
			throw new \Exception("Either width or height must be set");
		}
		
		$this->scale($scale);
		return $this;
	}
	
	/**
	 * Resizes the image to be at least $widthX$height in size and the crops it to those dimensions.
	 * Great for creating fixed-size avatars.
	 *
	 * @param int $width  Width to crop to
	 * @param int $height Height to crop to
	 * 
	 * @return \PHPixie\Image\Driver Returns self
	 */
	public function fill($width, $height){
		$this->resize($width, $height, false);
		$x = (int)($this->width - $width) / 2;
		$y = (int)($this->height - $height) / 2;
		$this->crop($width, $height, $x, $y);
		return $this;
	}
	
	/**
	 * Desttructor. Makes sure to remove the image resource from memory.
	 */
	public function __destruct() {
		$this->destroy();
	}
	
	/**
	 * Wraps text into lines that would fit the specified width
	 *
	 * @param string $text Text to wrap
	 * @param int $size Font size
	 * @param string $font_file Path to font file
	 * @param int $width Width in pixels to fit the text in
	 * 
	 * @return string $text Wrapped text
	 */
	protected function wrap_text($text, $size, $font_file, $width) {
		$blocks = explode("\n", $text);
		$lines = array();
		foreach($blocks as $block) {
			$words = explode(' ', $block);
			$line = '';
			$line_width = 0;
			$count = count($words);
			foreach($words as $key => $word) {
				$prefix = $line == ''?'':' ';
				$box = $this->text_metrics($prefix.$word, $size, $font_file);
				$word_width = $box['width'];
				if ($line == '' || $line_width + $word_width < $width) {
					$line.= $prefix.$word;
					$line_width+= $word_width;
				}else {
					$lines[] = $line;
					$line = $word;
					$box = $this->text_metrics($word, $size, $font_file);
					$line_width = $box['width'];
				}
			}
			$lines[] = $line;
		}
		return implode("\n", $lines);
	}
	
	/**
	 * Gets the file extension of the image
	 *
	 * @param string $file path to image
	 * 
	 * @return string Extension of the image file
	 */
	protected function get_extension($file) {
		$ext = strtolower(pathinfo($file, \PATHINFO_EXTENSION));
		if ($ext == 'jpg')
			$ext = 'jpeg';
		return $ext;
	}
	
	/**
	 * Calculates offset between two lines of text based on font size and line spacing.
	 *
	 * @param int $size Font size
	 * @param int $line_spacing Line spacing multiplier.
	 * 
	 * @return int Line spacing
	 */
	protected function baseline_offset($size, $line_spacing) {
		return $size * $line_spacing;
	}
	
	/**
	 * Calculates text metrics of the specified text. 
	 *
	 * Takes line spacing into account.
	 * Gets width, height, ascender of the first line of text and descender of the last one.
	 *
	 * @param string $text Text to calculate size for
	 * @param int    $size Font size
	 * @param string $font_file Path to font file
	 * @param int    $line_spacing Line spacing multiplier
	 * 
	 * @return array Text metrics
	 */
	public function text_size($text, $size, $font_file, $line_spacing = 1) {
		$lines = explode("\n", $text);
		$box = null;
		$ascender = 0;
		$baseline_offset = $this->baseline_offset($size, $line_spacing);
		foreach($lines as $k=>$line) {
			$line_box = $this->text_metrics($line, $size, $font_file);
			if ($box == null) {
				$box = $line_box;
				$ascender = $line_box['ascender'];
			}else {
				$box['width'] = $line_box['width']>$box['width'] ? $line_box['width'] : $box['width'];
				$box['descender'] = $line_box['descender'];
				$box['height'] = $ascender + $k*$baseline_offset + $line_box['descender'];
			}
		}
		return $box;
	}
	
	/**
	 * Draws text over the image.
	 *
	 * @param string $text         Text to draw
	 * @param int    $size         Font size
	 * @param string $font_file    Path to font file
	 * @param int    $x            X coordinate of the baseline of the first line of text
	 * @param int    $y            Y coordinate of the baseline of the first line of text
	 * @param int    $color        Text color (e.g 0xffffff)
	 * @param float  $opacity      Text opacity
	 * @param int    $wrap_width   Width to wrap text at. Null means no wrapping.
	 * @param int    $line_spacing Line spacing multiplier
	 * @param float  $angle        Counter clockwise text rotation angle
	 * 
	 * @return \PHPixie\Image\Driver Returns self
	 */
	public function text($text, $size, $font_file, $x, $y, $color = 0x000000, $opacity = 1.0, $wrap_width = null, $line_spacing = 1, $angle = 0.0) {
		if ($wrap_width != null)
			$text = $this->wrap_text($text, $size, $font_file, $wrap_width);
			
		$lines = explode("\n", $text);
		$offset_x = 0;
		$offset_y = 0;
		$baseline = $this->baseline_offset($size, $line_spacing);
		foreach($lines as $line){
			$box = $this->draw_text($line, $size, $font_file, $x + $offset_x, $y + $offset_y, $color, $opacity, $angle);
			$rad = deg2rad($angle);
			$offset_x += sin($rad)*$baseline;
			$offset_y += cos($rad)*$baseline;
		}
		return $this;
	}
	
	/**
	 * Creates a blank image and fill it with specified color.
	 *
	 * @param int   $width   Image width
	 * @param int   $height  Image height
	 * @param int   $color   Image color
	 * @param float $opacity Color opacity
	 * 
	 * @return \PHPixie\Image\Driver Returns self
	 */
	public abstract function create($width, $height, $color = 0xffffff, $opacity = 0.0);
	
	/**
	 * Reads image from file.
	 *
	 * @param   string  $file  Image file
	 *
	 * @return  \PHPixie\Image\Driver Initialized Image
	 */
	public abstract function read($file);
	
	/**
	 * Loads image data from a bytestring.
	 *
	 * @param   string  $bytes  Image data
	 *
	 * @return  \PHPixie\Image\Driver Initialized Image
	 */
	public abstract function load($bytes);
	
	/**
	 * Gets color of the pixel at specifed coordinates.
	 * 
	 * Returns array with 'color' and 'opacity' keys
	 *
	 * @param int   $x  X coordinate
	 * @param int   $y  Y coordinate
	 * 
	 * @return array Pixel color data
	 */
	public abstract function get_pixel($x, $y);
	
	/**
	 * Renders and ouputs the image.
	 * 
	 * @param string $format   Image format (gif, png or jpeg)
	 * @param bool   $die      Whether to stop script execution after image has been outputted.
	 * @param int    $quality  Compression quality (0 - 100)
	 * 
	 * @return \PHPixie\Image\Driver Returns self
	 * @throw  \Exception  if the format is not supported
	 */
	public abstract function render($format = 'png', $die = true, $quality = 90);
	
	/**
	 * Saves the image to file. If $format is ommited the format is guessed based on file extension.
	 * 
	 * @param string $file File to save the image to.
	 * @param string $format Image format (gif, png or jpeg)
	 * @param int    $quality  Compression quality (0 - 100)
	 * 
	 * @return \PHPixie\Image\Driver Returns self
	 * @throw  \Exception  if the format is not supported
	 */
	public abstract function save($file, $format = null, $quality = 90);
	
	/**
	 * Destroys the image resource.
	 */
	public abstract function destroy();
	
	/**
	 * Crops the image.
	 *
	 * @param int $width  Width to crop to
	 * @param int $height Height to crop to
	 * @param int $x      X coordinate of crop start position
	 * @param int $y      Y coordinate of crop start position
	 *
	 * @return \PHPixie\Image\Driver Returns self
	 */
	public abstract function crop($width, $height, $x = 0, $y = 0);
	
	/**
	 * Scales the image to the specified ratio.
	 *
	 * @param float $scale Scale ratio
	 *
	 * @return \PHPixie\Image\Driver Returns self
	 */
	public abstract function scale($scale);
	
	/**
	 * Rotates the image counter clockwise.
	 *
	 * @param float $angle     Rotation angle in degrees
	 * @param int   $bg_color  Background color
	 * @param int   $bg_color  Background opacity
	 *
	 * @return \PHPixie\Image\Driver Returns self
	 */
	public abstract function rotate($angle, $bg_color = 0xffffff, $bg_opacity = 0);
	
	/**
	 * Flips the image.
	 *
	 * @param bool $flip_x Whether to flip image horizontally
	 * @param bool $flip_y Whether to flip image vertically
	 *
	 * @return \PHPixie\Image\Driver Returns self
	 */
	public abstract function flip($flip_x = false, $flip_y = false);
	
	/**
	 * Overlays another image over the current one.
	 *
	 * @param \PHPixie\Image\Driver $layer Image to overlay over the current one
	 * @param int $x      X coordinate of the overlay
	 * @param int $y      Y coordinate of the overlay
	 *
	 * @return \PHPixie\Image\Driver Returns self
	 */
	public abstract function overlay($layer, $x = 0, $y = 0);
	
	/**
	 * Gets metris of the specified text.
	 *
	 * Returns an array with keys 'width', 'height', 'ascender' and 'descender'.
	 *
	 * @param string $text Text to get metrics of
	 * @param int    $size Font size
	 * @param string $font_file Path to font file
	 *
	 * @return array Text metrics
	 */
	protected abstract function text_metrics($text, $size, $font_file);
	
	/**
	 * Draws text over the image.
	 *
	 * @param string $text         Text to draw
	 * @param int    $size         Font size
	 * @param string $font_file    Path to font file
	 * @param int    $x            X coordinate of the baseline of the first line of text
	 * @param int    $y            Y coordinate of the baseline of the first line of text
	 * @param int    $color        Text color (e.g 0xffffff)
	 * @param float  $opacity      Text opacity
	 * @param float  $angle        Counter clockwise text rotation angle
	 * 
	 * @return \PHPixie\Image\Driver Returns self
	 */
	protected abstract function draw_text($text, $size, $font_file, $x, $y, $color, $opacity, $angle);
	
	/**
	 * Gets driver specific color representation.
	 *
	 * @param int    $color    Color
	 * @param float  $opacity  Opacity
	 * 
	 * @return mixed Color representation
	 */
	protected abstract function get_color($color, $opacity);
}
