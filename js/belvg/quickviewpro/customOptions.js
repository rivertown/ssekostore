

Product.QuickviewOptions = Class.create();
Product.QuickviewOptions.prototype = {
    initialize : function(config) {
        this.config = config;
        this.reloadPrice();
        //document.observe("dom:loaded", this.reloadPrice.bind(this));
    },
    reloadPrice : function() {
        var thisOpt = this;
        var config  = this.config;
        var skipIds = [];
        $$('body .popup .product-custom-option').each(function(element){
            var optionId = 0;
            element.name.sub(/[0-9]+/, function(match){
                optionId = parseInt(match[0], 10);
            });

            if (config[optionId]) {
                var configOptions = config[optionId];
                var curConfig = {price: 0};
                if (element.type == 'checkbox' || element.type == 'radio') {
                    if (element.checked) {
                        if (typeof configOptions[element.getValue()] != 'undefined') {
                            curConfig = configOptions[element.getValue()];
                        }
                    }
                } else if(element.hasClassName('datetime-picker') && !skipIds.include(optionId)) {
                    dateSelected = true;
                    $$('.product-custom-option[id^="options_' + optionId + '"]').each(function(dt){
                        if (dt.getValue() == '') {
                            dateSelected = false;
                        }
                    });
                    if (dateSelected) {
                        curConfig = configOptions;
                        skipIds[optionId] = optionId;
                    }
                } else if(element.type == 'select-one' || element.type == 'select-multiple') {
                    if ('options' in element) {
                        $A(element.options).each(function(selectOption){
                            if ('selected' in selectOption && selectOption.selected) {
                                if (typeof(configOptions[selectOption.value]) != 'undefined') {
                                    curConfig = configOptions[selectOption.value];
                                }
                            }
                        });
                    }
                } else {
                    if (element.getValue().strip() != '') {
                        curConfig = configOptions;
                    }
                }
                
                if(element.type == 'select-multiple' && ('options' in element)) {
                    $A(element.options).each(function(selectOption) {
                        if (('selected' in selectOption) && typeof(configOptions[selectOption.value]) != 'undefined') {
                            if (selectOption.selected) {
                                curConfig = configOptions[selectOption.value];
                            } else {
                                curConfig = {price: 0};
                            }

                            if (Object.isFunction(optionsPrice.addCustomPrices)) {
                                optionsPrice.addCustomPrices(optionId + '-' + selectOption.value, curConfig);
                            } else {
                                optionsPrice.changePrice(optionId + '-' + selectOption.value, curConfig);
                            }

                            optionsPrice.reload();
                        }
                    });
                } else {
                    if (Object.isFunction(optionsPrice.addCustomPrices)) {
                        optionsPrice.addCustomPrices(element.id || optionId, curConfig);
                    } else {
                        if (element.type == 'checkbox' || element.type == 'radio') {
                            if (element.checked) {
                                thisOpt.reloadPrice14(optionId, element.value);
                            }
                        } else {
                            thisOpt.reloadPrice14(optionId, element.value);
                        }
                    }

                    optionsPrice.reload();
                }
            }
        });
    },
    reloadPrice14 : function(a, b) {
        if (this.config[a][b]) {
            var price = parseFloat(this.config[a][b]);
            optionsPrice.changePrice('options', price);
        } else {
            optionsPrice.changePrice('options', 0);
        }
    }
}

