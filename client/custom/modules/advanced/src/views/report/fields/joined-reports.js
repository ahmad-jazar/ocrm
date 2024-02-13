
define('advanced:views/report/fields/joined-reports', ['views/fields/link-multiple-with-columns'], function (Dep) {

    return Dep.extend({

        columnList: ['label'],

        selectPrimaryFilterName: 'grid',

        createDisabled: true,

        columnsDefs: {
            'label': {
                type: 'varchar',
                scope: 'Report',
                field: 'joinedReportLabel',
            }
        },

        fetch: function () {
            var data = Dep.prototype.fetch.call(this);

            var dataList = [];

            data[this.idsName].forEach(id => {
                dataList.push({
                    id: id,
                    label: ((data[this.columnsName] || {})[id] || {}).label,
                });
            });

            data.joinedReportDataList = dataList;

            return data;
        },
    });
});
