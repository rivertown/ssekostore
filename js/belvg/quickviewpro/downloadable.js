
    Product.Downloadable = Class.create();
    Product.Downloadable.prototype = {
        config : {},
        initialize : function(config){
            this.config = config;
            this.reloadPrice();
            document.observe("dom:loaded", this.reloadPrice.bind(this));
        },
        reloadPrice : function(){
            var price = 0;
            config = this.config;
            $$('.product-downloadable-link').each(function(elm){
                if (config[elm.value] && elm.checked) {
                    price += parseFloat(config[elm.value]);
                }
            });
            try {
                var _displayZeroPrice = optionsPrice.displayZeroPrice;
                optionsPrice.displayZeroPrice = false;
                optionsPrice.changePrice('downloadable', price);
                optionsPrice.reload();
                optionsPrice.displayZeroPrice = _displayZeroPrice;
            } catch (e) {

            }
        }
    };