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
        // Definir constante global
        if (!defined('_EPAYCO_MULTIMEDIA_URL_')) {
            define('_EPAYCO_MULTIMEDIA_URL_', 'https://multimedia.epayco.co');
        }
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
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('paymentOptions');
    }

    public function uninstall()
    {
        // CreditCard_Order::remove();
        // CreditCard_OrderState::remove();
        // Configuration::deleteByName('PAYCO_LIVE_MODE');
        // Configuration::deleteByName('P_TITULO');
        // Configuration::deleteByName('P_CUST_ID_CLIENTE');
        // Configuration::deleteByName('P_KEY');
        // Configuration::deleteByName('PUBLIC_KEY');
        // Configuration::deleteByName('PRIVATE_KEY');
        // Configuration::deleteByName('P_TYPE_CHECKOUT');
        // Configuration::deleteByName('P_TEST_REQUEST');
        // Configuration::deleteByName('P_STATE_END_TRANSACTION');
        // //Configuration::deleteByName('P_SPLIT_PRIMARY_RECEIVER_FEE');
        // Configuration::deleteByName('p_split_type');
        // Configuration::deleteByName('P_REDUCE_STOCK_PENDING');
        // Configuration::deleteByName('payco', false);

        EpaycoOrder::remove();
        SplitRules::remove();
        return parent::uninstall();
    }


    /**
     * Load the configuration form
     */
    public function getContent()
    {
        try {
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
        if (is_array($result2) && !empty($result2)) {
            foreach ($result2 as &$row) {
                // Si typefeed está vacío, nulo o no existe, establecer 'porcentaje'
                if (empty($row['typefeed']) || !isset($row['typefeed'])) {
                    $row['typefeed'] = $this->p_split_type === '1' ? 'fijo' : 'porcentaje';
                }
            }
            // Desenganchar la referencia para evitar problemas
            unset($row);
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
        } catch (\Throwable $th) {
            throw $th;
        }
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
                                'value' => '1',
                                'label' => $this->trans('fijo'),
                            ),
                            array(
                                'id' => 'p_split_type_porcentaje',
                                'value' => '2',
                                'label' => $this->trans('porcentual'),
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
    public function hookDisplayBackOfficeHeader()
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
            "logo_url" => _EPAYCO_MULTIMEDIA_URL_ . '/plugins-sdks/paymentLogo.svg',
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
                $test = true;
            } else {
                $test = false;
            }

            if ($this->p_type_checkout == 1) {
                $external = "standard";
            } else {
                $external = "onepage";
            }

            // Usa esto:
            $productDetails = $this->getCompleteOrderProductDetails($order->id);

            $descripcion = '';

            foreach ($productDetails as $product) {
                $descripcion .= $product['product_name'] . ', ';
                
                // Validar que tenga receiver y fee válidos
                if (!empty($product['epayco_receiver']) && 
                    isset($product['total_price_tax_incl']) && 
                    $product['total_price_tax_incl'] > 0) {
                    $amountReceiver = number_format($product['total_price_tax_incl'], 2, '.', '');
                    $taxAmount = number_format($product['tax_amount'], 2, '.', '');
                    $taxBase = number_format($product['total_price_tax_excl'], 2, '.', '');
                    $receiversArray[] = [
                        'merchantId' =>(int)trim($product['epayco_receiver']),
                        'amount' =>  (float)$amountReceiver,
                        'tax' => (float)$taxAmount,
                        'taxBase' => (float)$taxBase,
                        'fee' => isset($product['epayco_fee']) && $product['epayco_fee'] !== null 
                            ? (int)trim($product['epayco_fee']) 
                            : 0
                    ];
                }
            }
            
            $descripcion = substr($descripcion, 0, -2);

            if (!EpaycoOrder::ifExist($order->id)) {
                EpaycoOrder::create($order->id, 1);
            }

            $p_url_response = Context::getContext()->link->getModuleLink('payco', 'response');
            $p_url_confirmation = Context::getContext()->link->getModuleLink('payco', 'confirmation');
            $lang = $this->context->language->language_code;

            if ($lang == "es") {
                // Si el idioma es español, usa el botón en español
                $url_button = _EPAYCO_MULTIMEDIA_URL_ . '/plugins-sdks/Boton-color-espanol.png';
            } else {
                // Si el idioma no es español, usa el botón en inglés
                $url_button = _EPAYCO_MULTIMEDIA_URL_ . '/plugins-sdks/Boton-color-Ingles.png';
                // Reasignar $lang a "en" (opcional, si es necesario en tu lógica)
                $lang = "en";
            }

            $myIp = $this->getCustomerIp();
            $is_split = count($receiversArray) > 0 ? 'true' : 'false';
            $tokenResponse = $this->epaycoBerarToken(trim($this->public_key),trim($this->private_key));
            $token = null;
            if(isset($tokenResponse['token'])){
                $token = $tokenResponse['token'];
            }
            $date = new DateTimeImmutable();
            $dataScript  = array(
                "name"=>$this->string_sanitize($descripcion),
                "description"=>$this->string_sanitize($descripcion),
                "invoice"=>(string)$refVenta,
                "currency"=>$currency,
                "amount"=>floatval(number_format($value, 2, '.', '')),
                "taxBase"=>floatval(number_format($valorBaseDevolucion, 2, '.', '')),
                "tax"=>floatval(number_format($iva, 2, '.', '')),
                "taxIco"=>floatval(0),
                "country"=>$iso,
                "lang"=>$lang,
                "confirmation"=>$p_url_confirmation,
                "response"=>$p_url_response,
                "billing" => [
                    "name" =>$this->context->customer->firstname . " " . $this->context->customer->lastname,
                    "address" => $addressdelivery->address1 . " " . $addressdelivery->address2,
                    "email" => $this->context->customer->email,
                ],
                "autoclick"=> true,
                "ip"=>$myIp,
                "test"=>$test,
                 "extras" => [
                    "extra1" => (string)$extra1,
                    "extra2" => (string)$extra2,
                    "extra3" => $lang
                ],
                "extrasEpayco" => [
                    "extra5" => "P24"
                ],
                "epaycoMethodsDisable" => [],
                "method"=> "POST",
                "checkout_version"=>"2",
                "autoClick" => false,
            );
            if($is_split){
                $dataScript["splitPayment"] = [
                    "type" => $this->p_split_type  ==  '1' ? 'amount' : 'percentage',
                    "receivers" =>$receiversArray
                ];
           }

            $checkoutSessionResponse = $this->epaycoSessionCheckout($token, $dataScript);
            $sessionId = null;
            if(isset($checkoutSessionResponse['success'])){
                $sessionId = $checkoutSessionResponse["data"]['sessionId'];
            }

            $payload = array(
                'sessionId' => $sessionId,
                'type' => $external,
                'test' => $test,
            );
            $checkout =  base64_encode(json_encode($payload));      

            $this->smarty->assign(
                array(
                    'this_path_bw' => $this->_path,
                    'checkout' => $checkout,
                    'status' => isset($sessionId) ? 'ok' : 'fail',
                    'url_button' => $url_button
                )
            );
        }else {
            $this->smarty->assign('status', 'failed');
        }
        // Redirige al checkout
        return $this->display(__FILE__, 'views/templates/hook/payment_return.tpl');
    }

   /**
     * Obtener valor de una característica específica del producto
     * @param int $productId
     * @param string $featureName
     * @return string|null
     */
    private function getProductFeature($productId, $featureName)
    {
        try {
        /*$sql = '
            SELECT fvl.value 
            FROM `' . _DB_PREFIX_ . 'feature_product` fp
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_lang` fl 
                ON fp.id_feature = fl.id_feature 
                AND fl.id_lang = ' . (int)Context::getContext()->language->id . '
            LEFT JOIN `' . _DB_PREFIX_ . 'feature_value_lang` fvl 
                ON fp.id_feature_value = fvl.id_feature_value 
                AND fvl.id_lang = ' . (int)Context::getContext()->language->id . '
            WHERE fp.id_product = ' . (int)$productId . '
            AND fl.name = "' . pSQL($featureName) . '"
            LIMIT 1';
        $result = Db::getInstance()->getValue($sql);
        return $result ? $result : null;
        */
        $product = new Product($productId, false, Context::getContext()->language->id);
        if (!Validate::isLoadedObject($product)) {
            return null;
        }
        
        $features = $product->getFeatures();
        
        foreach ($features as $feature) {
            $featureObj = new Feature($feature['id_feature'], Context::getContext()->language->id);
            if ($featureObj->name === $featureName) {
                $featureValue = new FeatureValue($feature['id_feature_value'], Context::getContext()->language->id);
                return $featureValue->value;
            }
        }
        return null;
        } catch (Exception $e) {
            error_log("Error al obtener característica del producto: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener detalles completos de productos de una orden
     * @param int $orderId
     * @return array
     */
    private function getCompleteOrderProductDetails($orderId){
        $order = new Order($orderId);
        if (!Validate::isLoadedObject($order)) {
            return [];
        }
        
        $products = [];
        $orderDetails = $order->getOrderDetailList();
        
        foreach ($orderDetails as $detail) {
            $product = new Product($detail['product_id'], false, Context::getContext()->language->id);
            // Obtener la característica "ePayco receiver"
            $epaycoReceiver = $this->getProductFeature($detail['product_id'], 'ePayco receiver');
            if($epaycoReceiver) {
				$request = 'SELECT feed FROM `'.SplitRules::$definition['table'].'`WHERE `customer_id` = "'.$epaycoReceiver.'"';
				/** @var array $result */
                $fee = Db::getInstance()->getValue($request);
            }
            $products[] = [
                'product_id' => (int)$detail['product_id'],
                'product_attribute_id' => (int)$detail['product_attribute_id'],
                'product_name' => $detail['product_name'],
                'product_quantity' => (int)$detail['product_quantity'],
                'unit_price_tax_excl' => (float)$detail['unit_price_tax_excl'], // Base sin impuesto
                'unit_price_tax_incl' => (float)$detail['unit_price_tax_incl'], // Precio con impuesto
                'tax_amount' => (float)($detail['unit_price_tax_incl'] - $detail['unit_price_tax_excl']), // Impuesto
                'total_price_tax_excl' => (float)$detail['total_price_tax_excl'], // Total base
                'total_price_tax_incl' => (float)$detail['total_price_tax_incl'], // Total con impuesto
                'total_tax_amount' => (float)($detail['total_price_tax_incl'] - $detail['total_price_tax_excl']), // Total impuesto
                'epayco_receiver' => $epaycoReceiver, // Característica personalizada
                'epayco_fee' => $fee ?? null
            ];
        }
        return $products;
    }

    private function is_blank($var){
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

    public function epaycoBerarToken($public_key,$private_key)
    {
        $publicKey = trim($public_key);
        $privateKey = trim($private_key);
        $bearer_token = base64_encode($publicKey . ":" . $privateKey);
        
        if (!isset($_COOKIE[$publicKey])) {
            $token = base64_encode($publicKey . ":" . $privateKey);
            $bearer_token = $token;
            $cookie_value = $bearer_token;
            setcookie($publicKey, $cookie_value, time() + (60 * 14), "/");
        } else {
            $bearer_token = $_COOKIE[$publicKey];
        }
        
        $headers = array(
                'Content-Type: application/json',
                'Authorization: Basic '.$bearer_token
        );

        $data = array(
            'public_key' => $publicKey
        );
        $url = 'https://eks-apify-service.epayco.io/login';
        //return $this->epayco_realizar_llamada_api("login", [], $headers);
        $responseData = $this->PostCurl($url, $data, $headers);
        $jsonData = @json_decode($responseData, true);
        return $jsonData ;
    }

    private function epaycoSessionCheckout($bearer_token, $body){
        $headers = array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$bearer_token
        );

        $url = 'https://eks-apify-service.epayco.io/payment/session/create';
        $responseData = $this->PostCurl($url, $body, $headers);
        $jsonData = @json_decode($responseData, true);
        return $jsonData;
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


        if (isset($_REQUEST["?ref_payco"]) != "" || isset($_REQUEST["ref_payco"]) || $ref_payco) {

            if (isset($_REQUEST["?ref_payco"])) {
                $ref_payco = $_REQUEST["?ref_payco"];
            }

            if (isset($_REQUEST["ref_payco"])) {
                $ref_payco = $_REQUEST["ref_payco"];
            }

            $url = 'https://eks-checkout-service.epayco.io/validation/v1/reference/' . $ref_payco;
        }


        if ($ref_payco != "" and $url != "") {
            $responseData = $this->PostCurl($url, false, $this->StreamContext());
            $jsonData = @json_decode($responseData, true);
            $data = $jsonData['data'];

            $data["ref_payco"] = $ref_payco;
            $data["url"] = $url;

            //$this->Acentarpago($data["x_extra1"], $data["x_cod_response"], $data["x_ref_payco"], $data["x_transaction_id"], $data["x_amount"], $data["x_currency_code"], $data["x_signature"], $confirmation, $data["x_test_request"], $data["x_cod_transaction_state"], $ref_payco, $data["x_approval_code"], $data["x_franchise"]);
            $this->context->smarty->assign($data);
        }
    }

    public function PaymentSuccess($extra1, $response, $referencia, $transid, $amount, $currency, $signature, $confirmation, $textMode, $x_cod_transaction_state, $ref_payco, $x_approval_code, $x_franchise)
    {
        $this->Acentarpago($extra1, $response, $referencia, $transid, $amount, $currency, $signature, $confirmation, $textMode, $x_cod_transaction_state, $ref_payco, $x_approval_code, $x_franchise);
    }

    private function Acentarpago($extra1, $response, $referencia, $transid, $amount, $currency, $signature, $confirmation, $textMode, $x_cod_transaction_state, $old_ref_payco, $x_approval_code, $x_franchise)
    {
        //$this->writeCronLog($extra1." - ".$response." - ".$referencia." - ".$transid." - ".$amount." - ".$currency." - ".$signature." - ".$confirmation." - ".$textMode." - ".$x_cod_transaction_state." - ".$old_ref_payco." - ".$x_approval_code." - ".$x_franchise);
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

        // $order_id = Order::getByCartId((int)$idorder);

        $order = Order::getByCartId((int)$idorder);

        $keepOn = false;
        if ($this->p_test_request == 1) {
            $test = "yes";
        } else {
            $test = "no";
        }
        $validation = false;
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


        if ($x_signature == $signature && $validation) {
            $current_state = $order->current_state;
            //EpaycoOrder::updateRefPayco($order->id, $referencia);
            if (!EpaycoOrder::ifStockDiscount($order->id)) {
                EpaycoOrder::updateStockDiscount($order->id);
                error_log("Stock descontado automáticamente por PrestaShop para el pedido " . $order->id);
                $this->writeCronLog("Stock descontado automáticamente por PrestaShop para el pedido " . $order->id);
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
                            //$this->RestoreStock($order, '-');
                        }else{
                            $orderStatus = Db::getInstance()->executeS('
                            SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
                            WHERE `id_order_state` = ' . (int)$current_state);
                            $orderStatusName = $orderStatus[0]['name'];
                            if($orderStatusName == "ePayco Pago Rechazado de Prueba" || $orderStatusName == "ePayco Pago Rechazado"){
                                //$this->RestoreStock($order, '-');
                            }
                        }
                    }
                }

                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                if ($payment && $validacionOrderName) {
                    $orderStatus = Db::getInstance()->executeS('
                        SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
                        WHERE `id_order_state` = ' . (int)$config['P_STATE_END_TRANSACTION'] . ' AND id_lang = ' . (int)$order->id_lang);

                    $orderStatusName = $orderStatus[0]['name'];
                    $newOrderName = $orderStatusName;
                    $orderStatusEndId = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        'SELECT * FROM `' . _DB_PREFIX_ . 'order_state_lang` 
                        WHERE `name` = "' . $orderStatusName . '"'
                    );
                     $orderStatus = Db::getInstance()->executeS('
                        SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
                        WHERE `id_order_state` = ' . (int)$current_state);
                        $orderStatusName = $orderStatus[0]['name'];
                    if($orderStatusName == "ePayco Pago Rechazado de Prueba" || $orderStatusName == "ePayco Pago Rechazado"){
                        $this->RestoreStock($order, '-');
                        $this->writeCronLog("Se actualiza el estado rechazado.<br>");
                    }
                    $history->changeIdOrderState((int)$orderStatusEndId, $order, true);
                } else {
                    if (in_array($x_cod_response, [2, 4, 6, 9, 10, 11])) {
                        if ($current_state != Configuration::get($state)) {
                            $orderStatus = Db::getInstance()->executeS('
                                SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
                                WHERE `id_order_state` = ' . (int)$config['P_STATE_END_TRANSACTION']);
                            $orderStatusName = $orderStatus[0]['name'];
                            $orderStatusPre = Db::getInstance()->executeS('
                                SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
                                WHERE `id_order_state` = ' . (int)$current_state);
                            $orderStatusNamePre = $orderStatusPre[0]['name'];
                            if( $orderStatusName == $orderStatusNamePre){
                                exit;
                            }
                            if ($confirmation || in_array($x_franchise, ["VS", "CR", "AM", "DC", "MC", "PSE"])) {
                                $this->writeCronLog("Llamando a RestoreStock en condición de rechazo/fallo. order: ".(int)$order->id . " ref_payco: ". $referencia);
                            }
                            //EpaycoOrder::deletePaycoOrder($order->id);
                            if (trim($x_cod_response) == 10) {
                                $this->RestoreStock($order, '+');
                            }
                            if ($orderStatusPreName == "ePayco Esperando Pago" || $orderStatusPreName == "ePayco Pago Pendiente" || $orderStatusPreName == "ePayco Pago Pendiente de Prueba") {
                                $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                                $this->RestoreStock($order, '+');
                            }
                        }
                    }

                    $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                    if (!$validacionOrderName) {
                        if ($orderStatusPreName != "ePayco Pago Rechazado" || $orderStatusPreName != "ePayco Pago Cancelado" || $orderStatusPreName != "ePayco Pago Fallido") {
                            $keepOn = true;
                        }

                        if ($keepOn) {
                            if ($x_cod_response == 1) {
                                $orderStatus = Db::getInstance()->executeS('
                                    SELECT name FROM `' . _DB_PREFIX_ . 'order_state_lang`
                                    WHERE `id_order_state` = ' . (int)$config['P_STATE_END_TRANSACTION']);
                                $orderStatusName = $orderStatus[0]['name'];
                                $orderStatusEndId = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                                    'SELECT * FROM `' . _DB_PREFIX_ . 'order_state_lang` 
                                    WHERE `name` = "' . $orderStatusName . '"'
                                );
                                $history->changeIdOrderState((int)$orderStatusEndId, $order, true);
                                $this->RestoreStock($order, '-');
                            }
                            if ($x_cod_response != 1) {
                                $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                                $this->RestoreStock($order, '-');
                            }
                        }
                        if (!$keepOn) {
                            $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                        }
                    }
                }
                if (!$keepOn) {
                    if ($x_cod_response != 1) {
                        $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                    }
                }
            }
            header("HTTP/1.1 200 OK");
            echo $x_cod_response;
            die();
        }else{
            $this->writeCronLog("Firma no valida");
        }
    }

    private function RestoreStock($order, $operation)
    {
        if ($order && !empty($order->getProductsDetail())) {
            foreach ($order->getProductsDetail() as $product) {

                StockAvailable::updateQuantity(
                    (int)$product['product_id'],                  // ID del producto
                    (int)$product['product_attribute_id'],        // ID del atributo del producto (si aplica)
                    $operation . (int)$product['product_quantity'], // Operación (+ o -) seguido de la cantidad
                    (int)$order->id_shop                          // ID de la tienda (shop)
                );
            }
        } else {
            error_log("La orden no tiene productos o no se pudo cargar la orden.");
            $this->writeCronLog("La orden no tiene productos o no se pudo cargar la orden.");
        }
    }

    private function PostCurl($url, $body, $headers, $method='POST')
    {
        try{
            if (function_exists('curl_init')) {
            // Inicializamos cURL
            $ch = curl_init();
            $timeout = 5;
            $user_agent = 'Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

            // Configuraciones de cURL
            curl_setopt($ch, CURLOPT_URL, $url);
            if(!$body){
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // Desactivar verificación de certificado SSL
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);    // Desactivar verificación de host SSL
                curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);   // Establecer el agente de usuario
                curl_setopt($ch, CURLOPT_HEADER, 0);                // No incluir encabezados en la salida
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        // Devolver la respuesta como string
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); // Tiempo de conexión máximo
                curl_setopt($ch, CURLOPT_MAXREDIRS, 10);            // Máximo de redirecciones permitidas
            }else{
                $jsonData = json_encode($body);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); 
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Seguir redirecciones
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // Tiempo de espera máximo
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Tiempo de espera máximo
                curl_setopt($ch,CURLOPT_SSLKEYPASSWD, '');
                curl_setopt($ch,CURLOPT_ENCODING, "");
                curl_setopt($ch,CURLOPT_MAXREDIRS, 10);
                curl_setopt($ch,CURLOPT_TIMEOUT, 600);
                curl_setopt($ch,CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            }
            $data = curl_exec($ch);
            if ($data === false) {
                return array('curl_error' => curl_error($ch), 'curerrno' => curl_errno($ch));
            }
            curl_close($ch);

            return $data;
            } else {

                $data = @Tools::file_get_contents($url);
                return $data;
            }
        } catch (\Throwable $e) {
            /* @phpstan-ignore-next-line */
            var_dump($e);
            die();
        } catch (\Exception $e) {
            var_dump($e);
            die();
        }
        
    }

    private function StreamContext()
    {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'protocol_version' => 1.1,
                'timeout' => 10,           // Tiempo de espera máximo
                'ignore_errors' => true     // Ignorar errores HTTP
            )
        ));

        return $context;
    }

    private function string_sanitize($string, $force_lowercase = true, $anal = false)
    {

        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;", "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "_", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        return $clean;
    }

    private function writeCronLog($message)
    {
        $logFile = _PS_MODULE_DIR_.'payco/logs/cron.log';
        $date = date('Y-m-d H:i:s');
        //file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
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

