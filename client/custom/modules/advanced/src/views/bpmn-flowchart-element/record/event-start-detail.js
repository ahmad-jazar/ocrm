
define('advanced:views/bpmn-flowchart-element/record/event-start-detail',
['advanced:views/bpmn-flowchart-element/record/detail'], function (Dep) {

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
