
define('advanced:views/workflow/fields/help-text', ['views/fields/text'], function (Dep) {

    return Dep.extend({

        createDisabled: true,

        detailTemplate: 'advanced:workflow/fields/help-text/detail'
    });
});
