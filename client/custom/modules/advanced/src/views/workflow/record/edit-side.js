
define('advanced:views/workflow/record/edit-side', ['views/record/edit-side'], function (Dep) {

    return Dep.extend({

        panelList: [
            {
                name: 'default',
                label: false,
                view: 'advanced:views/workflow/record/panels/side'
            }
        ],
    });
});
