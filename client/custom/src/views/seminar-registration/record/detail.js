define('custom:views/seminar-registration/record/detail', ['views/record/detail'], function (Dep) {
    return Dep.extend({
        bottomView :'custom:views/seminar-registration/record/detail-bottom'
    });
});
