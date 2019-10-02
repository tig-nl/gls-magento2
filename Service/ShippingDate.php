<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */

namespace TIG\GLS\Service;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use TIG\GLS\Model\Config\Provider\Carrier;

class ShippingDate
{
    /** @var TimezoneInterface $timezone */
    private $timezone;

    /** @var Carrier $carrierConfig */
    private $carrierConfig;

    /**
     * ShippingDate constructor.
     *
     * @param TimezoneInterface $timezone
     * @param Carrier           $carrierConfig
     */
    public function __construct(
        TimezoneInterface $timezone,
        Carrier $carrierConfig
    ) {
        $this->timezone      = $timezone;
        $this->carrierConfig = $carrierConfig;
    }

    /**
     * @param      $format
     * @param bool $useProcessingTime
     *
     * @return string
     */
    public function calculate($format, $useProcessingTime = true)
    {
        $currentTime    = $this->timezone->date();
        $currentTime    = $currentTime->format('H:m:s');
        $cutOffTime     = $this->carrierConfig->getCutOffTime();
        $shippingDate   = $this->timezone->date(null, null, true, false);

        if ($useProcessingTime) {
            $processingTime = $this->carrierConfig->getProcessingTime();
            $shippingDate->modify("+ $processingTime days");
        }

        if ($currentTime > $cutOffTime) {
            $shippingDate->modify("+ 1 days");
        }

        return $shippingDate->format($format);
    }
}
