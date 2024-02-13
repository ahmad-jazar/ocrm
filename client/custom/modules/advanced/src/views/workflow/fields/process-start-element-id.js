
define('advanced:views/workflow/fields/process-start-element-id', 'views/fields/enum', function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'change:startElementNames', function (model, startElementNames) {
                this.translatedOptions = startElementNames;
            });

            this.listenTo(this.model, 'change:startElementIdList', function (model, startElementIdList) {
                this.params.options = startElementIdList || [];
                this.reRender();
            });

            this.listenTo(this.model, 'change:target', function (model, startElementIdList) {
                this.params.options = [];
                this.reRender();
            });
        },

    });
});
