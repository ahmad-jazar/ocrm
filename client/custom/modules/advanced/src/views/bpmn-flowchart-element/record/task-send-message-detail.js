
define('advanced:views/bpmn-flowchart-element/record/task-send-message-detail',
['advanced:views/bpmn-flowchart-element/record/detail'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.controlFieldsVisibility();
        },

        controlFieldsVisibility: function () {
            this.hideField('fromEmailAddress');
            this.hideField('toEmailAddress');
            this.hideField('replyToEmailAddress');
            this.hideField('toSpecifiedTeams');
            this.hideField('toSpecifiedUsers');
            this.hideField('toSpecifiedContacts');

            if (this.model.get('from') === 'specifiedEmailAddress') {
                this.showField('fromEmailAddress');
            }
            if (this.model.get('to') === 'specifiedEmailAddress') {
                this.showField('toEmailAddress');
            }
            if (this.model.get('replyTo') === 'specifiedEmailAddress') {
                this.showField('replyToEmailAddress');
            }
            if (this.model.get('to') === 'specifiedUsers') {
                this.showField('toSpecifiedUsers');
            }
            if (this.model.get('to') === 'specifiedTeams') {
                this.showField('toSpecifiedTeams');
            }
            if (this.model.get('to') === 'specifiedContacts') {
                this.showField('toSpecifiedContacts');
            }
        },
    });
});
