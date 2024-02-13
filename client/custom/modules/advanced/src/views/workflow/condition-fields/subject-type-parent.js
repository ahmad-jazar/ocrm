
define('advanced:views/workflow/condition-fields/subject-type-parent',
    'advanced:views/workflow/condition-fields/subject-type', function (Dep) {

    return Dep.extend({

        list: ['value', 'field', 'typeOf'],

    });
});
