{*
* 2007-2015 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!DOCTYPE html>
<head>
  <link rel="stylesheet" type="text/css"  href="{$css_url}style.css">
  <link rel="stylesheet" type="text/css"  href="{$css_url}general.css">
  <link  rel="stylesheet" type="text/css" href="{$css_url}card-js.min.css" />
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
</head>

<body>

                <div class="middle-xs bg_onpage porcentbody m-0" style="margin: 0">
                    <link rel="stylesheet" type="text/css"  href="{$css_url}style.css" />
                    <link rel="stylesheet" type="text/css"  href="{$css_url}general.css" />
                    <link  rel="stylesheet" type="text/css" href="{$css_url}card-js.min.css" />
                    <div class="centered" id="centered">
                        <div class="onpage relative" id="web-checkout-content" style="
                                    border-radius: 5px;">
                            <div class="body-modal fix-top-safari">

                                <div class="bar-option hidden-print">
                                    <div class="dropdown select-pais pointer">
                                        <ul class="nav nav-tabs js-nav-tabs" id="form-nav" role="tablist" style="/* width: 716.75px; */">
                                            <li id="tab_step1" class="nav-item"><a href="#step1" role="tab" data-toggle="tab" class="nav-link active" aria-selected="true" aria-expanded="true">Tarjeta de Credito</a></li>
                                            <li id="tab_step3" class="nav-item"><a href="#step3" role="tab" data-toggle="tab" class="nav-link" aria-selected="false" aria-expanded="false">Efectivo</a></li>
                                            <li id="tab_step2" class="nav-item"><a href="#step2" role="tab" data-toggle="tab" class="nav-link" aria-expanded="false">PSE</a></li>
                                        </ul>
                                        <br>
                                    </div>
                                </div>

                                <div class="wc scroll-content">
                                    <div class="menu-select">

                                        <form action="{$action}" id="token-credit" method="post">
                                            <input id="ePaycopaymentMethod" name="ePaycopaymentMethod" hidden value="" type="text" />

                                            <div class="input-form" id="cardOutner">
                                                <span style="
                                                    position: absolute;
                                                    padding-left: 63px;
                                                    padding-top: 58px !important;
                                                    line-height: 40px;
                                                    font-size: 5px;
                                                    ">
                                                    <i class="fas fa-user loadshield2" style="color: #158cba; font-size: 17px;" aria-hidden="true"></i>
                                                </span>
                                                <input type="text" class="binding-input inspectletIgnore"  name="card_name" id="card_name" placeholder="Nombre como aparece en la tarjeta"  autocomplete="off" required />
                                            </div>


                                            <div class="input-form">
                                                <div class="select-option cuotas bordergray" style="width: 100px;
                                                   position: absolute;">
                                                    <select class="select binding-select" name="documento" style="width: 100%;" required>
                                                        <option value="CC">CC</option>
                                                        <option value="NIT">NIT</option>
                                                        <option value="CE">CE</option>
                                                        <option value="PPN">PPN</option>
                                                        <option value="SSN">SSN</option>
                                                        <option value="LIC">LIC</option>
                                                        <option value="DNI">DNI</option>
                                                    </select>
                                                </div>
                                                <input type="text" class="binding-input inspectletIgnore"  name="document_number" id="document_number" placeholder="Numero de documento"  autocomplete="off" style="
                                                    margin-left: 31%; width: 65%;" required />
                                            </div>
                                            <div id="token-credit-form">
                                            <div class="input-form">
                                                <div class="form-container">
                                                    <div class="field-container">
                                                        <span style="
                                                            position: absolute;
                                                            padding-left: 63px;
                                                            padding-top: 64px !important;">
                                                        <i class="far fa-credit-card loadshield2" style="color: #158cba; font-size: 18px;" aria-hidden="true"></i>
                                                        </span>

                                                        <input id="cardnumber" type="text" name="cardnumber" required />
                                                        <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                                            xmlns:xlink="http://www.w3.org/1999/xlink">
                                                        </svg>
                                                    </div>
                                                    <div class="field-container">
                                                        <input id="expirationdate" type="text" pattern="[0-9]*" inputmode="numeric" hidden="true" />
                                                    </div>
                                                    <div class="field-container">
                                                        <input id="securitycode" type="text" pattern="[0-9]*" inputmode="numeric" hidden="true" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="select-option bordergray vencimiento" style="float:left" id="expiration">

                                                <div class="input-form full-width noborder monthcredit nomargin">
                                                    <span class="icon-date_range icon-select" style="color: #3582b7;">
                                                        <i class="far fa-calendar-alt"></i>
                                                    </span>
                                                    <input class="binding-input inspectletIgnore" id="month-value" name="month" placeholder="MM" maxlength="2" autocomplete="off" data-epayco="card[exp_month]"  inputmode="numeric"  pattern="[0-9]*" required />
                                                </div>

                                                <div class="" style="
                                                    float:left;
                                                    width:10%;
                                                    margin:0;
                                                    text-align:center;
                                                    line-height: 40px;
                                                    height: 38px;
                                                    background-color: white;
                                                    color:#a3a3a3;">
                                                    /
                                                </div>

                                                <div class="input-form full-width normalinput noborder yearcredit nomargin">
                                                    <input class="binding-input inspectletIgnore" name="year" id="year-value" placeholder="AAAA" maxlength="4" autocomplete="off" data-epayco="card[exp_year]" pattern="[0-9]*" inputmode="numeric" required />
                                                </div>

                                            </div>

                                            <div class="input-form normalinput cvv_style"       id="cvc_">
                                                <input type="password" placeholder="CVC" class="nomargin  binding-input" name="card_cvc" id="card_cvc" autocomplete="off" maxlength="4" data-epayco="card[cvc]" style="width: 85% !important;" required />
                                                <i class="fa fa-question-circle pointer" aria-hidden="true" style="right: 19px; padding: 0; color: #3582b7;" id="look-cvv"></i>
                                            </div>

                                            <div class="select-option cuotas bordergray" >
                                                <select class="select binding-select" name="dues" style="width: 100%;" required>
                                                    <option value="">Cuotas</option>
                                                    <option value="01">1</option>
                                                    <option value="02">2</option>
                                                    <option value="03">3</option>
                                                    <option value="04">4</option>
                                                    <option value="05">5</option>
                                                    <option value="06">6</option>
                                                    <option value="07">7</option>
                                                    <option value="08">8</option>
                                                    <option value="09">9</option>
                                                    <option value="10">10</option>
                                                    <option value="11">11</option>
                                                    <option value="12">12</option>
                                                    <option value="13">13</option>
                                                    <option value="14">14</option>
                                                    <option value="15">15</option>
                                                    <option value="16">16</option>
                                                    <option value="17">17</option>
                                                    <option value="18">18</option>
                                                    <option value="19">19</option>
                                                    <option value="20">20</option>
                                                    <option value="21">21</option>
                                                    <option value="22">22</option>
                                                    <option value="23">23</option>
                                                    <option value="24">24</option>
                                                    <option value="25">25</option>
                                                    <option value="26">26</option>
                                                    <option value="27">27</option>
                                                    <option value="28">28</option>
                                                    <option value="29">29</option>
                                                    <option value="30">30</option>
                                                    <option value="31">31</option>
                                                    <option value="32">32</option>
                                                    <option value="33">33</option>
                                                    <option value="34">34</option>
                                                    <option value="35">35</option>
                                                    <option value="36">36</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="cash-form" style="display: none;">

                                            <div class="select-option cuotas bordergray">
                                                <select class="select binding-select" name="typePersonCash" style="width: 100%;" required="">
                                                    <option value="0">Natural</option>
                                                    <option value="1">Juridica</option>
                                                </select>
                                            </div>

                                            <div class="clearfix" style="padding: 10px;"></div>
                                            <div class="select-option cuotas bordergray">
                                                <select class="select binding-select" name="cashPayments" style="width: 100%;" required="">
                                                    <option value="efecty">efecty</option>
                                                    <option value="baloto">baloto</option>
                                                    <option value="gana">gana</option>
                                                    <option value="redservi">redservi</option>
                                                    <option value="puntored">puntored</option>
                                                    <option value="sured">sured</option>
                                                    <option value="apostar">apostar</option>
                                                    <option value="susuerte">susuerte</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div id="pse-form" style="display: none;">

                                            <div class="select-option cuotas bordergray">
                                                <select class="select binding-select" name="typePersonBank" style="width: 100%;" required="">
                                                    <option value="0">Natural</option>
                                                    <option value="1">Juridica</option>
                                                </select>
                                            </div>

                                            <div class="clearfix" style="padding: 10px;"></div>
                                            <div class="select-option cuotas bordergray">
                                                <select class="select binding-select" name="bank" style="width: 100%;" required="">
                                                    {if $isText eq "true"}
                                                        <option value="1077">BANKA</option>
                                                        <option value="1022">BANCO UNION COLOMBIANO</option>
                                                    {else}
                                                        <option value="1059">BANCAMIA S.A.</option>
                                                        <option value="1040">BANCO AGRARIO</option>
                                                        <option value="1052">BANCO AV VILLAS</option>
                                                        <option value="1013">BANCO BBVA COLOMBIA S.A.</option>
                                                        <option value="1032">BANCO CAJA SOCIAL</option>
                                                        <option value="1066">BANCO COOPERATIVO COOPCENTRAL</option>
                                                        <option value="1558">BANCO CREDIFINANCIERA</option>
                                                        <option value="1051">BANCO DAVIVIENDA</option>
                                                        <option value="1001">BANCO DE BOGOTA</option>
                                                        <option value="1023">BANCO DE OCCIDENTE</option>
                                                        <option value="1062">BANCO FALABELLA</option>
                                                        <option value="1012">BANCO GNB SUDAMERIS</option>
                                                        <option value="1006">BANCO ITAU</option>
                                                        <option value="1060">BANCO PICHINCHA S.A.</option>
                                                        <option value="1002">BANCO POPULAR</option>
                                                        <option value="1065">BANCO SANTANDER COLOMBIA</option>
                                                        <option value="1069">BANCO SERFINANZA</option>
                                                        <option value="1007">BANCOLOMBIA</option>
                                                        <option value="1061">BANCOOMEVA S.A.</option>
                                                        <option value="1283">CFA COOPERATIVA FINANCIERA</option>
                                                        <option value="1009">CITIBANK</option>
                                                        <option value="1370">COLTEFINANCIERA</option>
                                                        <option value="1292">CONFIAR COOPERATIVA FINANCIERA</option>
                                                        <option value="1291">COOFINEP COOPERATIVA FINANCIERA</option>
                                                        <option value="1289">COTRAFA</option>
                                                        <option value="1097">DALE</option>
                                                        <option value="1551">DAVIPLATA</option>
                                                        <option value="1303">GIROS Y FINANZAS COMPAÑIA DE FINANCIAMIENTO S.A.</option>
                                                        <option value="1637">IRIS</option>
                                                        <option value="1801">MOVII S.A.</option>
                                                        <option value="1507">NEQUI</option>
                                                        <option value="1151">RAPPIPAY</option>
                                                        <option value="1019">SCOTIABANK COLPATRIA</option>
                                                    {/if}
                                                </select>
                                            </div>
                                        </div>

                                            <div class="clearfix" style="padding: 10px;"></div>
                                        </form>
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/imask/3.4.0/imask.min.js"></script>
                        <script src="{$js_url}index2.js"></script>
                        <script src="https://kit.fontawesome.com/fc569eac4d.js" crossorigin="anonymous"></script>
                    <script type='text/javascript' src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

                    <script type="text/javascript">
                        $( document ).ready( function() {
                            $("#token-credit-form").show();
                            $("#tab_step1").click(function() {
                                $("#cash-form").hide();
                                $("#cardOutner").show();
                                $("#token-credit-form").show();
                                $("#pse-form").hide()
                                $('#ePaycopaymentMethod').val('TC');
                            });
                            $("#tab_step3").click(function() {
                                $("#cardOutner").hide();
                                $("#token-credit-form").hide();
                                $("#cash-form").show();
                                $("#pse-form").hide();
                                $('#ePaycopaymentMethod').val('CASH');
                            });
                            $("#tab_step2").click(function() {
                                $("#cardOutner").hide();
                                $("#token-credit-form").hide();
                                $("#cash-form").hide();
                                $("#pse-form").show();
                                $('#ePaycopaymentMethod').val('PSE');
                            });
                        });
                    </script>
                </div>
 </body>
       <script src="https://cdnjs.cloudflare.com/ajax/libs/imask/3.4.0/imask.min.js"></script>
       <script src="{$js_url}index2.js"></script>
      <script src="https://kit.fontawesome.com/fc569eac4d.js" crossorigin="anonymous"></script>
</html>