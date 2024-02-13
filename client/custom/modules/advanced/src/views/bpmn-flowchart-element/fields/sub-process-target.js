
define(
    'advanced:views/bpmn-flowchart-element/fields/sub-process-target',
    ['advanced:views/bpmn-flowchart-element/fields/call-activity-target', 'advanced:bpmn-element-helper'],
    function (Dep, Helper) {

    return Dep.extend({

        skipParent: true,

        setup: function () {
            Dep.prototype.setup.call(this);

            this.bpmnHelper = new Helper(this.getHelper(), this.model);
        },

        fetch: function () {
            var data = Dep.prototype.fetch.call(this);

            data.targetType = this.bpmnHelper.getEntityTypeFromTarget(data.target);

            return data;
        },
    });
});
