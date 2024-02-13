
define('advanced:views/report/modals/select-records', ['crm:views/document/modals/select-records'], function (Dep) {

    return Dep.extend({

        categoryScope: 'ReportCategory',
        categoryField: 'category',
        categoryFilterType: 'inCategory',

        createButton: false,
    });
});
