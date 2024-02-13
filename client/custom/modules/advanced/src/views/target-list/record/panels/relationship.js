
define('advanced:views/target-list/record/panels/relationship',
['crm:views/target-list/record/panels/relationship'], function (Dep) {

    return Dep.extend({

        actionPopulateFromReport: function (data) {
            let link = data.link;

            let filterName = 'list' + Espo.Utils.upperCaseFirst(link);

            Espo.Ui.notify(' ... ');

            this.createView('dialog', 'views/modals/select-records', {
                scope: 'Report',
                multiple: false,
                createButton: false,
                primaryFilterName: filterName,
            }, view => {
                view.render();

                Espo.Ui.notify(false);

                this.listenToOnce(view, 'select', select => {
                    Espo.Ajax
                        .postRequest('Report/action/populateTargetList', {
                            id: select.id,
                            targetListId: this.model.id,
                        })
                        .then(() => {
                            Espo.Ui.success(this.translate('Linked'));

                            this.collection.fetch();
                        });
                });
            });
        },
    });
});
