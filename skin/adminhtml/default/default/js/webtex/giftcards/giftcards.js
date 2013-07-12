var updateBalance = function update(url,customerId){
    new Ajax.Updater('customer_balance',url,{
            parameters:{giftcard_code:$F('giftcard_code'),customer_id:customerId}
        }
   
    );
    $('giftcard_code').value=""; 
};

var toggleCardsUsing = function toggle(url){
    new Ajax.Request(url, {
        method: 'post',
        parameters:{giftcard_use:$F('giftcard_use')},
        onSuccess: function() {
            order.itemsUpdate();
        }
    });
};