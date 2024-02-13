
define('advanced:views/bpmn-flowchart-element/record/event-start-conditional-edit',
['advanced:views/bpmn-flowchart-element/record/event-start-edit'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (this.model.isInSubProcess) {
                this.hideField('triggerType');
            } else {
                this.showField('triggerType');
            }
        },
    });
});
