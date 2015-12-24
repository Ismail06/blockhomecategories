<?php

/*
*  @author Ismail Albakov <www.wowsite.ru>
*  @copyright  2015
*  @version  1.0
*/

if (!defined('_PS_VERSION_'))
	exit;

class BlockHomeCategories extends Module
{
	public function __construct()
	{
		$this->name = 'blockhomecategories';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Ismail Albakov (wowsite.ru)';

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Categories block on home page');
		$this->description = $this->l('Adds a block of categories and child categories on Homepage.');
	}

	function install()
	{
		$this->_clearCache('*');
		Configuration::updateValue('BLK_FEATURED_CATEGORIES_NBR', 8);
		Configuration::updateValue('BLK_FEATURED_CHILD_CATEGORIES_NBR', 5);
		Configuration::updateValue('BLK_FEATURED_CAT_ID', (int)Context::getContext()->shop->getCategory());

	    if (!parent::install() || !$this->registerHook('displayHome') || !$this->registerHook('header') || !$this->registerHook('categoryUpdate') || !$this->registerHook('DisplayCustomCategories'))
			return false;
	    return true;
	}

	public function uninstall()
	{
		$this->_clearCache('*');

		return parent::uninstall();
	}

	public function _clearCache($template, $cache_id = NULL, $compile_id = NULL)
	{
		parent::_clearCache('blockhomecategories.tpl');
	}

	public function hookCategoryUpdate($params)
	{
		$this->_clearCache('*');
	}

	public function hookDisplayHeader($params)
	{
		$this->hookHeader($params);
	}

	public function hookHeader($params)
	{
		$this->context->controller->addCss(($this->_path).'assets/css/blockhomecategories.css');
	}

	protected function getCacheId($name = null)
	{
		if ($name === null)
			$name = 'blockhomecategories';
		return parent::getCacheId($name.'|'.date('Ymd'));
	}

	public function hookDisplayHome($params)
	{

		if (!$this->isCached('blockhomecategories.tpl', $this->getCacheId('blockhomecategories')))
		{

	        $categoryRoot = new Category((int)Configuration::get('BLK_FEATURED_CAT_ID'),$this->context->language->id,$this->context->shop->id);
	        $categoriesHome = $categoryRoot->getSubCategories($this->context->language->id, true, true);
	        array_splice($categoriesHome, (int)Configuration::get('BLK_FEATURED_CATEGORIES_NBR'));

	        foreach ($categoriesHome as $key => $item)
	        {

	        	if ($item['level_depth'] > 1)
	        	{
	        		$categoryRoot = new Category($item['id_category'], $this->context->language->id, $this->context->shop->id);
	        		$categoriesHome[$key]['childcategory'] = $categoryRoot->getSubCategories($this->context->language->id, true, true);
	        		$categoriesHome[$key]['count_cild'] = count($categoriesHome[$key]['childcategory']);
	        		array_splice($categoriesHome[$key]['childcategory'], (int)Configuration::get('BLK_FEATURED_CHILD_CATEGORIES_NBR'));
	        	}
	        	 	
	        }

	        $this->smarty->assign(array(
	            'categories' => $categoriesHome,
	            'max_child_cats' => (int)Configuration::get('BLK_FEATURED_CHILD_CATEGORIES_NBR'),
	            'homeSize' => Image::getSize('medium_default')
	        ));
	    }

        return $this->display(__FILE__, 'blockhomecategories.tpl', $this->getCacheId('blockhomecategories'));
	}

	public function hookDisplayCustomCategories($params)
	{
		return $this->hookDisplayHome($params);
	}

	public function getContent()
	{
		$output = '';
		$errors = array();
		if (Tools::isSubmit('submitblockhomecategories'))
		{
			$nbr = Tools::getValue('BLK_FEATURED_CATEGORIES_NBR');
			if (!Validate::isInt($nbr) || $nbr <= 0)
			$errors[] = $this->l('The number of parent categories is invalid. Please enter a positive number.');

			$cat = Tools::getValue('BLK_FEATURED_CAT_ID');
			if (!Validate::isInt($cat) || $cat <= 0)
				$errors[] = $this->l('The category ID is invalid. Please choose an existing category ID.');

			$child = Tools::getValue('BLK_FEATURED_CHILD_CATEGORIES_NBR');
			if (!Validate::isInt($child) || $child <= 0)
				$errors[] = $this->l('Set maximum child categories to display. Please enter a positive number.');

			if (isset($errors) && count($errors)) 
			{
				$output = $this->displayError(implode('<br />', $errors));
			}
			else
			{
				Configuration::updateValue('BLK_FEATURED_CATEGORIES_NBR', (int)$nbr);
				Configuration::updateValue('BLK_FEATURED_CAT_ID', (int)$cat);
				Configuration::updateValue('BLK_FEATURED_CHILD_CATEGORIES_NBR', (int)$child);
				Tools::clearCache(Context::getContext()->smarty, $this->getTemplatePath('blockhomecategories.tpl'));
				$output = $this->displayConfirmation($this->l('Your settings have been updated.'));
			}
		}

		return $output.$this->renderForm();
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'description' => $this->l('Fill inputs below.'),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Number of categories to be displayed'),
						'name' => 'BLK_FEATURED_CATEGORIES_NBR',
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Set the number of categories that you would like to display on homepage (default: 8).'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Choose the parent category ID'),
						'name' => 'BLK_FEATURED_CAT_ID',
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Choose the parent category ID (default: 2 for "Home").'),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Set maximum child categories to display'),
						'name' => 'BLK_FEATURED_CHILD_CATEGORIES_NBR',
						'class' => 'fixed-width-xs',
						'desc' => $this->l('Set maximum child categories to display (default: 5).'),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitblockhomecategories';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			'BLK_FEATURED_CATEGORIES_NBR' => Tools::getValue('BLK_FEATURED_CATEGORIES_NBR', (int)Configuration::get('BLK_FEATURED_CATEGORIES_NBR')),
			'BLK_FEATURED_CAT_ID' => Tools::getValue('BLK_FEATURED_CAT_ID', (int)Configuration::get('BLK_FEATURED_CAT_ID')),
			'BLK_FEATURED_CHILD_CATEGORIES_NBR' => Tools::getValue('BLK_FEATURED_CHILD_CATEGORIES_NBR', (int)Configuration::get('BLK_FEATURED_CHILD_CATEGORIES_NBR')),
		);
	}

}
