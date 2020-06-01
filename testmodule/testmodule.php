<?php
if (!defined('_PS_VERSION_'))
  exit;
 
class TestModule extends Module
{
  public function __construct()
  {
    $this->name = 'testmodule';
    $this->tab = 'other';
    $this->version = '1.0.0';
    $this->author = 'Korobov Vitalik';
    $this->need_instance = 0;
    $this->bootstrap = true;
 
    parent::__construct();
 
    $this->displayName = $this->l('My test module');
    $this->description = $this->l('Description of my module.');
    $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
 
    if (!Configuration::get('TESTMODULE_NAME'))      
      $this->warning = $this->l('No name provided');
  }
public function install()
{

  if (Shop::isFeatureActive())
    Shop::setContext(Shop::CONTEXT_ALL);
 
  if (!parent::install() ||
    !$this->registerHook('leftColumn') ||
    !$this->registerHook('header') ||
    !$this->registerHook('footer') ||
    !$this->registerHook('home') ||
    !Configuration::updateValue('MIN_PRICE', null)||
    !Configuration::updateValue('MAX_PRICE', null)
  )
    return false;
 
  return true;
}
public function uninstall()
{
  if (!parent::uninstall() ||
      !Configuration::updateValue('MIN_PRICE', null)||
      !Configuration::updateValue('MAX_PRICE', null)
  )
    return false;
 
  return true;
}
    public function getContent()
    {

        if (((bool)Tools::isSubmit('submitTestmodule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->submit_action = 'submitTestmodule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Настройки'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'MIN_PRICE',
                        'label' => $this->l('Минимальная цена'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'MAX_PRICE',
                        'label' => $this->l('Максимальная цена'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Сохранить'),
                ),
            ),
        );
    }
    protected function getConfigFormValues()
    {
        return array(
            'MIN_PRICE' => Configuration::get('MIN_PRICE', null),
            'MAX_PRICE' => Configuration::get('MAX_PRICE', null),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

    }

    public function hookDisplayFooter()
    {
        $min = Configuration::get('MIN_PRICE');
        $max = Configuration::get('MAX_PRICE');
        $query = "SELECT count(id_product) as count FROM ps_product WHERE price BETWEEN ' $min  ' and ' $max '";
        $res = Db::getInstance()->executeS($query);

        foreach ($res AS $row) {
            $arr[] = $row['count'];
        }

        $this->context->smarty->assign('mess', $arr);
        return $this->display(__FILE__, 'testmodule.tpl');

    }

}