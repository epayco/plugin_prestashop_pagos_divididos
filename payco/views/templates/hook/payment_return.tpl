{*
* 2007-2017 PrestaShop
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
*}
{if $status == 'ok'}

<div class="loader-container">
    <div class="loading"></div>
</div>
<p style="text-align: center;" class="epayco-title">
    <span class="animated-points">Cargando metodos de pago</span>
   <br><small class="epayco-subtitle"> Si no se cargan autom谩ticamente, de clic en el bot贸n "Pagar con ePayco"</small>
</p>

<script type="text/javascript" src="https://epayco-checkout-testing.s3.amazonaws.com/checkout.preprod.js?version=1643645084821"></script>
<form id="epayco_form" style="text-align: center;">
     <a href="#" onclick="return theFunction();">
        <img src="https://multimedia.epayco.co/epayco-landing/btns/Boton-epayco-color1.png" />
    </a>
    <script type="text/javascript">
        var handler = ePayco.checkout.configure({
            key: "{$public_key}",
            test: "{$merchanttest}"
        });
         var isSplit = false;

        var js_array ="{$split_receivers|@print_r}";

        const js_arrays = js_array.substring(0, js_array.length - 1);
        var split_receiver =JSON.parse(js_arrays.replace(/'/g,'"'));
        if(split_receiver.length > 0){
            isSplit = true;
        }
            let split_receivers = [];
            for(var jsa of split_receiver){
                split_receivers.push({
                    "id" :  jsa.id,
                    "total": jsa.total,
                    "iva" : jsa.iva,
                    "base_iva": jsa.base_iva,
                    "fee" : jsa.fee
                });
            }


        var data={
            name: "{$descripcion}",
            description: "{$descripcion}",
            invoice: "{$refVenta|escape:'htmlall':'UTF-8'}",
            currency: "{$currency|lower|escape:'htmlall':'UTF-8'}",
            amount: "{$total|escape:'htmlall':'UTF-8'}",
            tax: "{$iva|escape:'htmlall':'UTF-8'}",
            tax_base: "{$baseDevolucionIva|escape:'htmlall':'UTF-8'}",
            country: "{$iso|lower|escape:'htmlall':'UTF-8'}",
            external: "{$external|escape:'htmlall':'UTF-8'}",
            response: "{$p_url_response|unescape: 'html' nofilter}",
            confirmation: "{$p_url_confirmation|unescape: 'html' nofilter}",
            email_billing: "{$p_billing_email|escape:'htmlall':'UTF-8'}",
            name_billing: "{$p_billing_name|escape:'htmlall':'UTF-8'} {$p_billing_last_name|escape:'htmlall':'UTF-8'}",
            address_billing: "{$p_billing_address|escape:'htmlall':'UTF-8'}",
            lang: "{$lang|escape:'htmlall':'UTF-8'}",
            extra1: "{$extra1|escape:'htmlall':'UTF-8'}",
            extra2: "{$extra2|escape:'htmlall':'UTF-8'}"
            }
            if(isSplit){
            data.split_app_id= "{$merchantid|escape:'htmlall':'UTF-8'}",
            data.split_merchant_id= "{$merchantid|escape:'htmlall':'UTF-8'}",
            data.split_type= "01",
            data.split_primary_receiver= "{$merchantid|escape:'htmlall':'UTF-8'}",
            data.split_primary_receiver_fee= "0",
            data.splitpayment= "true",
            data.split_rule= "multiple",
            data.split_receivers= split_receivers
        }
            handler.open(data)
                function theFunction () {
                handler.open(data)
            }

</script>
    
{else}
<p class="warning">
  {l s='Hemos notado un problema con tu orden, si crees que es un error puedes contactar a nuestro departamento de Soporte' mod='payco'}
  {l s='' mod='payco'}.
</p>
{/if}