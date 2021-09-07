/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paymenterio',
                component: 'Paymenterio_Payments/js/view/payment/method-renderer/paymenterio-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
