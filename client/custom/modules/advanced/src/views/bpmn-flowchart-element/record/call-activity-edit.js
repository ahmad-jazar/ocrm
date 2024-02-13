
define('advanced:views/bpmn-flowchart-element/record/call-activity-edit',
['advanced:views/bpmn-flowchart-element/record/edit', 'advanced:views/bpmn-flowchart-element/record/task-user-detail'],
function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'change:target', (m, v, o) => {
                if (o.ui) {
                    m.set({
                        flowchartId: null,
                        flowchartName: null,
                    });
                }
            });
        },
    });
});
