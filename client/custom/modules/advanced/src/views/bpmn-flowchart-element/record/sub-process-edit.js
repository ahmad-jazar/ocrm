
define('advanced:views/bpmn-flowchart-element/record/sub-process-edit', [
    'advanced:views/bpmn-flowchart-element/record/edit',
    'advanced:bpmn-element-helper',
], function (Dep, Helper) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);
            this.hideField('targetType');
            this.hideField('flowchartVisualization');
            this.hidePanel('flowchartVisualization');

            this.controlTargetTypeField();

            this.bpmnHelper = new Helper(this.getHelper(), this.model);
        },

        controlTargetTypeField: function () {
            var dataList = this.model.get('dataList') || [];

            if (dataList.length === 0) {
                this.setFieldNotReadOnly('target');
            } else {
                this.setFieldReadOnly('target');
            }
        },
    });
});
