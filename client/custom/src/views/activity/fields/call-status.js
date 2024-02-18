define('custom:views/activity/fields/call-status', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        fieldValueMap: {
            Bad: 'Email instead',
            BadEmail: 'None',
            Busy: 'Busy/No Answer',
            LeftAMessage: 'Left a Message',
            AnsweringMachineNoAns: 'BusyNoAnswer',
        },

        fieldValueMapDes: {
            'CallActionCompleted': {
                'Introduction Complete': 'There is date field in seminar reg. which pick current date and time of the PC',
            },
            Bad: {
                'Email instead': 'when we select Call Status Bad, Automatically call Result comes Email Instead',
            },
            BadEmail: {
                'None': 'when we select Call Status Bad # , Automatically call Result comes None',
            },
            Busy: {
                'Busy/No Answer': 'when we select Call Status Busy, Automatically call Result comes Busy / no Answer',
            },
            LeftAMessage: {
                'Left a Message': 'when we select Call Status left a message , Automatically call Result comes left a message and also change Seminar reg. status Left a Message',
            },
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'change:' + this.name, () => {
                this.model.set('callResult', this.fieldValueMap[this.model.get(this.name)]);

            });

            this.listenTo(this.model, 'change:callResult', () => {

                this.model.set('description', '');
                if (this.fieldValueMapDes[this.model.get(this.name)]) {
                    this.model.set('description', this.fieldValueMapDes[this.model.get(this.name)][this.model.get('callResult')]);
                }
            });
        },
    });
});
