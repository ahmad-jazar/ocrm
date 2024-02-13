
define('advanced:workflow-helper', ['view'], function (Fake) {

    var WorkflowHelper = function (metadata) {
        this.metadata = metadata;
    };

    _.extend(WorkflowHelper.prototype, {

        getComplexFieldEntityType: function (field, entityType) {
            if (~field.indexOf('.') && !~field.indexOf('created:')) {
                var arr = field.split('.');
                var link = arr[0];

                return this.getMetadata().get(['entityDefs', entityType, 'links', link, 'entity']);
            }

            return entityType;
        },

        getComplexFieldLinkPart: function (field) {
            if (~field.indexOf('.')) {
                var arr = field.split('.');

                return arr[0];
            }

            return null;
        },

        getComplexFieldFieldPart: function (field) {
            if (~field.indexOf('.')) {
                var arr = field.split('.');

                return arr[1];
            }

            return field;
        },

        getComplexFieldForeignEntityType: function (field, entityType) {
            var targetLinkEntityType;

            if (~field.indexOf('.')) {
                var arr = field.split('.');
                var foreignField = arr[1];
                var link = arr[0];

                var foreignEntityType = this.getMetadata()
                    .get(['entityDefs', entityType, 'links', link, 'entity']);

                targetLinkEntityType = this.getMetadata()
                    .get(['entityDefs', foreignEntityType, 'links', foreignField, 'entity']);
            } else {
                targetLinkEntityType = this.getMetadata().get(['entityDefs', entityType, 'links', field, 'entity']);
            }

            return targetLinkEntityType;
        },


        getMetadata: function () {
            return this.metadata;
        },
    });

    return WorkflowHelper;
});
