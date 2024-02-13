
define('advanced:views/bpmn-flowchart/record/panels/flowchart', ['views/record/panels/bottom'], function (Dep) {

    return Dep.extend({

        template: 'advanced:bpmn-flowchart/record/panels/flowchart',

        setup: function () {
            Dep.prototype.setup.call(this);

            this.createView('flowchart', 'advanced:views/bpmn-flowchart/fields/flowchart', {
                model: this.model,
                el: this.options.el + ' .field[data-name="flowchart"]',
                defs: {
                    name: 'flowchart'
                },
                mode: this.mode,
                inlineEditDisabled: true,
                disabled: this.recordHelper.getFieldStateParam('flowchart', 'hidden'),
            });
        },

        getFieldViews: function () {
            var fields = {};
            fields.flowchart = this.getView('flowchart');

            return fields;
        }
    });
});
