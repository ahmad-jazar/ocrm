
define('advanced:views/bpmn-flowchart/record/detail', 'views/record/detail', function (Dep) {

    return Dep.extend({

        saveAndContinueEditingAction: true,

        setup: function () {
            Dep.prototype.setup.call(this);

            if (!this.model.isNew()) {
                this.setFieldReadOnly('targetType');
            }
        },

    });
});