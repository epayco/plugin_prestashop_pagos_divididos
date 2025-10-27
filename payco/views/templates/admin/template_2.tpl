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
        <div class="payco-content">
            <div class="row">
                <table class="table">
            <caption></caption>
            <thead>
                <tr>
                <th scope="col">#</th>
                <th scope="col">customer_id</th>
                <th scope="col">tipo de split</th>
                <th scope="col">fee</th>
                </tr>
            </thead>
            {foreach from=$team_record key=team item=record}
            <tbody>
                <tr>
                <th scope="row" style="width: 20%;">
                <a href="javascript:eliminar({$record.id},{$record.customer_id})" class="btn btn-danger" id="eliminar_rule">eliminar</a>
                </th>
                <td>{$record.customer_id}</td>
                <td>{$record.typefeed}</td>
                <td>{$record.feed}</td>
                </tr>
            </tbody>
            {/foreach}
            </table>
            <div id="url_delete_dir" hidden="true">{$delete_dir|escape:'html':'UTF-8'}</div>
            <br>
            <div class="col-md-4">
                <h5>{l s=''}</h5>
            </div>
            <div class="col-md-2 alert" id"chec2_" name="chec2_" data-alert="alert">
                <h5 name="guardadop" style="visibility: hidden;">{l s='Eliminado!' mod='payco'}</h5>
            </div>
            <div class="col-md-4">
                <h5>{l s='' mod='payco'}</h5>
            </div>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js"></script>
            <script type="text/javascript">  					
                function eliminar(id,customer_id){
                     var url=$("#url_delete_dir").text();
                     var data={
                        "customer_id":customer_id,
                        "id":id
                    };
                    $.ajax({
                        type:"POST",
                        url:url,
                        data:data
                    })
                     .done(function(done) {
                        if(done=="1"){
                            alertar2()
                        }
                     })
                     .fail(function(error) {
                          console.log(error)
                     });
                }
                function alertar2(){
                    document.getElementsByName('chec2_')[0].classList.add('alert-success')
                    document.getElementsByName('guardadop')[0].style.visibility = 'visible';
                    $("#snoAlertBox").fadeIn();
                    closeSnoAlertBox2();
                }
                function closeSnoAlertBox2() {
                    window.setTimeout(function() {
                        $("#snoAlertBox").fadeOut(300)
                    }, 2000);
                    window.setTimeout(function() {
                        document.getElementsByName('chec2_')[0].classList.remove('alert-success')
                        document.getElementsByName('guardadop')[0].style.visibility = 'hidden';
                        location.reload();
                    }, 3000);
                };
            </script>
            </div>
        </div>
    </div>
</div>
