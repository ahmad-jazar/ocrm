
define('advanced:views/workflow/conditions/currency', ['advanced:views/workflow/conditions/float'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/base',

        getSubjectInputViewName: function (subjectType) {
            return 'advanced:views/workflow/condition-fields/subjects/text-input-currency';
        },
    });
});
