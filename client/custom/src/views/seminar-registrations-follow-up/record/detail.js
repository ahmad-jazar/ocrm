define('custom:views/seminar-registrations-follow-up/record/detail', ['views/record/detail'], function (Dep) {
    return Dep.extend({

        mapData: {
            'CallActionCompleted': {
                'none': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                },
                'Attendance Verified': {
                    registrationStatus: 'Attendance Verified',
                    contactStatus: 'none',
                },
                'Left a Message': {
                    registrationStatus: 'Left a Message',
                    contactStatus: 'none',
                },
                'Cancelled-Prospect': {
                    registrationStatus: 'Cancelled',
                    contactStatus: 'Prospect',
                },
                'Cancelled- No Interest': {
                    registrationStatus: 'Cancelled',
                    contactStatus: 'No Interest',
                },
                'Reschedule': {
                    registrationStatus: 'Reschedule',
                    contactStatus: 'none',
                },
                'Create New Sem Reg': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                },
                'Email instead': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                },
                'Busy/No Answer': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                },
                'No Show Called': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                },
                'Introduction Complete': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                }
            },
            'Bad': {
                'Email instead': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                },
            },
            'BadEmail': {
                'none': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                },
            },
            'Busy': {
                'Busy/No Answer': {
                    registrationStatus: 'none',
                    contactStatus: 'none',
                },
            },
            'Left a Message': {
                'Busy/No Answer': {
                    registrationStatus: 'Left a Message',
                    contactStatus: 'none',
                },
            }
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.layoutName = 'SeminarRegistrationsFollowUpDetail';
            this.setFieldReadOnly('seminar');
            this.setFieldReadOnly('contact');


            this.listenTo(this.model, 'after:relate', (model) => {

                this.actionEdit();
                this.model.set('contactStatus', this.mapData[model.get('callStatus')][model.get('callResult')].contactStatus);
                this.model.set('registrationStatus', this.mapData[model.get('callStatus')][model.get('callResult')].registrationStatus);
            });
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
