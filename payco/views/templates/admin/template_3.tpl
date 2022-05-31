{*
* 2007-2021 PrestaShop
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
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
<div class="row payco-header">
		
	<form action="{$update_dir|escape:'html':'UTF-8'}" method="POST" id="myform2">	
		<div class="col-xs-6 col-md-4 text-center" style="width: 25% !important;">
					<h4>customer Id</h4>
					<input type="text" name="customer_id_" id="customer_id_">
				</div>
				<div class="col-xs-6 col-md-4 text-center" style="width: 25% !important;">
				<h4>tipo</h4>
				<select name="typefeed_">
					<option value="01">fijo</option>

				</select>
				</div>
				<div class="col-xs-6 col-md-4 text-center" style="width: 25% !important;">
					<h4>Valor</h4>
					<input type="text" name="feed_" id="feed_">
				</div>
				<div class="col-xs-6 col-md-4 text-center" style="width: 25% !important;">
				<br>	
					<input type="submit" class="btn btn-primary" id="create-account-btn" value="Actualizar customer!">
				</div>
				</form>
				<div id="url_update" hidden="true">{$update_dir|escape:'html':'UTF-8'}</div>
				
				<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
				<script type="text/javascript">
				$(document).ready( function(){
										
					const $checkout_form = $( '#myform2' );
					    $checkout_form.on('submit', function (event) {  
					    event.preventDefault();
						debugger
						$(':input[type="submit"]').prop('disabled', true);
					

					    var url_update=$("#url_update").text();
					    var customer_id_ = document.getElementById('customer_id_').value.replace(/[ -]/g, "");
					    var feed_ = document.getElementById('feed_').value.replace(/[ -]/g, "");
						var typefeed_=  $('select[name="typefeed_"] option:selected').val();
						var data_={
					        "customer_id_":customer_id_,
					        "feed_":feed_,
							"typefeed_":typefeed_
					 	};
							$.ajax({
								type:"POST",
								url:url_update,
								data:data_,
								beforeSend:function(){},
								success: function(datos){
									
									if(datos =="0"){
										alertarError2()
									$(':input[type="submit"]').prop('disabled', false);
									 
									}else{
										alertar3()
										$(':input[type="submit"]').prop('disabled', false);
										$checkout_form[0].reset();
									}
								
								}
								});
							});	
						});
					function alertar3(){
						debugger
						document.getElementsByName('chec_3')[0].classList.add('alert-success')
						document.getElementsByName('guardado3')[0].style.visibility = 'visible';
						$("#snoAlertBox").fadeIn();
  						 closeSnoAlertBox();
					}
					function closeSnoAlertBox() {
					window.setTimeout(function() {
						$("#snoAlertBox").fadeOut(300)
						}, 2000);
					window.setTimeout(function() {
						document.getElementsByName('chec_3')[0].classList.remove('alert-success')
						document.getElementsByName('guardado3')[0].style.visibility = 'hidden';
						}, 3000);	
					};

					function alertarError2(){
						document.getElementsByName('chec_3')[0].classList.add('alert-danger')
						document.getElementsByName('guardado3')[0].style.visibility = 'hidden';
						$("#snoAlertBox").fadeIn();
  						 closeSnoAlertBoxError2();
					}
					function closeSnoAlertBoxError2() {
					window.setTimeout(function() {
						$("#snoAlertBox").fadeOut(300000)
						}, 2000);
					window.setTimeout(function() {
						document.getElementsByName('chec_3')[0].classList.remove('alert-danger')
						document.getElementsByName('guardado3')[0].style.visibility = 'hidden';
						}, 3000);	
					};

				</script>
			</div>
	<hr />
	<div class="payco-content">
				<div class="row">
					<div class="col-md-4">
						<h5>{l s=''}</h5>
					</div>
					<div class="col-md-2 alert" id"chec_3" name="chec_3" data-alert="alert">
						<h5 name="guardado3" style="visibility: hidden;">{l s='Actualizado!' mod='payco'}</h5>
					</div>
					<div class="col-md-4">
						<h5>{l s='' mod='payco'}</h5>
					</div>
				</div>
				</div>
	
</div>
