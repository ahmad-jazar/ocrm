define('custom:controllers/logout', ['controllers/record'], function (Dep) {
    return Dep.extend({

        defaultAction : 'logout',

        actionLogout() {
            this.getRouter().logout();
        }
    });
});
