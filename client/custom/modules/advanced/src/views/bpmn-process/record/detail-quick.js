
define('advanced:views/bpmn-process/record/detail-quick', ['views/record/detail-small'], function (Dep) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);
            this.hideField('startElementId');
        },
    });
});
