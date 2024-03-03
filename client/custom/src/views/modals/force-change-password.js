define('custom:views/modals/force-change-password', ['views/modals/change-password'], function (Dep) {
    return Dep.extend({

        noCloseButton: true,

        escapeDisabled: true,

        setup: function () {
            Dep.prototype.setup.call(this);

            this.buttonList = [
                {
                    name: 'change',
                    label: 'Change',
                    style: 'primary',
                },
                {
                    name: 'logout',
                    label: 'Logout',
                    style: 'danger',
                }
            ];
        },

        actionLogout() {
            this.getRouter().logout();
        }
    });
});