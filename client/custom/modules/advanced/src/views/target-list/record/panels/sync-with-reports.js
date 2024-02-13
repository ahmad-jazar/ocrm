
define('advanced:views/target-list/record/panels/sync-with-reports', ['views/record/panels/side'], function (Dep) {

    return Dep.extend({

        fieldList: [
            'syncWithReportsEnabled',
            'syncWithReports',
            'syncWithReportsUnlink',
        ],

        actionList: [
            {
                "name": "syncWithReport",
                "label": "Sync Now",
                "acl": "edit",
                "action": "syncWithReports",
            }
        ],

        setup: function () {
            Dep.prototype.setup.call(this);
        },

        actionSyncWithReports: function () {
            if (!this.model.get('syncWithReportsEnabled')) {
                return;
            }

            Espo.Ui.notify(' ... ');

            Espo.Ajax
                .postRequest('Report/action/syncTargetListWithReports', {targetListId: this.model.id})
                .then(() => {
                    Espo.Ui.success(this.translate('Done'));

                    this.model.trigger('after:relate');
                })
        },
    });
});
