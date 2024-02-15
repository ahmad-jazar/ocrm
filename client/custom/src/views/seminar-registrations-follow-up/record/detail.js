define('custom:views/seminar-registrations-follow-up/record/detail', ['views/record/detail'], function (Dep) {
    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.layoutName = 'SeminarRegistrationsFollowUpDetail';
            this.setFieldReadOnly('seminar');
            this.setFieldReadOnly('contact');
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            const $recordGrid = this.$el.find('.record-grid');
            const $left = $recordGrid.find('.left');
            $recordGrid.css({
                'display': 'flex',
                'flex-direction': 'row-reverse',
            });
            $left.css({
                'width': '300%',
            });
        },

        manageAccessEdit: function () {
            this.model.entityType = 'SeminarRegistrationsFollowUp';

            Dep.prototype.manageAccessEdit.call(this);
            this.model.entityType = 'SeminarRegistration';
        }
    });
});
