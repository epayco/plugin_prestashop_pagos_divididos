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
		
	<form action="{$setup_dir|escape:'html':'UTF-8'}" method="POST" id="myform">	
		<div class="col-xs-6 col-md-4 text-center" style="width: 25% !important;">
					<h4>customer Id</h4>
					<input type="text" name="customer_id" id="customer_id">
				</div>
				<div class="col-xs-6 col-md-4 text-center" style="width: 25% !important;">
				<h4>tipo</h4>
				<select name="typefeed">
					<option value="01">fijo</option>
				</select>
				</div>
				<div class="col-xs-6 col-md-4 text-center" style="width: 25% !important;">
					<h4>Valor</h4>
					<input type="text" name="feed" id="feed">
				</div>
				<div class="col-xs-6 col-md-4 text-center" style="width: 25% !important;">
				<br>	
					<input type="submit" class="btn btn-primary" id="create-account-btn" value="Create an account now!">
				</div>
				</form>
				<div id="url" hidden="true">{$setup_dir|escape:'html':'UTF-8'}</div>
				
				<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
				<script type="text/javascript">
				$(document).ready( function(){
										
					const $checkout_form = $( '#myform' );
					    $checkout_form.on('submit', function (event) {  
					    event.preventDefault();
						$(':input[type="submit"]').prop('disabled', true);
					debugger

					    var url=$("#url").text();
					    var customer_id = document.getElementById('customer_id').value.replace(/[ -]/g, "");
					    var feed = document.getElementById('feed').value.replace(/[ -]/g, "");
						var typefeed=  $('select[name="typefeed"] option:selected').val();
						var data={
					        "customer_id":customer_id,
					        "feed":feed,
							"typefeed":typefeed
					 	};
							$.ajax({
								type:"POST",
								url:url,
								data:data,
								beforeSend:function(){},
								success: function(datos){
									debugger
									if(datos =="0"){
										alertarError()
									$(':input[type="submit"]').prop('disabled', false);
									 
									}else{
										alertar()
										$(':input[type="submit"]').prop('disabled', false);
										$checkout_form[0].reset();
									}
								
								}
								});
							});	
						});
					function alertar(){
						document.getElementsByName('chec_')[0].classList.add('alert-success')
						document.getElementsByName('guardado')[0].style.visibility = 'visible';
						$("#snoAlertBox").fadeIn();
  						 closeSnoAlertBox();
					}
					function closeSnoAlertBox() {
					window.setTimeout(function() {
						$("#snoAlertBox").fadeOut(300)
						}, 2000);
					window.setTimeout(function() {
						document.getElementsByName('chec_')[0].classList.remove('alert-success')
						document.getElementsByName('guardado')[0].style.visibility = 'hidden';
						location.reload();
						}, 3000);	
					};

					function alertarError(){
						document.getElementsByName('chec_')[0].classList.add('alert-danger')
						document.getElementsByName('guardado')[0].style.visibility = 'hidden';
						$("#snoAlertBox").fadeIn();
  						 closeSnoAlertBoxError();
					}
					function closeSnoAlertBoxError() {
					window.setTimeout(function() {
						$("#snoAlertBox").fadeOut(300000)
						}, 2000);
					window.setTimeout(function() {
						document.getElementsByName('chec_')[0].classList.remove('alert-danger')
						document.getElementsByName('guardado')[0].style.visibility = 'hidden';
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
					<div class="col-md-2 alert" id"chec_" name="chec_" data-alert="alert">
						<h5 name="guardado" style="visibility: hidden;">{l s='Guardado!' mod='payco'}</h5>
					</div>
					<div class="col-md-4">
						<h5>{l s='' mod='payco'}</h5>
					</div>
				</div>
				</div>
	
</div>
