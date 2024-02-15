define('custom:views/seminar-registration/fields/seminar', ['views/fields/link'], function (Dep) {
    return Dep.extend({
        selectPrimaryFilterName: 'upcoming',
    });
});
