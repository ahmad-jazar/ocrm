
define('advanced:views/report-panel/list', ['views/list'], function (Dep) {

    return Dep.extend({

        actionRebuildPanels: function () {
            Espo.Ui.notify(' ... ');

            Espo.Ajax.postRequest('ReportPanel/action/rebuild', {}).then(() => {
                Espo.Ui.success(this.translate('Done'));
            });
        },
    });
});
