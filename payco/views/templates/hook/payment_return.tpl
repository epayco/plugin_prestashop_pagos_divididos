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
<style>
    .epayco-title{
        max-width: 900px;
        display: block;
        margin:auto;
        color: #444;
        font-weight: 700;
        margin-bottom: 25px;
    }

    .loader-container{
        position: relative;
        padding: 20px;
        color: #f0943e;
    }

    .epayco-subtitle{
        font-size: 14px;
    }
    .epayco-button-render{
        transition: all 500ms cubic-bezier(0.000, 0.445, 0.150, 1.025);
        transform: scale(1.1);
        box-shadow: 0 0 4px rgba(0,0,0,0);
    }
    .epayco-button-render:hover {
        /*box-shadow: 0 0 4px rgba(0,0,0,.5);*/
        transform: scale(1.2);
    }

    .animated-points::after{
        content: '';
        animation-duration: 2s;
        animation-fill-mode: forwards;
        animation-iteration-count: infinite;
        animation-name: animatedPoints;
        animation-timing-function: linear;
        position: absolute;
    }

    .animated-background {
        animation-duration: 2s;
        animation-fill-mode: forwards;
        animation-iteration-count: infinite;
        animation-name: placeHolderShimmer;
        animation-timing-function: linear;
        color: #f6f7f8;
        background: linear-gradient(to right, #7b7b7b 8%, #999 18%, #7b7b7b 33%);
        background-size: 800px 104px;
        position: relative;
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .loading::before{
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        box-sizing: border-box;
        border-width: 2px;
        border-color: currentColor currentColor currentColor transparent;
        position: absolute;
        margin: auto;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        content: " ";
        display: inline-block;
        background: center center no-repeat;
        background-size: cover;
        border-radius: 50%;
        border-style: solid;
        width: 30px;
        height: 30px;
        opacity: 1;
        -webkit-animation: loaderAnimation 1s infinite linear,fadeIn 0.5s ease-in-out;
        -moz-animation: loaderAnimation 1s infinite linear, fadeIn 0.5s ease-in-out;
        animation: loaderAnimation 1s infinite linear, fadeIn 0.5s ease-in-out;
    }

    @keyframes animatedPoints{
        33%{
            content: '.'
        }
        66%{
            content: '..'
        }
        100%{
            content: '...'
        }

    }
    @keyframes placeHolderShimmer{
        0%{
            background-position: -800px 0
        }
        100%{
            background-position: 800px 0
        }
    }
    @keyframes loaderAnimation{
        0%{
            -webkit-transform:rotate(0);
            transform:rotate(0);
            animation-timing-function:cubic-bezier(.55,.055,.675,.19)
        }
        50%{
            -webkit-transform:rotate(180deg);
            transform:rotate(180deg);
            animation-timing-function:cubic-bezier(.215,.61,.355,1)
        }
        100%{
            -webkit-transform:rotate(360deg);
            transform:rotate(360deg)
        }
    }
</style>
<script type="text/javascript" src="https://checkout.epayco.co/checkout.js"></script>
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