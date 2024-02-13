
define('advanced:views/report/record/row-actions/default', ['views/record/row-actions/edit-and-remove'], function (Dep) {

    return Dep.extend({

        getActionList: function () {
            var actionList = Dep.prototype.getActionList.call(this);

            actionList.unshift({
                link: '#Report/show/' + this.model.id,
                label: 'Show',
                action: 'show',
                data: {
                    id: this.model.id,
                },
            });

            return actionList;
        },
    });
});
