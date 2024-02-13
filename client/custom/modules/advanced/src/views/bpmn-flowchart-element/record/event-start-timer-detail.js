
define('advanced:views/bpmn-flowchart-element/record/event-start-timer-detail',
['advanced:views/bpmn-flowchart-element/record/event-start-detail'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            if (this.model.isInSubProcess) {
                this.showField('timer');
                this.hideField('targetReport');
                this.hideField('scheduling');
            } else {
                this.hideField('timer');
                this.showField('targetReport');
                this.showField('scheduling');
            }
        },
    });
});
