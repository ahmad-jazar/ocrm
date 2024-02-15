define('custom:views/activity/fields/call-status', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        fieldValueMap: {
            Bad: 'Email instead',
            BadEmail: 'None',
            Busy: 'BusyNoAnswer',
            LeftAMessage: 'Left a Message',
            AnsweringMachineNoAns: 'BusyNoAnswer',
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'change:' + this.name, this.updateCallResult);
        },

        updateCallResult: function () {
            this.model.set('callResult', this.fieldValueMap[this.model.get(this.name)]);
        }
    });
});
