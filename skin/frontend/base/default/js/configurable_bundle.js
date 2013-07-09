if(typeof Product=='undefined') {
    var Product = {};
}
var bconfInstances = $H({});
var productConfigCollection = new Array();
var _size_id = '';
/**************************** CONFIGURABLE PRODUCT **************************/
Product.BConfig = Class.create();
Product.BConfig.prototype = {
    initialize: function(config){
        this.config     = config;
        this.taxConfig  = this.config.taxConfig;
        this.settings   = $$('select[rel="conf-' + config.productId + '"]');
        this.state      = new Hash();
        this.priceTemplate = new Template(this.config.template);
        this.prices     = config.prices;
        
        productConfigCollection[this.config.productId] = this;
        this.settings.each(function(element){
            Event.observe(element, 'change', this.configure.bind(this))
        }.bind(this));

        // fill state
        this.settings.each(function(element){
            var attributeId = element.id.replace(/[a-z]*/, '');
            if(attributeId && this.config.attributes[attributeId]) {
                element.config = this.config.attributes[attributeId];
                element.attributeId = attributeId;
                this.state[attributeId] = false;
            }
        }.bind(this))

        // Init settings dropdown
        var childSettings = [];
        for(var i=this.settings.length-1;i>=0;i--){

            if( this.checkEq(this.settings[i]) ){

                var prevSetting = this.settings[i-1] ? this.settings[i-1] : false;
                var nextSetting = this.settings[i+1] ? this.settings[i+1] : false;
                if(i==0){
                    this.fillSelect(this.settings[i])
                }
                else {
                    this.settings[i].disabled=true;
                }
                $(this.settings[i]).childSettings = childSettings.clone();
                $(this.settings[i]).prevSetting   = prevSetting;
                $(this.settings[i]).nextSetting   = nextSetting;
                childSettings.push(this.settings[i]);

           }

        }

        // try retireve options from url
        var separatorIndex = window.location.href.indexOf('#');
        if (separatorIndex!=-1) {
            var paramsStr = window.location.href.substr(separatorIndex+1);
            this.values = paramsStr.toQueryParams();
            this.settings.each(function(element){
                var attributeId = element.attributeId;
                element.value = this.values[attributeId];
                this.configureElement(element);
            }.bind(this));
        }

        for(var i in this.config.attributes) {
            if(this.config.attributes[i].code == 'size') {
                _size_id = this.config.attributes[i].id;
            }
        }
    },

    configure: function(event){
        var element = Event.element(event);

        if( this.checkEq(element) ){
            this.configureElement(element);
        }
    },

    configureElement : function(element) {
        this.reloadOptionLabels(element);
        if(element.value){
            this.state[element.config.id] = element.value;
            if(element.nextSetting && this.checkEq(element)){
                element.nextSetting.disabled = false;
                this.fillSelect(element.nextSetting);
                this.resetChildren(element.nextSetting);
            }
        }
        else {
            this.resetChildren(element);
        }
        this.reloadPrice();
        var size_id = '';
        for(var i in this.config.attributes) {
            if(this.config.attributes[i].code == 'size') {
                size_id = this.config.attributes[i].id;
            }
        }
        //console.log(element.config.options[0]);
        this.createButtons(this.config.productId,element.config.id,size_id,element.config.options, element.selectedIndex-1);
    },

    createButtons: function (product_id,attr_id,size_id,opts, index) {
		//console.log(options[0].products[0]);
        var optionHtml = '';
        jQuery("#"+ product_id + "_" + size_id).each(function() {
            //console.log(jQuery(this).attr('id'));
            //console.log(jQuery(this).attr('id').indexOf(product_id+'_'));
            //if(jQuery(this).attr('id').indexOf(product_id+'_') != -1) {
                //jQuery(this).css('display','none');
                jQuery(this).each(function(){
                    //console.log(window.bconfInstances._object.12_40);
                    var options = jQuery('option[value!=""]', jQuery(this));
					if(options.length > 0){
                        var j = 0;
                        jQuery(options).each(function(){
							var option = jQuery(this).text();
                            // console.log("Option is:"+option);
							// console.log("Counter is:"+j);
							// console.log("Product is:"+opts[index].products[j]);
							var optionVal = jQuery(this).val();
							var color_value = jQuery('#'+product_id+'_80').val();
							var _product_id = jQuery('#'+product_id+'-'+optionVal+'-'+color_value).val();
							//alert('#'+product_id+'_'+optionVal+'_'+color_value);
							//alert(_product_id);
							//old _product_id - opts[index].products[j]
                            //optionHtml += '<button class="sizeButton-'+product_id+' sizeButton" onclick="productConfigCollection['+product_id+'].setSize('+_product_id+','+ optionVal +',this,'+product_id+','+size_id+');opConfig.reloadPrice(-1,'+product_id+',null);return false;" value="' + optionVal + '">' + option + '</button>';
                            j++;
                        });
                        //jQuery('#sizeButtons-'+product_id).html(optionHtml);
                     }

                    //jQuery("#label-"+ product_id + "_" + size_id).show();
                });
            //}
        });
    },

    reloadOptionLabels: function(element){

        if( this.checkEq(element) ){

            var selectedPrice;
            if(element.options[element.selectedIndex].config){
                selectedPrice = parseFloat(element.options[element.selectedIndex].config.price)
            }
            else{
                selectedPrice = 0;
            }
            for(var i=0;i<element.options.length;i++){
                if(element.options[i].config){
                    element.options[i].text = this.getOptionLabel(element.options[i].config, element.options[i].config.price-selectedPrice);
                }
            }

        }
    },

    resetChildren : function(element){

        if(element.childSettings && this.checkEq(element)) {
            for(var i=0;i<element.childSettings.length;i++){
                element.childSettings[i].selectedIndex = 0;
                element.childSettings[i].disabled = true;
                if(element.config){
                    this.state[element.config.id] = false;
                }
            }
        }
    },

    fillSelect: function(element){
        var attributeId = element.id.replace(/[a-z]*/, '');

        if(!this.checkEq(element)){
            return false;
        }

        var options = this.getAttributeOptions(attributeId);
        this.clearSelect(element);
        element.options[0] = new Option('Select '+element.config.label,'');

        var prevConfig = false;
        if(element.prevSetting){
            if( this.checkEq(element.prevSetting) ){
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }
        }

        if(options) {
            var index = 1;
            for(var i=0;i<options.length;i++){
                var allowedProducts = [];
                if(prevConfig) {
                    for(var j=0;j<options[i].products.length;j++){
                        if(prevConfig.config.allowedProducts
                            && prevConfig.config.allowedProducts.indexOf(options[i].products[j])>-1){
                            allowedProducts.push(options[i].products[j]);
                        }
                    }
                } else {
                    allowedProducts = options[i].products.clone();
                }

                if(allowedProducts.size()>0){
                    options[i].allowedProducts = allowedProducts;
                    element.options[index] = new Option(this.getOptionLabel(options[i], options[i].price), options[i].id);
                    element.options[index].config = options[i];
                    index++;
                }
            }
        }
    },

    getOptionLabel: function(option, price){
        var price = parseFloat(price);
        if (this.taxConfig.includeTax) {
            var tax = price / (100 + this.taxConfig.defaultTax) * this.taxConfig.defaultTax;
            var excl = price - tax;
            var incl = excl*(1+(this.taxConfig.currentTax/100));
        } else {
            var tax = price * (this.taxConfig.currentTax / 100);
            var excl = price;
            var incl = excl + tax;
        }

        if (this.taxConfig.showIncludeTax || this.taxConfig.showBothPrices) {
            price = incl;
        } else {
            price = excl;
        }

        var str = option.label;
        if(price){
            if (this.taxConfig.showBothPrices) {
                str+= ' ' + this.formatPrice(excl, true) + ' (' + this.formatPrice(price, true) + ' ' + this.taxConfig.inclTaxTitle + ')';
            } else {
                str+= ' ' + this.formatPrice(price, true);
            }
        }
        return str;
    },

    checkEq: function(element){
        return ( element.readAttribute('rel') == 'conf-' + this.config.productId );
    },

    formatPrice: function(price, showSign){
        var str = '';
        price = parseFloat(price);
        if(showSign){
            if(price<0){
                str+= '-';
                price = -price;
            }
            else{
                str+= '+';
            }
        }

        var roundedPrice = (Math.round(price*100)/100).toString();

        if (this.prices && this.prices[roundedPrice]) {
            str+= this.prices[roundedPrice];
        }
        else {
            str+= this.priceTemplate.evaluate({price:price.toFixed(2)});
        }
        return str;
    },

    clearSelect: function(element){
        for(var i=element.options.length-1;i>=0;i--){
            if( this.checkEq(element) ){
                element.remove(i);
            }
        }
    },

    getAttributeOptions: function(attributeId){

        if(this.config.attributes[attributeId]){
            //console.log(this.config.attributes[attributeId].options);
            return this.config.attributes[attributeId].options;
        }
    },

    reloadPrice: function(){
        var price = 0;

        for(var i=this.settings.length-1;i>=0;i--){
            var selected = this.settings[i].options[this.settings[i].selectedIndex];
            if(selected.config && this.checkEq(this.settings[i])){
                price += parseFloat(selected.config.price);
            }
        }

        optionsPrice.changePrice('config', price);
        optionsPrice.reload();

        return price;

        if($('product-price-'+this.config.productId)){
            $('product-price-'+this.config.productId).innerHTML = price;
        }
        this.reloadOldPrice();
    },

    reloadOldPrice: function(){
        if ($('old-price-'+this.config.productId)) {

            var price = parseFloat(this.config.oldPrice);
            for(var i=this.settings.length-1;i>=0;i--){
                var selected = this.settings[i].options[this.settings[i].selectedIndex];
                if(selected.config && this.checkEq(this.settings[i])){
                    price+= parseFloat(selected.config.price);
                }
            }
            if (price < 0)
                price = 0;
            price = this.formatPrice(price);

            if($('old-price-'+this.config.productId)){
                $('old-price-'+this.config.productId).innerHTML = price;
            }

        }
    }
}



/**************************** BUNDLE PRODUCT **************************/
Product.Bundle = Class.create();
Product.Bundle.prototype = {
    initialize: function(config){
        this.config = config;
        this.reloadPrice();
    },
    changeSelection: function(selection){
    	var name = selection.name;
    	if(name.substring(0, 7) != 'ratings')
    	{
        	parts = selection.id.split('-');
               
			var required = $(selection).hasClassName('validate-one-required-by-name');
	
	        // $$('.bconf-cont').invoke('remove');
	        if (this.config['options'][parts[2]].isMulti) {
	            selected = new Array();
	            if (selection.tagName == 'SELECT') {
	                for (var i = 0; i < selection.options.length; i++) {
	                    if (selection.options[i].selected && selection.options[i].value != '') {
	                        selected.push(selection.options[i].value);
	                    }
	                }
	            } else if (selection.tagName == 'INPUT') {
	
	                selector = parts[0]+'-'+parts[1]+'-'+parts[2];
	                selections = $$('.'+selector);
	                for (var i = 0; i < selections.length; i++) {
	                    if (selections[i].checked && selections[i].value != '') {
	                        selected.push(selections[i].value);
	                    }
	                }
	            }
	            this.config.selected[parts[2]] = selected;
	        } else {
	
	            try{
	                var cnfPrId = this.config.options[parts[2]].selections[parts[3]].confProductId;
	                var cnfGId = cnfPrId + '_' + parts[3];
	                var confOpts = this.config.options[parts[2]].selections[parts[3]].configurableOptions[cnfGId];
	            }catch(er){/*console.error(er);*/}
	
	            if($('config-container' + parts[3])){
	                $('config-container' + parts[3]).remove();
	            }
	
	            if(typeof confOpts != "undefined"){
	                var atI = this.config.options[parts[2]].selections[parts[3]].confattributes[cnfGId].items;
	                var conf = this.config.options[parts[2]].selections[parts[3]].confattributes[cnfGId];
	                var html = '';
	                atI.each(function(i,j){
	                    //console.log("size id:" + _size_id);
	                    var hide_element = i.attribute_id == _size_id ? ' style="display:none"' : '';
			    		var required_entry = required == true ? 'required-entry ' : '';
	
	                    var myT = new Template('<label id="label-#{product_id}_#{attribute_id}" '+hide_element+'><i>#{label}</i></label><select rel="conf-#{product_id}" name="super_attribute[' + cnfPrId + '][#{attribute_id}]" id="#{product_id}_#{attribute_id}" class="'+required_entry+'super-attribute-select style-#{label}"><option>Select #{label}</option></select>');
	                    html += myT.evaluate(i);
	                    }
	                );
	                selection.next().insert({
	                          after: '<div class="controls" id="config-container' + parts[3] + '">' + html +'</div>'
	                        });
	                bconfInstances.set(cnfGId, new Product.BConfig(confOpts));
	            }
	
	            if (selection.value != '') {
	                this.config.selected[parts[2]] = new Array(selection.value);
	            } else {
	                this.config.selected[parts[2]] = new Array();
	            }
	            this.populateQty(parts[2], selection.value);
	        }
	        this.reloadPrice();
		}
    },

    reloadPrice: function() {
        var calculatedPrice = 0;
        var dispositionPrice = 0;
        for (var option in this.config.selected) {
            if (this.config.options[option]) {
                for (var i=0; i < this.config.selected[option].length; i++) {                	
                    var prices = this.selectionPrice(option, this.config.selected[option][i]);
                    calculatedPrice += Number(prices[0]);
                    dispositionPrice += Number(prices[1]);
                }
            }
        }

        if (this.config.specialPrice) {
            var newPrice = (calculatedPrice*this.config.specialPrice)/100;
            calculatedPrice = Math.min(newPrice, calculatedPrice);
        }

        optionsPrice.changePrice('bundle', calculatedPrice);
        optionsPrice.changePrice('nontaxable', dispositionPrice);
        optionsPrice.reload();

        return calculatedPrice;
    },

    selectionPrice: function(optionId, selectionId) {
        if (selectionId == '' || selectionId == 'none') {
            return 0;
        }
		if (this.config.options[optionId].selections[selectionId].customQty == 1 && !this.config['options'][optionId].isMulti) {
            if ($('bundle-option-' + optionId + '-qty-input')) {
                qty = $('bundle-option-' + optionId + '-qty-input').value;
            } else {
                qty = 1;
            }
        } else {
            qty = this.config.options[optionId].selections[selectionId].qty;
        }

        if (this.config.priceType == '0') {
            price = this.config.options[optionId].selections[selectionId].price;
            tierPrice = this.config.options[optionId].selections[selectionId].tierPrice;

            for (var i=0; i < tierPrice.length; i++) {
                if (Number(tierPrice[i].price_qty) <= qty && Number(tierPrice[i].price) <= price) {
                    price = tierPrice[i].price;
                }
            }
        } else {
            selection = this.config.options[optionId].selections[selectionId];
            if (selection.priceType == '0') {
                price = selection.priceValue;
            } else {
                price = (this.config.basePrice*selection.priceValue)/100;
            }
        }
        //price += this.config.options[optionId].selections[selectionId].plusDisposition;
        //price -= this.config.options[optionId].selections[selectionId].minusDisposition;
        //return price*qty;
        var disposition = this.config.options[optionId].selections[selectionId].plusDisposition +
            this.config.options[optionId].selections[selectionId].minusDisposition;

        var result = new Array(price*qty, disposition*qty);
        return result;
    },

    populateQty: function(optionId, selectionId){
        if (selectionId == '' || selectionId == 'none') {
            this.showQtyInput(optionId, '0', false);
            return;
        }
        if (this.config.options[optionId].selections[selectionId].customQty == 1) {
            this.showQtyInput(optionId, this.config.options[optionId].selections[selectionId].qty, true);
        } else {
            this.showQtyInput(optionId, this.config.options[optionId].selections[selectionId].qty, false);
        }
    },

    showQtyInput: function(optionId, value, canEdit) {
        elem = $('bundle-option-' + optionId + '-qty-input');
        elem.value = value;
        elem.disabled = !canEdit;
        if (canEdit) {
            elem.removeClassName('qty-disabled');
        } else {
            elem.addClassName('qty-disabled');
        }
    },

    changeOptionQty: function (element, event) {
        var checkQty = true;
        if (typeof(event) != 'undefined') {
            if (event.keyCode == 8 || event.keyCode == 46) {
                checkQty = false;
            }
        }
        if (checkQty && (Number(element.value) == 0 || isNaN(Number(element.value)))) {
            element.value = 1;
        }
        parts = element.id.split('-');
        optionId = parts[2];
        if (!this.config['options'][optionId].isMulti) {
            selectionId = this.config.selected[optionId][0];
            this.config.options[optionId].selections[selectionId].qty = element.value*1;
            this.reloadPrice();
        }
    },

    validationCallback: function (elmId, result){
        if (typeof elmId == 'undefined') {
            return;
        }
        var container = $(elmId).up('ul.options-list');
        if (typeof container != 'undefined') {
            if (result == 'failed') {
                container.removeClassName('validation-passed');
                container.addClassName('validation-failed');
            } else {
                container.removeClassName('validation-failed');
                container.addClassName('validation-passed');
            }
        }
    }
}