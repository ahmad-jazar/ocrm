define('custom:views/seminar-registrations-follow-up/panels/contact-info', ['views/record/panels/side'], function (Dep) {
    return Dep.extend({
        setupFields: function () {

            if (window.location.href.includes('SeminarRegistrationsFollowUp')) {
                this.fieldList = ["contact", "salutationName", "firstName", "lastName", "phoneNumber", "emailAddress","contactOwner"];
            } else {
                Dep.prototype.setupFields.call(this);
            }
        },
    });
});
