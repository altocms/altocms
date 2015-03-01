<?php

namespace PHPixie\Image;

/**
 * GD Image driver.
 *
 * @package  Image
 */
class GD extends Driver{

	/**
	 * GD image resource
	 * @var resource
	 */
	public $image;
	
	public function create($width, $height, $color = 0xffffff, $opacity = 0) {
		$image = $this->create_gd($width, $height, $color, $opacity);
		$this->set_image($image, $width, $height);
		imagefilledrectangle($image, 0, 0, $width, $height, $this->get_color($color, $opacity));
		$this->format = 'png';
		return $this;
	}
	
	public function read($file) {
		$size = getimagesize($file);
		
		if (!$size)
			throw new \Exception("File is not a valid image");
			
		switch($size["mime"]) {
			case "image/png":
				$image = imagecreatefrompng($file);
				$this->format = 'png';
				break;
			case "image/jpeg":
				$image = imagecreatefromjpeg($file);
				$this->format = 'jpeg';
				break;
			case "image/gif":
				$this->format = 'gif';
				$image = imagecreatefromgif($file);
				break;
			default: 
				throw new \Exception("File is not a valid image");
				break;
		}
		
		imagealphablending($image, false);
		$this->set_image($image, $size[0], $size[1]);
		return $this;
	}
	
	public function load($bytes) {
		$image = imagecreatefromstring($bytes);
		imagealphablending($image, false);
		$this->set_image($image, imagesx($image), imagesy($image));
		$this->format = 'png';
		return $this;
	}
	
	/**
	 * Replaces the image resource with a new image
	 *
	 * @param resource $image  Image resource
	 * @param int      $width  New image width
	 * @param int      $height New image height
	 */
	protected function set_image($image, $width, $height) {
		if($this->image)
			imagedestroy($this->image);
		
		$this->image = $image;
		$this->width = $width;
		$this->height = $height;
	}
	
	/**
	 * Creates new GD Image
	 *
	 * @param int $width  Image width
	 * @param int $height Image height
	 *
	 * @return resource New GD image resource 
	 */
	protected function create_gd($width, $height) {
		$image = imagecreatetruecolor($width, $height);
		imagealphablending($image, false);
		return $image;
	}
	
	protected function get_color($color, $opacity) {
		$r = ($color >> 16) & 0xFF;
		$g = ($color >> 8) & 0xFF;
		$b = $color & 0xFF;
		return imagecolorallocatealpha($this->image, $r, $g, $b, 127*(1-$opacity));
	}
	
	public function get_pixel($x, $y) {
		$pixel = imagecolorat($this->image, $x, $y);
		$rgba = imagecolorsforindex($this->image, $pixel);
		return array(
			'color' => ($rgba['red'] << 16) + ($rgba['green'] << 8) + $rgba['blue'],
			'opacity' => 1 - $rgba['alpha'] / 127
		);
	}
	
	/**
	 * Creates image copy with white background for saving in JPEG format
	 *
	 * @return resource Image on white background
	 */
	protected function jpg_bg() {
		$bg = $this->create_gd($this->width, $this->height);
		imagefilledrectangle($bg, 0, 0, $this->width, $this->height, $this->get_color(0xffffff, 1));
		imagealphablending($bg, true);
		imagecopy($bg, $this->image, 0, 0, 0, 0, $this->width, $this->height);
		imagealphablending($bg, false);
		return $bg;
	}
	
	public function render($format = 'png', $die = true, $quality = 90) {
		switch($format) {
			case 'png':
				header('Content-Type: image/png');
				imagesavealpha($this->image, true);
				imagepng($this->image);
				break;
			case 'jpeg':
				header('Content-Type: image/jpeg');
				$bg = $this->jpg_bg($this->image);
				imagejpeg($bg, null, $quality);
				imagedestroy($bg);
				break;
			case 'gif':
				header('Content-Type: image/gif');
				imagegif($this->image);
				break;
			default:
				throw new \Exception("Type must be either png, jpeg or gif");
		}
		
		if($die){
			die;
		}
	}
	
	public function save($file, $format = null, $quality = 90) {
		if ($format == null)
			$format = $this->get_extension($file);
			
		switch($format) {
			case 'png':
				imagesavealpha($this->image, true);
				imagepng($this->image, $file);
				break;
			case 'jpeg':
				$bg = $this->jpg_bg($this->image);
				imagejpeg($bg, $file, $quality);
				imagedestroy($bg);
				break;
			case 'gif':
				imagegif($this->image, $file);
				break;
			default:
				throw new \Exception("Type must be either png, jpeg or gif");
		}
		return $this;
	}
	
	public function destroy() {
		if($this->image !== null) {
			imagedestroy($this->image);
			$this->image = null;
		}
	}
	
	public function crop($width, $height, $x = 0, $y = 0) {
		if ($width > ($maxwidth = $this->width-$x))
			$width = $maxwidth;
		
		if ($height > ($maxheight = $this->height-$y))
			$height = $maxheight;
			
		$cropped = $this->create_gd($width, $height);
		imagecopy($cropped, $this->image, 0, 0, $x, $y, $width, $height);
		$this->set_image($cropped, $width, $height);
		return $this;
	}
	
	public function scale($scale) {
		$width = ceil($this->width*$scale);
		$height = ceil($this->height*$scale);
		
		return $this->resize($width, $height);
	}

	public function resize($width, $height, $fit = true) {
		$resized = $this->create_gd($width, $height);
		imagecopyresampled($resized, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		$this->set_image($resized, $width, $height);
		return $this;
	}

	public function rotate($angle, $bg_color = 0xffffff, $bg_opacity = 0) {
		$rotated = imagerotate($this->image, $angle, $this->get_color($bg_color, $bg_opacity));
		imagealphablending($rotated, false);
		$this->set_image($rotated, imagesx($rotated), imagesy($rotated));
		return $this;
	}
	
	public function flip($flip_x = false, $flip_y = false) {
		if (!$flip_x && !$flip_y)
			return $this;
			
		$x = $flip_x ? $this->width-1 : 0;;
		$width = ($flip_x?-1:1) * $this->width;
		
		$y = $flip_y ? $this->height-1 : 0;;
		$height = ($flip_y?-1:1) * $this->height;
		
		$flipped = $this->create_gd($this->width, $this->height);
		imagecopyresampled($flipped, $this->image, 0, 0, $x, $y, $this->width, $this->height, $width, $height);
		$this->set_image($flipped, $this->width, $this->height);
		return $this;
	}
	
	public function overlay($layer, $x = 0, $y = 0) {
		imagealphablending($this->image, true);
		imagecopy($this->image, $layer->image, $x, $y, 0, 0, $layer->width, $layer->height);
		imagealphablending($this->image, false);
		return $this;
	}
	
	protected function draw_text($text, $size, $font_file, $x, $y, $color, $opacity, $angle) {
		$rad = deg2rad($angle);
		$size = floor($size * 72 / 96);
		$color = $this->get_color($color, $opacity);
		
		imagealphablending($this->image, true);
		imagettftext($this->image, $size, $angle, $x, $y, $color, $font_file, $text);
		imagealphablending($this->image, false);
		return $this;
	}
	
	protected function text_metrics($text, $size, $font_file) {
		$size = floor($size*72/96);
		$box = imagettfbbox($size, 0, $font_file, $text);
		return array(
			'ascender'  => -$box[7],
			'descender' => $box[3],
			'width'     => $box[2] - $box[6],
			'height'    => $box[3] - $box[7]
		);
	}
}
