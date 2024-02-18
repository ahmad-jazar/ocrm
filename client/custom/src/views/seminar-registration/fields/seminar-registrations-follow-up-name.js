define('custom:views/seminar-registration/fields/seminar-registrations-follow-up-name', ['views/fields/link'], function (Dep) {
    return Dep.extend({

        setup() {
            Dep.prototype.setup.call(this);

            this.foreignScope = 'SeminarRegistrationsFollowUp';
            this.model.set(this.nameName, this.model.get('name'));
            this.model.set(this.idName, this.model.get('id'));
        }
    });
});
