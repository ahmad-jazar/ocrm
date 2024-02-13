
define('advanced:views/bpmn-flowchart-element/record/task-user-edit',
[
    'advanced:views/bpmn-flowchart-element/record/edit',
    'advanced:views/bpmn-flowchart-element/record/task-user-detail'
],
function (Dep, Detail) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            Detail.prototype.controlFieldsVisibility.call(this);

            this.listenTo(this.model, 'change', () => {
                if (this.model.hasChanged('assignmentType')) {
                    Detail.prototype.controlFieldsVisibility.call(this);
                }
            });
        },
    });
});
