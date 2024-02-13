
define('advanced:views/report/fields/filters-control-2', ['views/fields/base'], function (Dep) {

    return Dep.extend({

        editTemplate: 'advanced:report/fields/filters-control-2/edit',
        detailTemplate: 'advanced:report/fields/filters-control-2/detail',

        setup: function () {
            var entityType = this.model.get('entityType');
            var dataList = this.model.get('filtersDataList') || [];

            // for backward compatibility
            if (!dataList.length) {
                var filterList = this.model.get('filters') || [];
                var filtersData = this.model.get('filtersData') || {};

                filterList.forEach(function (item) {
                    if (!(item in filtersData)) {
                        return;
                    }

                    var data = {};

                    data.id = Math.random().toString(16).slice(2);
                    data.name = item;

                    data.params = filtersData[item];
                    dataList.push(data);
                }, this);
            }

            this.createView('node', 'advanced:views/report/filters/node', {
                el: this.getSelector() + ' > .node-row > .node',
                scope: entityType,
                dataList: dataList
            });
        },

        afterRender: function () {},

        fetch: function () {
            return {
                filtersDataList: this.getView('node').fetch(),
                filtersData: null,
                filters: null
            };
        },
    });
});
