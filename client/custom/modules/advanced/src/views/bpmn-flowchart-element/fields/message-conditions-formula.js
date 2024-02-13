
define('advanced:views/bpmn-flowchart-element/fields/message-conditions-formula', 'views/fields/formula', function (Dep) {

    return Dep.extend({

        height: 30,

        setup: function () {
            this.params.targetEntityType = 'Email';
            Dep.prototype.setup.call(this);
        },

    });
});