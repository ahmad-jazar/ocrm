
define('advanced:views/bpmn-user-task/modals/resolve', ['views/modal'], function (Dep) {

    return Dep.extend({

        template: 'advanced:bpmn-user-task/modals/resolve',

        backdrop: true,

        setup: function () {
            this.header = this.translate('BpmnUserTask', 'scopeNames') + ' <span class="chevron-right"></span> ' +
                Handlebars.Utils.escapeExpression(this.model.get('name'));

            this.originalModel = this.model;
            this.model = this.model.clone();

            this.createView('record', 'advanced:views/bpmn-user-task/record/resolve', {
                model: this.model,
                el: this.getSelector() + ' .record'
            });

            this.buttonList = [
                {
                    name: 'resolve',
                    text: this.translate('Resolve', 'labels', 'BpmnUserTask'),
                    style: 'danger',
                    disabled: true
                },
                {
                    name: 'cancel',
                    label: 'Cancel'
                }
            ];

            this.listenTo(this.model, 'change:resolution', (model, value) => {
                if (value) {
                    this.enableButton('resolve');
                } else {
                    this.disableButton('resolve');
                }
            });
        },

        actionResolve: function () {
            this.disableButton('resolve');

            this.model.save().then(() => {
                this.originalModel.set('resolution', this.model.get('resolution'));
                this.originalModel.set('resolutionNote', this.model.get('resolutionNote'));
                this.originalModel.set('isResolved', true);
                this.originalModel.trigger('sync');

                Espo.Ui.success(this.translate('Done'));

                this.close();
            });
        },
    });
});
