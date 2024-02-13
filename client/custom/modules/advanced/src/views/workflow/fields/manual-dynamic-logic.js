
define('advanced:views/workflow/fields/manual-dynamic-logic',
['views/admin/field-manager/fields/dynamic-logic-conditions'], function (Dep) {

    return Dep.extend({

        data: function () {
            return {
                value: this.getValueForDisplay(),
            };
        },

        getValueForDisplay: function () {
            if (!this.model.get(this.name)) {
                return this.translate('None');
            }
        },

        setupEntityType: function () {
            this.options.scope = this.scope = this.model.get('entityType');

            this.listenTo(this.model, 'change:entityType', () => {
                this.options.scope = this.scope = this.model.get('entityType');

                if (this.scope) {
                    this.createStringView();
                }
            });
        },

        setup: function () {
            this.setupEntityType();

            this.conditionGroup = Espo.Utils.cloneDeep(
                (this.model.get(this.name) || {}).conditionGroup || []);

            this.createStringView();
        }
    });
});
