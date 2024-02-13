
define('advanced:views/report/filters/container', ['view'], function (Dep) {

    return Dep.extend({

        templateContent: '<div class="filter">{{{filter}}}</div>',

        events: {
            'click .remove-filter': function () {
                this.trigger('remove-item');
            }
        },

        setup: function () {
            this.scope = this.options.scope;
            this.filterData = this.options.filterData;

            var scope = this.scope;
            var name = this.filterData.name;
            var field = name;

            var link = null;

            if (~name.indexOf('.')) {
                link = name.split('.')[0];
                field = name.split('.')[1];

                scope = this.getMetadata().get('entityDefs.' + this.scope + '.links.' + link + '.entity');
            }

            if (!scope || !field) {
                return;
            }

            this.wait(true);

            this.getModelFactory().create(scope, model => {
                this.createView('filter', 'views/search/filter', {
                    name: field,
                    model: model,
                    params: this.filterData.params,
                    el: this.getSelector() + ' .filter',
                });

                if (scope !== this.scope) {
                    this.on('after:render', () => {
                        var label = this.translate(link, 'links', this.scope) + '.' +
                            this.translate(field, 'fields', scope);

                        this.$el.find('label[data-name="'+field+'"]').html(label);
                    });
                }

                this.wait(false);
            });
        },

        fetch: function () {
            var searchData = this.getView('filter').getView('field').fetchSearch();

            var prepareItem = function (data, name) {
                var type = data.type;

                if (type === 'or' || type === 'and' || type === 'not' || type === 'subQueryIn') {
                    (data.value || []).forEach(item => {
                        prepareItem(item, name);
                    });

                    return;
                }

                var attribute = data.attribute || data.field || name;

                if (~name.indexOf('.') && !~attribute.indexOf('.')) {
                    var link = name.split('.')[0];
                    attribute = link + '.' + attribute;
                }

                data.field = attribute;
                data.attribute = attribute;
            };

            prepareItem(searchData, this.filterData.name);

            return {
                id: this.filterData.id,
                name: this.filterData.name,
                params: searchData,
            };
        },
    });
});
