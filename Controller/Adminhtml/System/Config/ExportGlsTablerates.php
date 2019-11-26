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

namespace TIG\GLS\Controller\Adminhtml\System\Config;

use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;

class ExportGlsTablerates extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    // @codingStandardsIgnoreLine
    private $fileFactory;

    /**
     * ExportGlsTablerates constructor.
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Config\Model\Config\Structure           $configStructure
     * @param ConfigSectionChecker                             $sectionChecker
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        ConfigSectionChecker $sectionChecker,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        parent::__construct(
            $context,
            $configStructure,
            $sectionChecker
        );
    }

    /**
     * Export shipping table rates in csv format
     *
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        $fileName = 'gls_tablerates.csv';

        /** @var $gridBlock \TIG\GLS\Block\Adminhtml\Carrier\GLS\Grid */
        // @codingStandardsIgnoreLine
        $gridBlock = $this->_view->getLayout()->createBlock(
            \TIG\GLS\Block\Adminhtml\Carrier\GLS\Grid::class
        );

        $content = $gridBlock->getCsvFile();

        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
