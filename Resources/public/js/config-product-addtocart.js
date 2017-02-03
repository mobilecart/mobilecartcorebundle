var ConfigProductAddtocart = function(obj) {
    this.container = obj.container;
    this.productConfig = obj.productConfig;
    this.productType = obj.productType;
    this.initOptions();
    this.attachEvents();
};

ConfigProductAddtocart.prototype = {
    options : {},
    productConfig: {},
    attachEvents: function() {
        var self = this;

        self.container.find('select.config-option').change(function(e){
            var anOption = $(this);
            self.selectConfigurableOption(anOption.attr('name'), anOption.val());
        });

        self.container.find('form.add-to-cart').submit(function(e){

            if (self.productConfig['config_values'].length == 0) {
                return true;
            }

            var isOk = false;
            var simples = [];
            var i = 0;
            for(key in self.options) {

                if (i == 0) {
                    simples = self.options[key];
                } else {
                    simples = _.intersection(self.options[key], simples);
                }
                i++;
            }

            if (simples.length > 0) {
                _.forEach(simples, function(simple){
                    if (_.contains(self.productConfig['is_in_stock'], simple)) {
                        self.container.find('input.simple_id').val(simple);
                        isOk = true;
                        //break;
                    }
                });
            }

            return isOk;
        });
    },
    initOptions: function() {
        var self = this;
        switch(self.productType) {
            case 1:

                break;
            case 2:
                if (_.has(self.productConfig, 'config_values')
                    && _.isArray(self.productConfig['config_values'])) {

                    var configValues = self.productConfig['config_values'];
                    _.forEach(configValues, function(configValue){
                        if (_.has(configValue, 'var_code')) {
                            var varCode = configValue['var_code'];
                            var products = [];
                            if (_.has(configValue, 'product_values')
                                && _.isArray(configValue['product_values'])) {

                                var productValue = _.first(configValue['product_values']);
                                if (_.has(productValue, 'products')
                                    && _.isArray(productValue['products'])) {

                                    products = productValue['products'];
                                }
                            }

                            self.options[varCode] = products;
                        }
                    });
                }
                break;
            default:

                break;
        }
    },
    selectConfigurableOption: function(varCode, varValue) {
        var self = this;

        if (_.has(self.productConfig, 'config_values')) {
            var configValues = self.productConfig['config_values'];
            _.forEach(configValues, function(configValue){
                if (_.has(configValue, 'var_code') &&
                    configValue['var_code'] == varCode) {

                    if (_.has(configValue, 'product_values')) {
                        var productValues = configValue['product_values'];
                        _.forEach(productValues, function(productValue){
                            if (_.has(productValue, 'value') &&
                                productValue['value'] == varValue &&
                                _.has(productValue, 'products')) {

                                self.options[varCode] = productValue['products'];
                            }
                        });
                    }
                }
            });
        }
    }
};