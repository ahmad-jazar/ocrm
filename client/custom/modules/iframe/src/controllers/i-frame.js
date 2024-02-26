define('iframe:controllers/i-frame', ['controller'], function (Dep) {
    return Dep.extend({

        defaultAction: 'iframe',

        actionIframe() {
            this.main('iframe:views/iframe', {}, view => {
                view.name = this.name;
                view.render();
            });
        }
    });
});
