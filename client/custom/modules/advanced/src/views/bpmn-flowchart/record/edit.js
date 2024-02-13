
define('advanced:views/bpmn-flowchart/record/edit', 'views/record/edit', function (Dep) {

    return Dep.extend({

        saveAndContinueEditingAction: true,

        setup: function () {
            Dep.prototype.setup.call(this);

            var dataEntityTypeMap = {};

            if (!this.model.isNew()) {
                this.setFieldReadOnly('targetType');
            } else {
                this.controlFlowchartField();
                this.listenTo(this.model, 'change:targetType', function (model) {
                    var previousEntityType = model.previous('targetType');
                    var targetType = model.get('targetType');
                    dataEntityTypeMap[previousEntityType] = this.model.get('data');
                    var data = dataEntityTypeMap[targetType] || {
                        list: []
                    };
                    this.controlFlowchartField();
                    this.model.set('data', data);
                }, this);
            }
        },

        controlFlowchartField: function () {
            var targetType = this.model.get('targetType');
            if (targetType) {
                this.showField('flowchart');
            } else {
                this.hideField('flowchart');
            }
        },

    });
});