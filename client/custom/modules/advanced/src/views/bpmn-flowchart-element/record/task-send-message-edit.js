
define('advanced:views/bpmn-flowchart-element/record/task-send-message-edit',
[
    'advanced:views/bpmn-flowchart-element/record/edit',
    'advanced:views/bpmn-flowchart-element/record/task-send-message-detail'
], function (Dep, Detail) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            Detail.prototype.controlFieldsVisibility.call(this);

            this.listenTo(this.model, 'change', () => {
                if (this.model.hasChanged('from') || this.model.hasChanged('to') || this.model.hasChanged('replyTo')) {
                    Detail.prototype.controlFieldsVisibility.call(this);
                }
            });
        },
    });
});
