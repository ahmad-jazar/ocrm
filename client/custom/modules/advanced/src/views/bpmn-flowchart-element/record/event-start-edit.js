
define('advanced:views/bpmn-flowchart-element/record/event-start-edit',
['advanced:views/bpmn-flowchart-element/record/edit'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (this.model.isInSubProcess) {
                this.showField('isInterrupting');
            } else {
                this.hideField('isInterrupting');
            }
        },
    });
});
