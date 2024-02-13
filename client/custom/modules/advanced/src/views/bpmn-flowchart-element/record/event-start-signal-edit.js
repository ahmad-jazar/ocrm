
define('advanced:views/bpmn-flowchart-element/record/event-start-signal-edit',
['advanced:views/bpmn-flowchart-element/record/event-start-edit'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (!this.model.isInSubProcess) {
                let options = this.getMetadata().get(['entityDefs', 'Workflow', 'fields', 'signalName', 'options']);
                options = Espo.Utils.clone(options);

                if (options.includes('@delete')) {
                    options.splice(options.indexOf('@delete'), 1);
                }

                this.setFieldOptionList('signal', options);
            }
        },
    });
});
