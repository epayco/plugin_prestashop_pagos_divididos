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
   <br><small class="epayco-subtitle"> Si no se cargan automáticamente, de clic en el botón "Pagar con ePayco"</small>
</p>

<script type="text/javascript" src="https://checkout.epayco.co/checkout.js"></script>
<form id="epayco_form" style="text-align: center;">
     <a href="#" onclick="return openChekout();">
    <img src="https://multimedia-epayco.s3.amazonaws.com/plugins-sdks/Boton-color-espanol.png"/>
    </a>
    <script type="text/javascript">
        var handler = ePayco.checkout.configure({
            key: "{$public_key}",
            test: "{$merchanttest}"
        });
        var extras_epayco = {
            extra5:"P25"
        };
        var isSplit = "{$merchanttest}" === "true" ? true : false;

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
            amount: "{$total|escape:'htmlall':'UTF-8'}".toString(),
            tax: "{$iva|escape:'htmlall':'UTF-8'}".toString(),
            tax_base: "{$baseDevolucionIva|escape:'htmlall':'UTF-8'}".toString(),
            country: "{$iso|lower|escape:'htmlall':'UTF-8'}",
            external: "{$external|escape:'htmlall':'UTF-8'}",
            response: "{$p_url_response|unescape: 'html' nofilter}",
            confirmation: "{$p_url_confirmation|unescape: 'html' nofilter}",
            email_billing: "{$p_billing_email|escape:'htmlall':'UTF-8'}",
            name_billing: "{$p_billing_name|escape:'htmlall':'UTF-8'} {$p_billing_last_name|escape:'htmlall':'UTF-8'}",
            address_billing: "{$p_billing_address|escape:'htmlall':'UTF-8'}",
            lang: "{$lang|escape:'htmlall':'UTF-8'}",
            extra1: "{$extra1|escape:'htmlall':'UTF-8'}",
            extra2: "{$extra2|escape:'htmlall':'UTF-8'}",
            autoclick: "true",
            ip:  "{$ip|escape:'htmlall':'UTF-8'}",
            test: "{$merchanttest|escape:'htmlall':'UTF-8'}".toString(),
            extras_epayco: extras_epayco
            }
            if(isSplit){
                data.split_app_id= "{$merchantid|escape:'htmlall':'UTF-8'}",
                data.split_merchant_id= "{$merchantid|escape:'htmlall':'UTF-8'}",
                data.split_type= "01",
                data.split_primary_receiver= "{$merchantid|escape:'htmlall':'UTF-8'}",
                data.split_primary_receiver_fee= "0",
                data.splitPrimaryReceiver_fee = "0",
                data.splitpayment= "true",
                data.split_rule= "multiple",
                data.split_receivers= split_receivers
            }
           
              

        const apiKey = "{$public_key}";
        const privateKey = "{$private_key}";
        var openChekout = function () {
            console.log(data);
            if(localStorage.getItem("invoicePaymentAgregador") == null){
            localStorage.setItem("invoicePaymentAgregador", data.invoice);
                makePayment(privateKey,apiKey,data, data.external == "true"?true:false)
            }else{
                if(localStorage.getItem("invoicePaymentAgregador") != data.invoice){
                    localStorage.removeItem("invoicePaymentAgregador");
                    localStorage.setItem("invoicePaymentAgregador", data.invoice);
                    makePayment(privateKey,apiKey,data, data.external == "true"?true:false)
                }else{
                   makePayment(privateKey,apiKey,data, data.external == "true"?true:false)
                }
            }
        }
        var makePayment = function (privatekey, apikey, info, external) {
            const headers = { "Content-Type": "application/json" } ;
            headers["privatekey"] = privatekey;
            headers["apikey"] = apikey;
            var payment =   function (){
                return  fetch("https://cms.epayco.co/checkout/payment/session", {
                    method: "POST",
                    body: JSON.stringify(info),
                    headers
                })
                    .then(res =>  res.json())
                    .catch(err => err);
            }
            payment()
                .then(session => {
                    if(session.data.sessionId != undefined){
                        localStorage.removeItem("sessionPaymentAgregador");
                        localStorage.setItem("sessionPaymentAgregador", session.data.sessionId);
                        const handlerNew = window.ePayco.checkout.configure({
                            sessionId: session.data.sessionId,
                            external: external,
                        });
                        handlerNew.openNew()
                    }else{
                        handler.open(data)
                    }
                })
                .catch(error => {
                    error.message;
                });
        }
    openChekout() 
         

</script>
    
{else}
<p class="warning">
  {l s='Hemos notado un problema con tu orden, si crees que es un error puedes contactar a nuestro departamento de Soporte' mod='payco'}
  {l s='' mod='payco'}.
</p>
{/if}