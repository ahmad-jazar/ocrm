define('custom:handlers/seminar-registration/tasks-create', ['handlers/create-related'], Dep => {

    return class extends Dep {
        /**
         * @param {module:model.Class} model
         */
        getAttributes(model) {
            const attributes = {};

            if (model.get('contactId')) {
                attributes['parentType'] = 'Contact';
                attributes['parentId'] = model.get('contactId');
                attributes['parentName'] = model.get('contactName');
            }

            return Promise.resolve(attributes);
        }
    }
});
