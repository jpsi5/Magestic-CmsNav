<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Magestic
 * @package     Magestic_CmsNav
 * @copyright Copyright (c) 2006-2018 Magento, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Cms page edit form main tab
 *
 * @category    Magestic
 * @package     Magestic_CmsNav
 * @author      Justin Simon <justin.simon0019@gmail.com>
 */
class Magestic_CmsNav_Model_Observer
{
	/**
     * Adding custom field to Main tab for navigation menu display option
     *
     * @param Varien_Event_Observer $observer
     * @return Enterprise_Cms_Model_Observer
     */
	public function onMainTabPrepareForm(Varien_Event_Observer $observer)
	{
        $form = $observer->getEvent()->getForm();
        $baseFieldset = $form->getElement('base_fieldset');

        $page = Mage::registry('cms_page');

        if ($page) {
        	$baseFieldset->addField('show_in_menu', 'select', array(
                'label'     => Mage::helper('adminhtml')->__('Visible In Navigation Menu'),
                'title'     => Mage::helper('adminhtml')->__('Visible In Navigation Menu'),
                'name'      => 'show_in_menu',
                'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
            ),'identifier');

            $baseFieldset->addField('show_in_menu_priority', 'text', array(
                'label'     => Mage::helper('adminhtml')->__('Menu Sort Order'),
                'title'     => Mage::helper('adminhtml')->__('Menu Sort Order'),
                'class'     => 'validate-zero-or-greater',
                'name'      => 'show_in_menu_priority'
            ),'show_in_menu');
        }

        return $this;
	}

    /**
     * Adds catalog categories to top menu
     *
     * @param Varien_Event_Observer $observer
     */
    public function addCmsPageToTopmenuItems(Varien_Event_Observer $observer)
    {
        // $block = $observer->getEvent()->getBlock();
        // $block->addCacheTag(Mage_Catalog_Model_Category::CACHE_TAG);
        $storeId = Mage::app()->getStore()->getStoreId();
        $this->_addCmsPagesToMenu(
            Mage::getModel('cms/page')
                ->getCollection()
                ->addStoreFilter($storeId)
                ->setOrder('show_in_menu_priority', 'ASC'), 
            $observer->getMenu());
    }

    /**
     * Recursively adds categories to top menu
     *
     * @param Mage_Cms_Model_Page_Collection|array $pages
     * @param Varien_Data_Tree_Node $parentNode
     */
    protected function _addCmsPagesToMenu($pages, $parentNode)
    {
        foreach ($pages as $page) {
            if (!$page->getShowInMenu()) {
                continue;
            }

            $nodeId = 'cms-node-' . $page->getPageId();

            $tree = $parentNode->getTree();
            $pageData = array(
                'name' => $page->getTitle(),
                'id' => $nodeId,
                'url' => Mage::getBaseUrl() . $page->getIdentifier() . '/',
                'is_active' => $page->getShowInMenu()
            );
            $pageNode = new Varien_Data_Tree_Node($pageData, 'id', $tree, $parentNode);
            $parentNode->addChild($pageNode);
        }
    }
}