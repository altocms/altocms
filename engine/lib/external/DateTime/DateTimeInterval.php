<?php
/**
 * DateTimeInterval class file
 *
 * @author Vadim Shemarov <vshemarov[at]gamil[dot]com>
 */

class DateTimeInterval
{
    const PT1S = 1; // 1; - 1 second
    const PT1M = 60; // 60 * 1; - 1 minute
    const PT1H = 3600; // 60 * 60 * 1; - 1 hour
    const P1D = 86400; // 60 * 60 * 24 * 1; - 1 day
    const P1W = 604800; // 60 * 60 * 24 * 7; - 1 week
    const P1M = 2592000; // 60 * 60 * 24 * 30; - 1 month
    const P1Y = 31536000; // 60 * 60 * 24 * 365; - 1 year

    protected $oDT;

    public function __construct($sInterval)
    {
        try {
            if (substr($sInterval, 0, 1) == 'P') {
                $this->oDT = new DateInterval($sInterval);
            } else {
                $this->oDT = DateInterval::createFromDateString($sInterval);
            }
        } catch (Exception $oE) {
            $this->oDT = new DateInterval(self::Normalize($sInterval));
        }
    }

    public function Seconds()
    {
        return ($this->oDT->y * self::P1Y)
            + ($this->oDT->m * self::P1M)
            + ($this->oDT->d * self::P1D)
            + ($this->oDT->h * self::PT1H)
            + ($this->oDT->i * self::PT1M)
            + $this->oDT->s;
    }

    /**
     * Normalizes interval according by ISO 8601
     *
     * @param   string  $sInterval
     * @return  string
     */
    static public function Normalize($sInterval)
    {
        $sResult = '';
        if (preg_match('/P(?<y>\d+Y)?(?<m>\d+M)?(?<w>\d+W)?(?<d>\d+D)?(T)?(?<th>\d+H)?(?<tm>\d+M)?(?<ti>\d+I)?(?<ts>\d+S)?/', $sInterval, $aM)) {
            $sP = '';
            $sT = '';
            if (isset($aM['y'])) $sP .= $aM['y'];
            if (isset($aM['m'])) $sP .= $aM['m'];
            // не может быть использован совместно с D
            if (isset($aM['w']) && !isset($aM['d'])) $sP .= $aM['d'];
            if (isset($aM['d'])) $sP .= $aM['d'];
            if (isset($aM['th'])) $sT .= $aM['th'];
            if (isset($aM['tm'])) $sT .= $aM['tm'];
            // нестандартный I заменяем на M
            if (!isset($aM['tm']) && isset($aM['ti'])) $sT .= str_replace('I', 'M', $aM['ti']);
            if (isset($aM['ts'])) $sT .= $aM['ts'];
            if ($sP || $sT) $sResult = 'P';
            if ($sP) $sResult .= $sP;
            if ($sT) $sResult .= 'T' . $sT;
        }
        if ($sResult) {
            return $sResult;
        }
        return 'PT0S';
    }

}

// EOF