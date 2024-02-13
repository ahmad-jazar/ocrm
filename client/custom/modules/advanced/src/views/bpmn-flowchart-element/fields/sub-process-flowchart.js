
define('advanced:views/bpmn-flowchart-element/fields/sub-process-flowchart', 'advanced:views/bpmn-flowchart/fields/flowchart', function (Dep) {

    return Dep.extend({

        dataAttribute: 'flowchartData',

        isSubProcess: true,

        setup: function () {
            Dep.prototype.setup.call(this);

            if (this.model.elementType === 'eventSubProcess') this.isEventSubProcess = true;
        },

    });
});
