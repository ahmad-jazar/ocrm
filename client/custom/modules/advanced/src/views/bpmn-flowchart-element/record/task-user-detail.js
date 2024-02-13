
define('advanced:views/bpmn-flowchart-element/record/task-user-detail',
['advanced:views/bpmn-flowchart-element/record/detail'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.controlFieldsVisibility();
        },

        controlFieldsVisibility: function () {
            this.hideField('targetUser');
            this.hideField('targetUser');
            this.hideField('targetUserPosition');

            this.setFieldNotRequired('targetUser');
            this.setFieldNotRequired('targetTeam');

            if (this.model.get('assignmentType') === 'specifiedUser') {
                this.showPanel('assignmentRule');
                this.showField('targetUser');
                this.setFieldRequired('targetUser');
            } else if ((this.model.get('assignmentType') || '').indexOf('rule:') === 0) {
                this.showPanel('assignmentRule');
                this.showField('targetTeam');
                this.showField('targetUserPosition');
                this.setFieldRequired('targetTeam');
            }
        },
    });
});
