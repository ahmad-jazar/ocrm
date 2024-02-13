
define('advanced:views/report/fields/order-by-list', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        setupOptions: function () {
            var entityType = this.model.get('entityType');
            var itemList = [];

            itemList.push('');

            var fields = this.getMetadata().get('entityDefs.' + entityType + '.fields') || {};

            Object.keys(fields).forEach(field => {
                if (fields[field].disabled || fields[field].utility) {
                    return;
                }

                if (fields[field].reportDisabled) {
                    return;
                }
                if (fields[field].reportOrderByDisabled) {
                    return;
                }

                if (fields[field].directAccessDisabled) {
                    return;
                }

                if (fields[field].type === 'linkMultiple') {
                    return;
                }

                if (fields[field].type !== 'map') {
                    if (!this.getFieldManager().isEntityTypeFieldAvailable(entityType, field)) {
                        return;
                    }

                    itemList.push('ASC:' + field);
                    itemList.push('DESC:' + field);
                }
            });

            this.params.options = itemList;

            this.setupTranslatedOptions();
        },

        setupTranslatedOptions: function () {
            this.translatedOptions = {};

            this.translatedOptions[''] = this.translate('Default');

            this.params.options.forEach(function (item) {
                if (item === '') {
                    return;
                }

                var order = item.substr(0, item.indexOf(':'));
                var p = item.substr(item.indexOf(':') + 1);

                var scope = this.model.get('entityType');
                var entityType = scope;

                var field = p;

                var link = false;

                if (~p.indexOf(':')) {
                    p = field = p.split(':')[1];
                }

                if (~p.indexOf('.')) {
                    link = p.split('.')[0];
                    field = p.split('.')[1];
                    scope = this.getMetadata().get('entityDefs.' + entityType + '.links.' + link + '.entity');
                }

                this.translatedOptions[item] = this.translate(field, 'fields', scope);

                if (link) {
                    this.translatedOptions[item] = this.translate(link, 'links', entityType) + '.' +
                        this.translatedOptions[item];
                }

                if (order !== 'LIST') {
                    this.translatedOptions[item] = this.translatedOptions[item] +
                        ' (' + this.translate(order, 'orders', 'Report').toUpperCase() + ')';
                }
            }, this);
        },
    });
});
