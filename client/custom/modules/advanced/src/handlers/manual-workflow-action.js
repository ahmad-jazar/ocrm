
define('advanced:handlers/manual-workflow-action', ['action-handler'], function (Dep) {

    return Dep.extend({

        actionRunWorkflow: function (data) {
            /** @type {module:views/detail.Class} */
            let view = this.view;
            let id = data.id;
            let name = 'runWorkflow_' + id;

            Espo.Ui
                .confirm(view.translate('confirmation', 'messages'), {
                    confirmText: view.translate('Yes', 'labels'),
                    cancelText: view.translate('No', 'labels'),
                })
                .then(() => {
                    /** @type {module:model.Class} */
                    let model = this.view.model;

                    view.disableMenuItem(name);

                    Espo.Ui.notify(' ... ');

                    Espo.Ajax
                        .postRequest('WorkflowManual/action/run', {
                            targetId: model.id,
                            id: id,
                        })
                        .then(() => {
                            model.fetch()
                                .then(() => {
                                    Espo.Ui.success(view.translate('Done'));

                                    view.enableMenuItem(name);
                                });
                        })
                        .catch(() => {
                            view.enableMenuItem(name);
                        });
                });
        },
    });
});
