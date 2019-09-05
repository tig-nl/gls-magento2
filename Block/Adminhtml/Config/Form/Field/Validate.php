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

namespace TIG\GLS\Block\Adminhtml\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Validate extends Field
{
    const BUTTON_ID        = 'tig_gls_validate';
    const GLS_VALIDATE_URL = 'gls/credentials/validate';

    // @codingStandardsIgnoreLine
    protected $_template = 'TIG_GLS::config/form/validate.phtml';

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context      $context
     * @param UrlInterface $urlBuilder
     * @param array        $data
     */
    // @codingStandardsIgnoreLine
    public function __construct(
        Context $context,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $layout = $this->getLayout();

        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $layout->createBlock('Magento\Backend\Block\Widget\Button');
        $button->setData([
                'id'    => static::BUTTON_ID,
                'label' => __('Validate your GLS account')
        ]);

        $mageInit = ['glsValidate' => [
            'url' => $this->getValidateUrl(),
            'ok-image' => $this->getViewFileUrl('TIG_GLS::images/green-ok.png'),
            'nok-image' => $nokImage = $this->getViewFileUrl('TIG_GLS::images/red-nok.png'),
            'unknown-image' => $nokImage = $this->getViewFileUrl('TIG_GLS::images/orange-unknown.png')
        ]];

        $button->setDataAttribute(['mage-init' => json_encode($mageInit)]);

        return $button->toHtml();
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    // @codingStandardsIgnoreLine
    public function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getValidateUrl()
    {
        return $this->urlBuilder->getUrl(static::GLS_VALIDATE_URL);
    }
}
