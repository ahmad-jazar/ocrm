
define('advanced:views/report/record/list', ['views/record/list'], function (Dep) {

    return Dep.extend({

        quickEditDisabled: true,

        mergeAction: false,

        massActionList: ['remove', 'massUpdate', 'export'],

        rowActionsView: 'advanced:views/report/record/row-actions/default',

        massPrintPdfDisabled: true,

        actionShow: function (data) {
            if (!data.id) {
                return;
            }

            let model = this.collection.get(data.id);

            if (!model) {
                return;
            }

            this.createView('resultModal', 'advanced:views/report/modals/result', {
                model: model
            }, (view) => {
                view.render();

                this.listenToOnce(view, 'navigate-to-detail', (model) => {
                    let options = {
                        id: model.id,
                        model: model,
                        rootUrl: this.getRouter().getCurrentUrl(),
                    };

                    this.getRouter().navigate('#Report/view/' + model.id, {trigger: false});
                    this.getRouter().dispatch('Report', 'view', options);

                    view.close();
                });
            });
        },
    });
});
