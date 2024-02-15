define('custom:views/activity/fields/call-status', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        fieldValueMap: {
            Bad: 'EmailInstead',
            BadEmail: 'None',
            Busy: 'BusyNoAnswer',
            LeftAMessage: 'LeftAMessage',
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