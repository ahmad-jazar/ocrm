
define('advanced:views/report-filter/list', ['views/list'], function (Dep) {

    return Dep.extend({

        actionRebuildFilters: function () {
            Espo.Ui.notify(' ... ');

            Espo.Ajax.postRequest('ReportFilter/action/rebuild', {}).then(() => {
                Espo.Ui.success(this.translate('Done'));
            });
        },
    });
});
