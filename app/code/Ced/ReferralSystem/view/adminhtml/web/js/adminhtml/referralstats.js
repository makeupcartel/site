/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_ReferralSystem
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

function getDetails(cid) {
 require([
    "jquery",
    "Magento_Ui/js/modal/alert"
], function($,alert){
    var base_url = BASE_URL;
    var split = base_url.split("index/index");            
    var path = split[0]+'referralstats/refersource/';
    $.ajax({
        url: path, 
        type: 'POST',
        dataType: 'json',
        data: {id:cid},
        success: function(result){
            alert({title: 'Registered Referral Sources',
                    content: result});            
        }
    });
});
}
    require([
    "jquery",
    "Magento_Ui/js/modal/alert"
    ],function($,alert){
        $(document).ready(function () {
            $("#allrefer").removeAttr("onclick");
        });
        $('#allrefer').click(function(e){
            var base_url = BASE_URL;
            var split = base_url.split("index/index");            
            var path = split[0]+'referralstats/allrefersource/';
            $.ajax({
                url: path, 
                type: 'POST',
                data: {id:'cid'},
                dataType: 'json',
                success: function(result){
                    alert({title: 'All Registered Referral Sources',
                            content: result});            
                }
            });
        });
    });

