define('custom:views/seminar-registrations-follow-up/record/detail', ['views/record/detail'], function (Dep) {
    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.layoutName = 'SeminarRegistrationsFollowUpDetail';
            this.setFieldReadOnly('seminar');
        },

        manageAccessEdit: function () {
            this.model.entityType = 'SeminarRegistrationsFollowUp';

            Dep.prototype.manageAccessEdit.call(this);
            this.model.entityType = 'SeminarRegistration';
        }
    });
});
