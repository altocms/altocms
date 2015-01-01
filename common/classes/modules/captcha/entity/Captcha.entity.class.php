<?php
/*---------------------------------------------------------------------------
 * @Project: Alto CMS
 * @Project URI: http://altocms.com
 * @Description: Advanced Community Engine
 * @Copyright: Alto CMS Team
 * @License: GNU GPL v2 & MIT
 *----------------------------------------------------------------------------
 * Based on
 *   KCAPTCHA PROJECT VERSION 2.0
 *   Copyright by Kruglov Sergei, 2006, 2007, 2008, 2011
 *   www.captcha.ru, www.kruglov.ru
 *
 * KCAPTCHA is a free software. You can freely use it for developing own site or software.
 * If you use this software as a part of own sofware, you must leave copyright notices intact or add KCAPTCHA copyright notices to own.
 * As a default configuration, KCAPTCHA has a small credits text at bottom of CAPTCHA image.
 * You can remove it, but I would be pleased if you left it. ;)
 *----------------------------------------------------------------------------
 */

/**
 * Объект сущности комментариев
 *
 * @package modules.captcha
 * @since   1.1
 */
class ModuleCaptcha_EntityCaptcha extends Entity {

    protected $aCfg;
    protected $sKeyString = '';
    protected $xImage = null;

    public function Init() {

        parent::Init();

        $this->aCfg = Config::GetData('module.captcha');
        $this->Reset();
    }

    public function Reset() {

        $sFontsDir = realpath(__DIR__ . '/../' . $this->aCfg['fonts_dir']);
        $aFonts = glob($sFontsDir . '/*.png');

        $iAlphabetLength = strlen($this->aCfg['alphabet']);
        $iAllowedLength = strlen($this->aCfg['allowed_symbols']);

        $iStringLength = 3;
        if (is_array($this->aCfg['length']) && count($this->aCfg['length']) > 1) {
            list($iMin, $iMax) = $this->aCfg['length'];
            $iStringLength = mt_rand($iMin, $iMax);
        }

        do {
            // generating random keystring
            while (true) {
                $this->sKeyString = '';
                for ($i = 0; $i < $iStringLength; $i++) {
                    $sSymbol = $this->aCfg['allowed_symbols'][mt_rand(0, $iAllowedLength - 1)];
                    $this->sKeyString .= $sSymbol;
                }
                if (!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $this->sKeyString)) {
                    break;
                }
            }

            $sFontFile = $aFonts[mt_rand(0, count($aFonts) - 1)];
            $xFontImg = imagecreatefrompng($sFontFile);
            imagealphablending($xFontImg, true);

            $iFontfileWidth = imagesx($xFontImg);
            $iFontfileHeight = imagesy($xFontImg) - 1;

            $aFontMetrics = array();
            $iSymbol = 0;
            $bReadingSymbol = false;

            // loading font
            for ($i = 0; $i < $iFontfileWidth && $iSymbol < $iAlphabetLength; $i++) {
                $bTransparent = (imagecolorat($xFontImg, $i, 0) >> 24) == 127;

                if (!$bReadingSymbol && !$bTransparent) {
                    $aFontMetrics[$this->aCfg['alphabet'][$iSymbol]] = array('start' => $i);
                    $bReadingSymbol = true;
                    continue;
                }

                if ($bReadingSymbol && $bTransparent) {
                    $aFontMetrics[$this->aCfg['alphabet'][$iSymbol]]['end'] = $i;
                    $bReadingSymbol = false;
                    $iSymbol++;
                    continue;
                }
            }

            $iImgWidth = $this->aCfg['image.width'];
            $iImgHeight = $this->aCfg['image.height'];
            $iAmplitude = $this->aCfg['image.fluctuation_amplitude'];
            $fWhiteNoise = $this->aCfg['image.white_noise_density'];
            $fBlackNoise = $this->aCfg['image.black_noise_density'];

            $aForegroundColor = array(
                mt_rand($this->aCfg['image.foreground_color'][0][0], $this->aCfg['image.foreground_color'][0][1]),
                mt_rand($this->aCfg['image.foreground_color'][1][0], $this->aCfg['image.foreground_color'][1][1]),
                mt_rand($this->aCfg['image.foreground_color'][2][0], $this->aCfg['image.foreground_color'][2][1]),
            );
            $aBackgroundColor = array(
                mt_rand($this->aCfg['image.background_color'][0][0], $this->aCfg['image.background_color'][0][1]),
                mt_rand($this->aCfg['image.background_color'][1][0], $this->aCfg['image.background_color'][1][1]),
                mt_rand($this->aCfg['image.background_color'][2][0], $this->aCfg['image.background_color'][2][1]),
            );

            $img = imagecreatetruecolor($iImgWidth, $iImgHeight);
            imagealphablending($img, true);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);

            imagefilledrectangle($img, 0, 0, $iImgWidth - 1, $iImgHeight - 1, $white);

            // draw text
            $x = 1;
            $odd = mt_rand(0, 1);
            if ($odd == 0) {
                $odd = -1;
            }
            for ($i = 0; $i < $iStringLength; $i++) {
                $m = $aFontMetrics[$this->sKeyString[$i]];

                $y = (($i % 2) * $iAmplitude - $iAmplitude / 2) * $odd
                    + mt_rand(-round($iAmplitude / 3), round($iAmplitude / 3))
                    + ($iImgHeight - $iFontfileHeight) / 2;

                if ($this->aCfg['image.no_spaces']) {
                    $iShift = 0;
                    if ($i > 0) {
                        $iShift = 10000;
                        for ($sy = 3; $sy < $iFontfileHeight - 10; $sy += 1) {
                            for ($sx = $m['start'] - 1; $sx < $m['end']; $sx += 1) {
                                $rgb = imagecolorat($xFontImg, $sx, $sy);
                                $opacity = $rgb >> 24;
                                if ($opacity < 127) {
                                    $iLeft = $sx - $m['start'] + $x;
                                    $py = $sy + $y;
                                    if ($py > $iImgHeight) {
                                        break;
                                    }
                                    for ($px = min($iLeft, $iImgWidth - 1); $px > $iLeft - 200 && $px > 0; $px -= 1) {
                                        $color = imagecolorat($img, $px >= 0 ? $px : 0, $py >= 0 ? $py : 0) & 0xff;
                                        if ($color + $opacity < 170) { // 170 - threshold
                                            if ($iShift > $iLeft - $px) {
                                                $iShift = $iLeft - $px;
                                            }
                                            break;
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        if ($iShift == 10000) {
                            $iShift = mt_rand(4, 6);
                        }

                    }
                } else {
                    $iShift = 1;
                }
                imagecopy($img, $xFontImg, $x - $iShift, $y, $m['start'], 1, $m['end'] - $m['start'], $iFontfileHeight);
                $x += $m['end'] - $m['start'] - $iShift;
            }
        } while ($x >= $iImgWidth - 10); // while not fit in canvas

        //noise
        $white = imagecolorallocate($xFontImg, 255, 255, 255);
        $black = imagecolorallocate($xFontImg, 0, 0, 0);
        for ($i = 0; $i < (($iImgHeight - 30) * $x) * $fWhiteNoise; $i++) {
            imagesetpixel($img, mt_rand(0, $x - 1), mt_rand(10, $iImgHeight - 15), $white);
        }
        for ($i = 0; $i < (($iImgHeight - 30) * $x) * $fBlackNoise; $i++) {
            imagesetpixel($img, mt_rand(0, $x - 1), mt_rand(10, $iImgHeight - 15), $black);
        }

        $iCenter = $x / 2;

        // credits. To remove, see configuration file
        $this->xImage = imagecreatetruecolor($iImgWidth, $iImgHeight + ($this->aCfg['show_credits'] ? 12 : 0));
        $foreground = imagecolorallocate($this->xImage, $aForegroundColor[0], $aForegroundColor[1], $aForegroundColor[2]);
        $background = imagecolorallocate($this->xImage, $aBackgroundColor[0], $aBackgroundColor[1], $aBackgroundColor[2]);

        imagefilledrectangle($this->xImage, 0, 0, $iImgWidth - 1, $iImgHeight - 1, $background);
        imagefilledrectangle($this->xImage, 0, $iImgHeight, $iImgWidth - 1, $iImgHeight + 12, $foreground);

        if ($this->aCfg['show_credits']) {
            $sCredits = empty($this->aCfg['credits']) ? $_SERVER['HTTP_HOST'] : $this->aCfg['credits'];
            imagestring($this->xImage, 2, $iImgWidth / 2 - imagefontwidth(2) * strlen($sCredits) / 2, $iImgHeight - 2, $sCredits, $background);
        }

        // periods
        $rand1 = mt_rand(750000, 1200000) / 10000000;
        $rand2 = mt_rand(750000, 1200000) / 10000000;
        $rand3 = mt_rand(750000, 1200000) / 10000000;
        $rand4 = mt_rand(750000, 1200000) / 10000000;
        // phases
        $rand5 = mt_rand(0, 31415926) / 10000000;
        $rand6 = mt_rand(0, 31415926) / 10000000;
        $rand7 = mt_rand(0, 31415926) / 10000000;
        $rand8 = mt_rand(0, 31415926) / 10000000;
        // amplitudes
        $rand9 = mt_rand(330, 420) / 110;
        $rand10 = mt_rand(330, 450) / 100;

        //wave distortion

        for ($x = 0; $x < $iImgWidth; $x++) {
            for ($y = 0; $y < $iImgHeight; $y++) {
                $sx = $x + (sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6)) * $rand9 - $iImgWidth / 2 + $iCenter + 1;
                $sy = $y + (sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8)) * $rand10;

                if ($sx < 0 || $sy < 0 || $sx >= $iImgWidth - 1 || $sy >= $iImgHeight - 1) {
                    continue;
                } else {
                    $color = imagecolorat($img, $sx, $sy) & 0xFF;
                    $color_x = imagecolorat($img, $sx + 1, $sy) & 0xFF;
                    $color_y = imagecolorat($img, $sx, $sy + 1) & 0xFF;
                    $color_xy = imagecolorat($img, $sx + 1, $sy + 1) & 0xFF;
                }

                if ($color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255) {
                    continue;
                } else {
                    if ($color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0) {
                        $newred = $aForegroundColor[0];
                        $newgreen = $aForegroundColor[1];
                        $newblue = $aForegroundColor[2];
                    } else {
                        $frsx = $sx - floor($sx);
                        $frsy = $sy - floor($sy);
                        $frsx1 = 1 - $frsx;
                        $frsy1 = 1 - $frsy;

                        $newcolor = (
                            $color * $frsx1 * $frsy1 +
                            $color_x * $frsx * $frsy1 +
                            $color_y * $frsx1 * $frsy +
                            $color_xy * $frsx * $frsy);

                        if ($newcolor > 255) {
                            $newcolor = 255;
                        }
                        $newcolor = $newcolor / 255;
                        $newcolor0 = 1 - $newcolor;

                        $newred = $newcolor0 * $aForegroundColor[0] + $newcolor * $aBackgroundColor[0];
                        $newgreen = $newcolor0 * $aForegroundColor[1] + $newcolor * $aBackgroundColor[1];
                        $newblue = $newcolor0 * $aForegroundColor[2] + $newcolor * $aBackgroundColor[2];
                    }
                }

                imagesetpixel($this->xImage, $x, $y, imagecolorallocate($this->xImage, $newred, $newgreen, $newblue));
            }
        }

    }

    public function Display() {

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        if (function_exists("imagejpeg")) {
            header("Content-Type: image/jpeg");
            imagejpeg($this->xImage, null, $this->aCfg['image.jpeg_quality']);
        } else {
            if (function_exists("imagegif")) {
                header("Content-Type: image/gif");
                imagegif($this->xImage);
            } else {
                if (function_exists("imagepng")) {
                    header("Content-Type: image/x-png");
                    imagepng($this->xImage);
                }
            }
        }
    }

    function getKeyString() {

        return $this->sKeyString;
    }

}

// EOF