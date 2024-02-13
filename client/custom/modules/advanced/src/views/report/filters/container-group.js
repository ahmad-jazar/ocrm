
define('advanced:views/report/filters/container-group', ['view'], function (Dep) {

    return Dep.extend({

        template: 'advanced:report/filters/container-group',

        events: {
            'click > a[data-action="removeGroup"]': function () {
                this.trigger('remove-item');
            }
        },

        data: function () {
            var showGroupTypeLabel = true;

            if (this.type === 'and' || this.type === 'or') {
                showGroupTypeLabel = false;
            }

            return {
                type: this.type,
                noOffset: this.options.level > 3,
                showGroupTypeLabel: showGroupTypeLabel
            };
        },

        setup: function () {
            this.filterData = this.options.filterData;
            this.scope = this.options.scope;
            this.type = this.filterData.type;

            this.createView('node', 'advanced:views/report/filters/node', {
                el: this.getSelector() + ' > .node',
                scope: this.scope,
                dataList: this.filterData.params.value || [],
                level: this.options.level,
                filterData: this.filterData,
                isHaving: this.options.isHaving,
            });
        },

        fetch: function () {
            return {
                id: this.filterData.id,
                type: this.filterData.type,
                params: {
                    type: this.filterData.type,
                    value: this.getView('node').fetch(),
                },
            };
        },
    });
});
