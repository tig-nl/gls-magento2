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
namespace TIG\GLS\Block\Adminhtml\Grid;

use Magento\Backend\Block\Template;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\App\DeploymentConfig\Reader;
use TIG\GLS\Config\Provider\Webshop as WebshopConfig;



class DataProvider extends Template implements BlockInterface
{
    const XPATH_SHOW_GRID_TOOLBAR = 'tig_gls/general/show_grid_toolbar';

    /**
     * @var string
     */
    // @codingStandardsIgnoreLine
    protected $_template = 'TIG_GLS::grid/DataProvider.phtml';

    /**
     * @var Reader
     */
    private $configReader;

    /**
     * @var WebshopConfig
     */
    private $webshopConfig;

    /**
     * DataProvider constructor.
     *
     * @param Template\Context  $context
     * @param Reader            $reader
     * @param WebshopConfig     $webshopConfig
     * @param array             $data
     */
    public function __construct(
        Template\Context $context,
        Reader $reader,
        WebshopConfig $webshopConfig,
        array $data = []
    ) {
        $this->configReader = $reader;
        $this->webshopConfig = $webshopConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getAdminBaseUrl()
    {
        $config = $this->configReader->load();
        $adminSuffix = $config['backend']['frontName'];
        return $this->getBaseUrl() . $adminSuffix . '/';
    }

    /**
     * @return bool
     */
    public function getShowToolbar()
    {
        return $this->webshopConfig->getShowToolbar();
    }
}
