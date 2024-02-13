define('custom:views/seminar-registration/record/detail-bottom', ['views/record/detail-bottom'], function (Dep) {
    return Dep.extend({
        addRelationshipPanel: function (name, item) {
            if (name !== 'reschedule') {
                return
            }

            Dep.prototype.addRelationshipPanel.call(this, name, item);
        }
    });
});
