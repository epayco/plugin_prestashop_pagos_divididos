<?php

/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use EpaycoOrder as EpaycoOrder;
use SplitRules as SplitRules;

include(_PS_MODULE_DIR_ . 'payco/lib/EpaycoOrder.php');
include(_PS_MODULE_DIR_ . 'payco/lib/CreditCard_Order.php');
include(_PS_MODULE_DIR_ . 'payco/lib/CreditCard_OrderState.php');
include(_PS_MODULE_DIR_ . 'payco/lib/SplitRules.php');

class Payco extends PaymentModule
{
    protected $config_form = false;
    private $_html = '';
    private $_postErrors = array();
    public $orderStates;
    public $p_cust_id_cliente;
    public $p_key;
    public $public_key;
    public $private_key;
    public $p_test_request;
    public $P_SPLIT_PRIMARY_RECEIVER_FEE;
    public $p_split_type;
    public $p_type_checkout;
    public $p_url_response;
    public $p_url_confirmation;
    public $p_state_end_transaction;
    public $p_titulo;


    public function __construct()
    {

        $this->name = 'payco';
        $this->tab = 'payments_gateways';
        $this->version = '1.8.0.1';
        $this->author = 'ePayco';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();
        $this->displayName = $this->l('ePayco pagos divididos');
        $this->description = $this->l('ePayco: Paga con Tarjeta de crédito/débito nacional e internacional, PSE, Daviplata, Nequi, Paypal, Efectivo, Safetypay y muchos más.');
        $this->confirmUninstall = $this->l('Esta seguro de desistalar este modulo?');

        $config = Configuration::getMultiple(array(
            'P_CUST_ID_CLIENTE',
            'P_KEY',
            'PUBLIC_KEY',
            'PRIVATE_KEY',
            'P_TEST_REQUEST',
            'P_TITULO',
            //'P_SPLIT_PRIMARY_RECEIVER_FEE',
            'P_STATE_END_TRANSACTION',
            'p_split_type',
            'P_TYPE_CHECKOUT',
            'P_REDUCE_STOCK_PENDING'
        ));

        $config = Configuration::getMultiple(array(
            'P_CUST_ID_CLIENTE',
            'P_KEY',
            'PUBLIC_KEY',
            'PRIVATE_KEY',
            'P_TEST_REQUEST',
            'P_TITULO',
            //'P_SPLIT_PRIMARY_RECEIVER_FEE',
            'P_STATE_END_TRANSACTION',
            'p_split_type',
            'P_TYPE_CHECKOUT',
            'P_REDUCE_STOCK_PENDING'
        ));

        if (isset($config['P_CUST_ID_CLIENTE']))
            $this->p_cust_id_cliente = trim($config['P_CUST_ID_CLIENTE']);
        if (isset($config['P_KEY']))
            $this->p_key = trim($config['P_KEY']);
        if (isset($config['PUBLIC_KEY']))

            $this->public_key = trim($config['PUBLIC_KEY']);
        if (isset($config['PRIVATE_KEY']))
            $this->private_key = trim($config['PRIVATE_KEY']);
        if (isset($config['P_TEST_REQUEST']))
            $this->p_test_request = $config['P_TEST_REQUEST'];
        if (isset($config['P_TYPE_CHECKOUT']))
            $this->p_type_checkout = $config['P_TYPE_CHECKOUT'];

        if (isset($config['P_TITULO']))
            $this->p_titulo = trim($config['P_TITULO']);
        if (isset($config['P_SPLIT_PRIMARY_RECEIVER_FEE']))
            $this->p_state_end_transaction = $config['P_STATE_END_TRANSACTION'];

        //$this->P_SPLIT_PRIMARY_RECEIVER_FEE = trim($config['P_SPLIT_PRIMARY_RECEIVER_FEE']);

        //$this->P_SPLIT_PRIMARY_RECEIVER_FEE = trim($config['P_SPLIT_PRIMARY_RECEIVER_FEE']);
        if (isset($config['P_REDUCE_STOCK_PENDING']))
            $this->p_reduce_stock_pending = $config['P_REDUCE_STOCK_PENDING'];
        if (isset($config['p_split_type']))
            $this->p_split_type = $config['p_split_type'];

        if (!isset($this->p_cust_id_cliente) or !isset($this->p_key) or !isset($this->public_key))
            $this->warning = $this->l('P_CUST_ID_CLIENTE, P_KEY, PRIVATE_KEY y PUBLIC_KEY deben estar configurados para utilizar este módulo correctamente');

        if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
            $this->warning = $this->l('No currency set for this module');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if ((int)Configuration::get('payco') == 1) {
            $this->_errors[] = $this->l('El modulo ePayco actualmente ya esta instado');
            return false;
        }

        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        Configuration::updateValue('P_TITULO', 'ePayco: Paga con Tarjeta de crédito/débito nacional e internacional, PSE, Daviplata, Nequi, Paypal, Efectivo, Safetypay y muchos más.');
        Configuration::updateValue('P_CUST_ID_CLIENTE', '');
        Configuration::updateValue('P_KEY', '');
        Configuration::updateValue('PUBLIC_KEY', '');
        Configuration::updateValue('PRIVATE_KEY', '');
        Configuration::updateValue('P_TYPE_CHECKOUT', false);
        Configuration::updateValue('P_TEST_REQUEST', false);
        Configuration::updateValue('P_STATE_END_TRANSACTION', '');
        //Configuration::updateValue('P_SPLIT_PRIMARY_RECEIVER_FEE', '');
        Configuration::updateValue('p_split_type', true);
        Configuration::updateValue('P_REDUCE_STOCK_PENDING', true);
        //Set up our currencies and issuers
        CreditCard_OrderState::remove();
        CreditCard_OrderState::setup();
        //CreditCard_Issuer::setup();
        CreditCard_Order::setup();
        EpaycoOrder::remove();
        EpaycoOrder::setup();
        SplitRules::remove();
        SplitRules::setup();

        Configuration::updateValue('payco', true);
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('paymentOptions');
    }

    public function uninstall()
    {
        CreditCard_Order::remove();
        CreditCard_OrderState::remove();
        Configuration::deleteByName('PAYCO_LIVE_MODE');
        Configuration::deleteByName('P_TITULO');
        Configuration::deleteByName('P_CUST_ID_CLIENTE');
        Configuration::deleteByName('P_KEY');
        Configuration::deleteByName('PUBLIC_KEY');
        Configuration::deleteByName('PRIVATE_KEY');
        Configuration::deleteByName('P_TYPE_CHECKOUT');
        Configuration::deleteByName('P_TEST_REQUEST');
        Configuration::deleteByName('P_STATE_END_TRANSACTION');
        //Configuration::deleteByName('P_SPLIT_PRIMARY_RECEIVER_FEE');
        Configuration::deleteByName('p_split_type');
        Configuration::deleteByName('P_REDUCE_STOCK_PENDING');
        Configuration::deleteByName('payco', false);

        EpaycoOrder::remove();
        SplitRules::remove();
        return parent::uninstall();
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
            if (!count($this->_postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }


        try {
            $db = Db::getInstance();
            $request = 'SELECT * FROM `' . SplitRules::$definition['table'] . '`';
            /** @var array $result */
            $result2 = $db->executeS($request);
        } catch (\Throwable $th) {
            //throw $th;
        }

        $setup_dir = Context::getContext()->link->getModuleLink('payco', 'setup');
        $update_dir = Context::getContext()->link->getModuleLink('payco', 'update');
        $delete_dir = Context::getContext()->link->getModuleLink('payco', 'delete');

        $this->context->smarty->assign(array(
            'module_dir' => $this->_path,
            'setup_dir' => $setup_dir,
            'team_record' => $result2,
            'update_dir' => $update_dir,
            'delete_dir' => $delete_dir
        ));
        $this->_html .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        try {
            $db = Db::getInstance();
            $request = 'SELECT * FROM `' . SplitRules::$definition['table'] . '`';
            /** @var array $result */
            $result2 = $db->executeS($request);
        } catch (\Throwable $th) {
            //throw $th;
        }
        $states = CreditCard_OrderState::getOrderStates();

        $order_states = array();

        foreach ($states as $state) {
            $order_states[] = array("id" => $state["id_order_state"], "name" => $state["name"]);
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Configuración ePayco', array(), 'Modules.Payco.Admin'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Titulo', array(), 'Modules.Payco.Admin'),
                        'name' => 'P_TITULO',
                        'required' => true,
                        'desc' => $this->trans('Titulo que el usuario vera durante el Checkout del Plugin', array(), 'Modules.Payco.Admin'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('P_CUST_ID_CLIENTE', array(), 'Modules.Payco.Admin'),
                        'name' => 'P_CUST_ID_CLIENTE',
                        'desc' => $this->trans(
                            'Id del cliente recibidor primario (App, Maketplace, Tienda, etc).',
                            array(),
                            'Modules.Payco.Admin'
                        ),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('P_KEY', array(), 'Modules.Payco.Admin'),
                        'name' => 'P_KEY',
                        'desc' => $this->trans('Llave para firmar la información enviada y recibida de ePayco', array(), 'Modules.Payco.Admin'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('PUBLIC_KEY', array(), 'Modules.Payco.Admin'),
                        'name' => 'PUBLIC_KEY',
                        'desc' => $this->trans('LLave para autenticar y consumir los servicios de ePayco.', array(), 'Modules.Payco.Admin'),
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->trans('PRIVATE_KEY', array(), 'Modules.Payco.Admin'),
                        'name' => 'PRIVATE_KEY',
                        'desc' => $this->trans('LLave para autenticar y consumir los servicios de ePayco.', array(), 'Modules.Payco.Admin'),
                        'required' => true
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Estado final Pedido', array(), 'Modules.Payco.Admin'),
                        'name' => 'P_STATE_END_TRANSACTION',
                        'desc' => $this->trans('Escoja el estado del pago que se aplicar al confirmar la trasacción.', array(), 'Modules.Payco.Admin'),
                        'required' => true,
                        'options' => array(
                            'id' => 'id',
                            'name' => 'name',
                            'default' => array(

                                'value' => '2',
                                'label' => $this->l('Pago aceptado')
                            ),
                            'query' => $order_states,


                        ),
                    ),
                    /*array(
                        'type' => 'text',
                        'label' => $this->trans('P_SPLIT_PRIMARY_RECEIVER_FEE', array(), 'Modules.Payco.Admin'),
                        'name' => 'P_SPLIT_PRIMARY_RECEIVER_FEE',
                        'desc' => $this->trans('Comisión del recibidor primario (App,Maketplace,Tienda,etc)
                        .', array(), 'Modules.Payco.Admin'),
                        'required' => false,
                        'css' => 'hidden'
                    ),*/
                    array(
                        'type' => 'radio',
                        'label' => $this->trans('tipo de Split', array(), 'Modules.Payment.Admin'),
                        'name' => "p_split_type",
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'p_split_type_fijo',
                                'value' => true,
                                'label' => $this->trans('01'),
                            )
                        ),
                    ),

                    array(
                        'type' => 'radio',
                        'label' => $this->trans('Habilitar modo pruebas', array(), 'Modules.Payment.Admin'),
                        'name' => "P_TEST_REQUEST",
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'P_TEST_REQUEST_TRUE',
                                'value' => true,
                                'label' => $this->trans('Si (Transacciones en pruebas)', array(), 'Modules.Payment.Admin'),
                            ),
                            array(
                                'id' => 'P_TEST_REQUEST_FALSE',
                                'value' => false,
                                'label' => $this->trans('No (Transacciones en producción)', array(), 'Modules.Payment.Admin'),
                            )
                        ),
                    ),
                    array(
                        'type' => 'radio',

                        'label' => $this->trans('Tipo de checkout ePayco', array(), 'Modules.Payment.Admin'),

                        'name' => "P_TYPE_CHECKOUT",
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'P_TYPE_CHECKOUT_FALSE',
                                'value' => false,
                                'label' => $this->trans('OnPage Checkout (El usuario al pagar se queda en la tienda no hay redirección a ePayco)', array(), 'Modules.Payment.Admin'),
                            ),
                            array(
                                'id' => 'P_TYPE_CHECKOUT_TRUE',
                                'value' => true,
                                'label' => $this->trans('Estandard Checkout (El usuario al pagar es redireccionado a la pasarela de ePayco)', array(), 'Modules.Payment.Admin'),
                            )
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->trans('Reducir el stock en transacciones pendientes', array(), 'Modules.Payco.Admin'),

                        'name' => "P_REDUCE_STOCK_PENDING",
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'P_REDUCE_STOCK_PENDING_TRUE',
                                'value' => true,
                                'label' => $this->trans('Si', array(), 'Modules.Payment.Admin'),
                            ),
                            array(
                                'id' => 'P_REDUCE_STOCK_PENDING_FALSE',
                                'value' => false,
                                'label' => $this->trans('No', array(), 'Modules.Payment.Admin'),
                            )
                        ),
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        return $fields_form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'P_TITULO' => Tools::getValue('P_TITULO', Configuration::get('P_TITULO')),
            'P_CUST_ID_CLIENTE' => Tools::getValue('P_CUST_ID_CLIENTE', Configuration::get('P_CUST_ID_CLIENTE')),
            'P_KEY' => Tools::getValue('P_KEY', Configuration::get('P_KEY')),
            'PUBLIC_KEY' => Tools::getValue('PUBLIC_KEY', Configuration::get('PUBLIC_KEY')),
            'PRIVATE_KEY' => Tools::getValue('PRIVATE_KEY', Configuration::get('PRIVATE_KEY')),
            'P_TEST_REQUEST' => Tools::getValue('P_TEST_REQUEST', Configuration::get('P_TEST_REQUEST')),
            'P_TYPE_CHECKOUT' => Tools::getValue('P_TYPE_CHECKOUT', Configuration::get('P_TYPE_CHECKOUT')),
            'P_STATE_END_TRANSACTION' => Tools::getValue('P_STATE_END_TRANSACTION', Configuration::get('P_STATE_END_TRANSACTION')),
            //'P_SPLIT_PRIMARY_RECEIVER_FEE' => Tools::getValue('P_SPLIT_PRIMARY_RECEIVER_FEE', Configuration::get('P_SPLIT_PRIMARY_RECEIVER_FEE')),
            'p_split_type' => Tools::getValue('p_split_type', Configuration::get('p_split_type')),
            'P_REDUCE_STOCK_PENDING' => Tools::getValue('P_REDUCE_STOCK_PENDING', Configuration::get('P_REDUCE_STOCK_PENDING'))
        );
    }


    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('P_CUST_ID_CLIENTE'))
                $this->_postErrors[] = $this->l('\'P_CUST_ID_CLIENTE\' Campo Requerido.');
            if (!Tools::getValue('P_KEY'))
                $this->_postErrors[] = $this->l('\'P_KEY\' Campo Requerido.');
            if (!Tools::getValue('PUBLIC_KEY'))
                $this->_postErrors[] = $this->l('\'PUBLIC_KEY\' Campo Requerido.');
            if (!Tools::getValue('PRIVATE_KEY'))
                $this->_postErrors[] = $this->l('\'PRIVATE_KEY\' Campo Requerido.');
        }
    }


    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            //   $p_url_response=Context::getContext()->link->getModuleLink('payco', 'response'); 
            //   $p_url_confirmation=Context::getContext()->link->getModuleLink('payco', 'confirmation');

            if (Tools::getValue('P_TITULO') == "") {
                $p_titulo = "ePayco: Paga con Tarjeta de crédito/débito nacional e internacional, PSE, Daviplata, Nequi, Paypal, Efectivo, Safetypay y muchos más.";
            } else {
                $p_titulo = Tools::getValue('P_TITULO');
            }
            Configuration::updateValue('P_CUST_ID_CLIENTE', Tools::getValue('P_CUST_ID_CLIENTE'));
            Configuration::updateValue('P_KEY', Tools::getValue('P_KEY'));
            Configuration::updateValue('PUBLIC_KEY', Tools::getValue('PUBLIC_KEY'));
            Configuration::updateValue('PRIVATE_KEY', Tools::getValue('PRIVATE_KEY'));
            Configuration::updateValue('P_TEST_REQUEST', Tools::getValue('P_TEST_REQUEST'));
            Configuration::updateValue('P_TYPE_CHECKOUT', Tools::getValue('P_TYPE_CHECKOUT'));
            Configuration::updateValue('P_TITULO', $p_titulo);
            Configuration::updateValue('P_URL_RESPONSE', '');
            Configuration::updateValue('P_URL_CONFIRMATION', '');
            Configuration::updateValue('P_STATE_END_TRANSACTION', Tools::getValue('P_STATE_END_TRANSACTION'));
            //Configuration::updateValue('P_SPLIT_PRIMARY_RECEIVER_FEE', Tools::getValue('P_SPLIT_PRIMARY_RECEIVER_FEE'));
            Configuration::updateValue('p_split_type', Tools::getValue('p_split_type'));

            Configuration::updateValue('P_REDUCE_STOCK_PENDING', Tools::getValue('P_REDUCE_STOCK_PENDING'));

            //CreditCard_OrderState::updateStates(intval(Tools::getValue('id_os_initial')), Tools::getValue('id_os_deleteon'));
            $this->_html .= '<div class="bootstrap"><div class="alert alert-success">' . $this->l('Cambios Aplicados Exitosamente') . '</div></div>';
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        if (!$this->active)
            return false;

        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false)
            return false;

        $this->smarty->assign('module_dir', $this->_path);

        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
        $this->context->smarty->assign(array(
            "titulo" => $this->p_titulo,
            "logo_url" => 'https://multimedia-epayco.s3.amazonaws.com/plugins-sdks/paymentLogo.svg'
        ));
        $modalOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();

        $modalOption->setCallToActionText($this->l('Pagar con ePayco'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:payco/views/templates/hook/payment_onpage.tpl'));

        $payment_options = [
            $modalOption,
        ];

        return $payment_options;
    }
    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if ($this->active == false)
            return;

        if (version_compare(_PS_VERSION_, '1.7.0.0 ', '<')) {
            $order = $params['objOrder'];
            $value = $params['total_to_pay'];
            $currence = $params['currencyObj'];
        } else {
            $order = $params['order'];
            $value = $params['order']->getOrdersTotalPaid();
            $currence = new Currency($params['order']->id_currency);
        }

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }


        $extra1 = $order->id_cart;
        $extra2 = $order->id;

        $sql =
            '
			SELECT `id_feature`
			FROM `' . _DB_PREFIX_ . 'feature_lang`
			WHERE `name` = ' . '"ePayco receiver"';
        $feature_id = Db::getInstance()->getValue($sql);
        $products_info = Db::getInstance()->executeS(
            '
                
            SELECT DISTINCT psod.`product_id`,      
                   psod.`product_quantity`,
                   psod.`total_price_tax_excl` AS "product_price",
                   psod.`total_price_tax_incl` AS "product_tax",
                   psvl.`value` as "customer_id",
                   psps.`feed` as "fee value", 
                   psps.`typefeed` as "split type"
            FROM `' . _DB_PREFIX_ . 'order_detail` psod 
                LEFT JOIN `' . _DB_PREFIX_ . 'feature_product` psfp 
                    ON (psod.`product_id` = psfp.`id_product`)
                LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` psvl 
                    ON (psfp.`id_feature_value` = psvl.`id_feature_value`) 
                INNER JOIN `' . _DB_PREFIX_ . 'payco_split` psps 
                    ON (psvl.`value` = psps.`customer_id`) 
            WHERE psod.id_order = ' . (int) $extra2 . ' AND psfp.id_feature = ' . (int)$feature_id
        );
        $split = false;
        if (count($products_info) > 0) {
            $split = true;
        }

        $emailComprador = $this->context->customer->email;
        $valorBaseDevolucion = $order->total_paid_tax_excl;
        $iva = $value - $valorBaseDevolucion;
        $cart = $this->context->cart;



        $iso = 'CO';
        if ($iva == 0) $valorBaseDevolucion = 0;

        $currency = $this->getCurrency();
        $idcurrency = $order->id_currency;
        foreach ($currency as $mon) {
            if ($idcurrency == $mon['id_currency']) $currency = $mon['iso_code'];
        }

        if ($currency == '') {
            $currency = 'COP';
        }
        $refVenta = $order->reference;
        $state = $order->getCurrentState();

        if ($state) {

            $p_signature = md5(trim($this->p_cust_id_cliente) . '^' . trim($this->p_key) . '^' . $refVenta . '^' . $value . '^' . $currency);
            $addressdelivery = new Address((int)($cart->id_address_delivery));

            if ($this->p_test_request == 1) {
                $test = "true";
            } else {
                $test = "false";
            }

            if ($this->p_type_checkout == 1) {
                $external = "true";
            } else {
                $external = "false";
            }

            if ($this->p_type_checkout == 1) {
                $external = "true";
            } else {
                $external = "false";
            }

            $descripcion = '';
            $productos = Db::getInstance()->executeS('
			SELECT id_product FROM `' . _DB_PREFIX_ . 'cart_product`
			WHERE `id_cart` = ' . (int) $extra1);

            $idproduct_without_split = '';
            foreach ($productos as $producto) {
                // Your product id
                $id_product = (int)$producto['id_product'];
                // Language id
                $lang_id = (int) Configuration::get('PS_LANG_DEFAULT');
                // Load product object
                $product = new Product($id_product, false, $lang_id);
                // Validate product object
                if (Validate::isLoadedObject($product)) {
                    // Get product name
                    $descripcion = $descripcion . $product->name . ', ';
                }
                $products_ids = array_column($products_info, 'product_id');
                if (!in_array($id_product, $products_ids)) {
                    $idproduct_without_split = $idproduct_without_split . $id_product . ', ';
                }
            }
            $descripcion = substr($descripcion, 0, -2);
            $idproduct_without_split = substr($idproduct_without_split, 0, -2);

            if (!EpaycoOrder::ifExist($order->id)) {
                EpaycoOrder::create($order->id, 1);
            }

            $p_url_response = Context::getContext()->link->getModuleLink('payco', 'response');
            $p_url_confirmation = Context::getContext()->link->getModuleLink('payco', 'confirmation');
            $lang = $this->context->language->language_code;

            $vendorsArray = array();
            foreach ($products_info as $receiver) {
                if ($receiver['split type'] == "01") {
                    $receiver_total_tax =  floatval($receiver['product_tax']);
                    $receiver_tax = (floatval($receiver['product_tax']) - floatval($receiver['product_price']));
                    $receiver_total =  floatval($receiver['product_price']);
                    $receiver_feed = (floatval($receiver['fee value']));
                } else {
                    $porcentaje_fee_value = ((100 - (int)($receiver['feed'])) * $value) / 100;
                    $receiver_total = $value - $porcentaje_fee_value;
                    $receiver_feed = (100 - (int)($receiver['feed']));
                }
                $other = array(
                    'id' => $receiver['customer_id'],
                    'total' => strval($receiver_total_tax),
                    'iva' => strval($receiver_tax),
                    'base_iva' =>  strval($receiver_total),
                    'fee' => strval($receiver_feed)
                );
                array_push($vendorsArray, $other);
            }

            $sql_query = '
                    SELECT DISTINCT psod.`product_id`,      
                           psod.`product_quantity`,
                           psod.`total_price_tax_excl` AS "product_price",
                           psod.`total_price_tax_incl` AS "product_tax"
                    FROM `' . _DB_PREFIX_ . 'order_detail` psod 
                    WHERE psod.id_order = ' . (int)$extra2
                . ' AND psod.product_id  IN (' . $idproduct_without_split . ')';
            $vendorsArraysWhithoutSplit = Db::getInstance()->executeS($sql_query);

            foreach ($vendorsArraysWhithoutSplit as $receiver) {
                $receiver_total_tax =  floatval($receiver['product_tax']);
                $receiver_tax = (floatval($receiver['product_tax']) - floatval($receiver['product_price']));
                $receiver_total =  floatval($receiver['product_price']);
                $receiver_feed = floatval($receiver['product_price']);
                if (count($vendorsArray) > 0) {
                    $vendorsArray[] = [
                        'id' => trim($this->p_cust_id_cliente),
                        'total' => strval($receiver_total_tax),
                        'iva' => strval($receiver_tax),
                        'base_iva' => strval($receiver_total),
                        'fee' => strval(0)
                    ];
                }
            }

            if ($split) {
                $new_array = str_replace('"', "'", json_encode($vendorsArray));
            } else {
                $new_array = json_encode([]);
            }


            $myIp = $this->getCustomerIp();
            $is_split = 'false';
            if (count($vendorsArray) > 0) {
                $is_split = 'true';
            }
            $this->smarty->assign(
                array(
                    'isSplit' =>  $is_split,
                    'split_receivers' => strval($new_array),
                    'this_path_bw' => $this->_path,
                    'p_signature' => $p_signature,
                    'total_to_pay' => $value,
                    'status' => 'ok',
                    'refVenta' => $refVenta,
                    'custemail' => $emailComprador,
                    'extra1' => $extra1,
                    'extra2' => $extra2,
                    'total' => $value,
                    'currency' => $currency,
                    'iso' => $iso,
                    'iva' => $iva,
                    'baseDevolucionIva' => $valorBaseDevolucion,
                    'merchantid' => trim($this->p_cust_id_cliente),
                    'external' => $external,
                    'merchanttest' => $test,
                    'p_key' => trim($this->p_key),
                    'public_key' => trim($this->public_key),
                    'private_key' => trim($this->private_key),
                    'custip' => $_SERVER['REMOTE_ADDR'],
                    'custname' => $this->context->customer->firstname . " " . $this->context->customer->lastname,
                    'p_url_response' => $p_url_response,
                    'p_url_confirmation' => $p_url_confirmation,
                    'p_billing_email' => $this->context->customer->email,
                    'p_billing_name' => $this->context->customer->firstname,
                    'p_billing_last_name' => $this->context->customer->lastname,
                    'p_billing_address' => $addressdelivery->address1 . " " . $addressdelivery->address2,
                    'p_billing_city' => $addressdelivery->city,
                    'p_billing_country' => $addressdelivery->id_state,
                    'p_billing_phone' => "",
                    'lang' => $lang,
                    'descripcion' => $descripcion,
                    'ip' => $myIp
                )
            );
        } else {
            $this->smarty->assign('status', 'failed');
        }
        //redirige al checkout
        //luego al controlador response.php
        return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
    }


    private function is_blank($var)
    {
        return isset($var) || $var == '0' ? ($var == "" ? true : false) : false;
    }

    private function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;
        return false;
    }

    private function getCustomerIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public function PaymentReturnOnpage()
    {

        $ref_payco = "";
        $url = "";
        $confirmation = false;
        $x_ref_payco = "";

        foreach ($_REQUEST as $value) {
            if (preg_match("/ref_payco/", $value)) {
                $arr_refpayco = explode("=", $value);
                $ref_payco = $arr_refpayco[1];
            }
        }

        if (isset($_REQUEST["x_ref_payco"])) {
            $config = Configuration::getMultiple(array('P_CUST_ID_CLIENTE', 'P_KEY', 'PUBLIC_KEY', 'P_TEST_REQUEST'));
            $public_key = $config["PUBLIC_KEY"];
            $ref_payco = $_REQUEST["x_ref_payco"];
            $url = "https://secure.payco.co/restpagos/transaction/response.json?ref_payco=$ref_payco&public_key=" . $public_key;
            $confirmation = false;
        }

        if (isset($_REQUEST["?ref_payco"]) != "" || isset($_REQUEST["ref_payco"]) || $ref_payco) {

            if (isset($_REQUEST["?ref_payco"])) {
                $ref_payco = $_REQUEST["?ref_payco"];
            }

            if (isset($_REQUEST["ref_payco"])) {
                $ref_payco = $_REQUEST["ref_payco"];
            }
            if ($url == "") {
                $url = 'https://secure.epayco.io/validation/v1/reference/' . $ref_payco;
            }
        }





        if ($ref_payco != "" and $url != "") {
            $responseData = $this->PostCurl($url, false, $this->StreamContext());
            $jsonData = @json_decode($responseData, true);
            $data = $jsonData['data'];

            $data["ref_payco"] = $ref_payco;
            $data["url"] = $url;

            $this->Acentarpago($data["x_extra1"], $data["x_cod_response"], $data["x_ref_payco"], $data["x_transaction_id"], $data["x_amount"], $data["x_currency_code"], $data["x_signature"], $confirmation, $data["x_test_request"], $data["x_cod_transaction_state"], $ref_payco, $data["x_approval_code"]);
            $this->context->smarty->assign($data);
        }
    }

    public function PaymentSuccess($extra1, $response, $referencia, $transid, $amount, $currency, $signature, $confirmation, $textMode, $x_cod_transaction_state, $ref_payco, $x_approval_code)
    {
        $this->Acentarpago($extra1, $response, $referencia, $transid, $amount, $currency, $signature, $confirmation, $textMode, $x_cod_transaction_state, $ref_payco, $x_approval_code);
    }


    private function Acentarpago($extra1, $response, $referencia, $transid, $amount, $currency, $signature, $confirmation, $textMode, $x_cod_transaction_state, $old_ref_payco, $x_approval_code)
    {

        $config = Configuration::getMultiple(array('P_CUST_ID_CLIENTE', 'P_KEY', 'PUBLIC_KEY', 'P_TEST_REQUEST', 'P_STATE_END_TRANSACTION'));
        $x_cust_id_cliente = trim($config['P_CUST_ID_CLIENTE']);
        $x_key = trim($config['P_KEY']);
        $idorder = $extra1;
        $x_cod_response = (int)$response;
        $x_signature = hash(
            'sha256',
            $x_cust_id_cliente . '^'
                . $x_key . '^'
                . $referencia . '^'
                . $transid . '^'
                . $amount . '^'
                . $currency
        );

        $payment = false;
        if ($textMode == "TRUE") {
            $state = 'PAYCO_OS_REJECTED_TEST';
            if ($x_cod_response == 4)
                $state = 'PAYCO_OS_FAILED_TEST';
            else if ($x_cod_response == 2)
                $state = 'PAYCO_OS_REJECTED_TEST';
            else if ($x_cod_response == 3) {
                $state = 'PAYCO_OS_PENDING_TEST';
                $statePending = $state;
            } else if ($x_cod_response == 9)
                $state = 'PAYCO_OS_EXPIRED_TEST';
            else if ($x_cod_response == 10)
                $state = 'PAYCO_OS_ABANDONED_TEST';
            else if ($x_cod_response == 11)
                $state = 'PAYCO_OS_CANCELED_TEST';
            else if ($x_cod_response == 1) {
                $state = 'PS_OS_PAYMENT';
                $payment = true;
            }
        } else {
            $state = 'PAYCO_OS_REJECTED';
            if ($x_cod_response == 4)
                $state = 'PAYCO_OS_FAILED';
            else if ($x_cod_response == 2)
                $state = 'PAYCO_OS_REJECTED';
            else if ($x_cod_response == 3) {
                $state = 'PAYCO_OS_PENDING';
                $statePending = $state;
            } else if ($x_cod_response == 9)
                $state = 'PAYCO_OS_EXPIRED';
            else if ($x_cod_response == 10)
                $state = 'PAYCO_OS_ABANDONED';
            else if ($x_cod_response == 11)
                $state = 'PAYCO_OS_CANCELED';
            else if ($x_cod_response == 1) {
                $state = 'PS_OS_PAYMENT';
                $payment = true;
            }
        }

        $order = new Order((int)Order::getOrderByCartId((int)$idorder));
        $keepOn = false;
        if ($this->p_test_request == 1) {
            $test = "yes";
        } else {
            $test = "no";
        }
        $isTestTransaction = $textMode == 'TRUE' ? "yes" : "no";
        $orderAmount = floatval($order->getOrdersTotalPaid());
        if ($orderAmount == floatval($amount)) {

            if ($isTestTransaction == "yes") {
                $validation = true;
            }


            $order = new Order((int)Order::getOrderByCartId((int)$idorder));
            $keepOn = false;
            if ($this->p_test_request == 1) {
                $test = "yes";
            } else {
                $test = "no";
            }
            $isTestTransaction = $textMode == 'TRUE' ? "yes" : "no";
            $orderAmount = floatval($order->getOrdersTotalPaid());
            if ($orderAmount == floatval($amount)) {

                if ($isTestTransaction == "yes") {
                    $validation = true;
                }


                if ($isTestTransaction == "no") {
                    if ($x_approval_code != "000000" && $x_cod_response == 1) {
                        $validation = true;
                    } else {
                        if ($x_cod_response != 1) {
                            $validation = true;
                        } else {
                            $validation = false;
                        }
                    }
                }
            }
            $orderStatusPre = Db::getInstance()->executeS('
            SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
            WHERE `id_order_state` = ' . (int)$order->current_state);
            $orderStatusPreName = $orderStatusPre[0]['name'];

            if ($test == "yes") {
                if (
                    $orderStatusPreName == "ePayco Pago Rechazado Prueba" ||
                    $orderStatusPreName == "ePayco Pago Cancelado Prueba" ||
                    $orderStatusPreName == "ePayco Pago Abandonado Prueba" ||
                    $orderStatusPreName == "ePayco Pago Expirado Prueba"  ||
                    $orderStatusPreName == "ePayco Pago Fallido Prueba"
                ) {
                    $validacionOrderName = false;
                } else {
                    $validacionOrderName = true;
                }
            } else {
                if (
                    $orderStatusPreName == "ePayco Pago Rechazado" ||
                    $orderStatusPreName == "ePayco Pago Cancelado" ||
                    $orderStatusPreName == "ePayco Pago Abandonado" ||
                    $orderStatusPreName == "ePayco Pago Expirado"  ||
                    $orderStatusPreName == "ePayco Pago Fallido"
                ) {
                    $validacionOrderName = false;
                } else {
                    $validacionOrderName = true;
                }
            }
            $history = new OrderHistory();
            $history->id_order = (int)$order->id;
            if ($x_signature == $signature && $validation) {
                $current_state = $order->current_state;

                if (!EpaycoOrder::ifStockDiscount($order->id)) {
                    EpaycoOrder::updateStockDiscount($order->id, 1);
                }
                if ($current_state != Configuration::get($state)) {
                    if ($confirmation && !$payment && $x_cod_response != 3 && EpaycoOrder::ifStockDiscount($order->id)) {
                        if (!$validacionOrderName) {
                            $this->RestoreStock($order, '+');
                            $history = new OrderHistory();
                            $history->id_order = (int)$order->id;
                            $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                        }
                    } else {
                        if ($confirmation && $x_cod_response == 3 && EpaycoOrder::ifStockDiscount($order->id)) {
                            if (!$validacionOrderName) {
                                $this->RestoreStock($order, '-');
                            }
                        }
                    }




                    if ($payment) {
                        $orderStatus = Db::getInstance()->executeS('
                        SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
                        WHERE `id_order_state` = ' . (int)$config['P_STATE_END_TRANSACTION']);
                        if ($test == $isTestTransaction) {
                            $myOrderName = $orderStatus[0]['name'];
                            $findme   = 'Prueba';
                            $pos = strpos($myOrderName, $findme);
                            if ($pos === false) {
                                $orderStatusName = $textMode == "TRUE" ? $orderStatus[0]['name'] . " Prueba" : $orderStatus[0]['name'];
                            } else {
                                $orderStatusName = $orderStatus[0]['name'];
                            }

                            $newOrderName = $orderStatusName;
                            $orderStatusEndId = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                                'SELECT * FROM `' . _DB_PREFIX_ . 'order_state_lang` 
                            WHERE `name` = "' . $orderStatusName . '"'
                            );
                            if ($confirmation) {
                                // $this->RestoreStock($order, '-'); 
                            }
                        } else {
                            $orderStatusName = $orderStatus[0]['name'] . " Prueba";
                            $newOrderName = $orderStatusName;
                            $orderStatusEndId = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                                'SELECT * FROM `' . _DB_PREFIX_ . 'order_state_lang` 
                            WHERE `name` = "' . $orderStatusName . '"'
                            );
                            if ($orderStatusEndId != $current_state) {
                                if ($orderStatusPreName != "ePayco Pago Pendiente Prueba") {
                                    $this->RestoreStock($order, '+');
                                }
                            }
                        }
                        $history->changeIdOrderState((int)$orderStatusEndId, $order, true);
                    } else {

                        if (($x_cod_response == 2
                            || $x_cod_response == 4
                            || $x_cod_response == 6
                            || $x_cod_response == 9
                            || $x_cod_response == 10
                            || $x_cod_response == 11
                        ) && EpaycoOrder::ifStockDiscount($order->id)) {
                            if ($current_state != Configuration::get($state)) {
                                if (trim($x_cod_transaction_state) == 10) {
                                    if (!$confirmation && !$validacionOrderName) {
                                        $this->RestoreStock($order, '+');
                                    }
                                }
                                if ($orderStatusPreName == "ePayco Esperando Pago") {
                                    $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                                    $this->RestoreStock($order, '+');
                                }
                            }
                        }
                        $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                        if (!$validacionOrderName) {
                            if (!$test && $orderStatusPreName != "ePayco Pago Rechazado" || $orderStatusPreName != "ePayco Pago Cancelado" || $orderStatusPreName != "ePayco Pago Fallido") {
                                $keepOn = true;
                            }
                            if ($test && $orderStatusPreName != "ePayco Pago Rechazado Prueba" || $orderStatusPreName != "ePayco Pago Cancelado Prueba" || $orderStatusPreName != "ePayco Pago Fallido Prueba") {
                                $keepOn = true;
                            }
                            if ($keepOn && $orderStatusPreName == "ePayco Pago Rechazado") {
                                if ($x_cod_response == 1) {
                                    $orderStatus = Db::getInstance()->executeS('
                                    SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
                                    WHERE `id_order_state` = ' . (int)$config['P_STATE_END_TRANSACTION']);
                                    $orderStatusName = $textMode == "TRUE" ? $orderStatus[0]['name'] . " Prueba" : $orderStatus[0]['name'];
                                    $orderStatusEndId = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                                        'SELECT * FROM `' . _DB_PREFIX_ . 'order_state_lang` 
                                    WHERE `name` = "' . $orderStatusName . '"'
                                    );
                                    if ($isTestTransaction == "yes") {
                                        $history->changeIdOrderState((int)$orderStatusEndId, $order, true);
                                    }
                                }
                                if ($textMode == "TRUE" && $x_cod_response != 1) {
                                    $history->changeIdOrderState((int)$orderStatusEndId, $order, true);
                                } else {
                                    if ($x_cod_response != 1) {
                                        $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                                    }
                                }
                            }
                            if (!$keepOn) {
                                $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                            }
                        }
                    }
                    if (!$validacionOrderName && !$keepOn) {
                        $history->addWithemail(false);
                    }
                }
            } else {
                $history->changeIdOrderState((int)Configuration::get("PS_OS_ERROR"), $order, true);
                $this->RestoreStock($order, '+');
                $history->addWithemail(false);
            }
            if ($confirmation) {
                header("HTTP/1.1 200 OK");
                echo $x_cod_response;
                die();
            } else {
                $p_url_response = Context::getContext()->link->getModuleLink('payco', 'result');
                Tools::redirect($p_url_response . "?ref_payco=" . $old_ref_payco);
                /*if(Configuration::get('P_URL_RESPONSE') == Context::getContext()->link->getModuleLink('payco', 'response'))
            {
                Tools::redirect($p_url_response."?ref_payco=".$old_ref_payco);
            }
           else{
              
                 header("location:index.php?controller=history");
              
           }*/
            }
        }
    }


    private function RestoreStock($orderId, $operation)
    {
        $order = $orderId;
        foreach ($order->getProductsDetail() as $product) {
            StockAvailable::updateQuantity($product['product_id'], $product['product_attribute_id'], $operation . (int)$product['product_quantity'], $order->id_shop);
        }
    }

    private function PostCurl($url)
    {

        if (function_exists('curl_init')) {
            $ch = curl_init();
            $timeout = 5;
            $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        } else {
            $data =  @Tools::file_get_contents($url);
            return $data;
        }
    }

    private function StreamContext()
    {

        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'protocol_version' => 1.1,
                'timeout' => 10,
                'ignore_errors' => true
            )
        ));
        return $context;
    }

    public function SplitCustomer($customer_id, $fee, $typefeed)
    {
        $data = SplitRules::create($customer_id, $fee, $typefeed);
        var_dump($data);
        die();
    }

    public function SplitCustomerUpdate($customer_id, $fee, $typefeed)
    {
        $data = SplitRules::SplitCustomerUpdate($customer_id, $fee, $typefeed);
        var_dump($data);
        die();
    }

    public function SplitCustomerDelete($customer_id, $id_)
    {
        $data = SplitRules::deleteSplitRule($customer_id, $id_);
        var_dump($data);
        die();
    }
}
