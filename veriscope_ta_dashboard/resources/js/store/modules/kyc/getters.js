export const getters = {
    UID: () =>
        (
            document.head.querySelector('meta[name="user-id"]')
            && document.head.querySelector('meta[name="user-id"]').content
        )
            ? document.head.querySelector('meta[name="user-id"]').content
            : null,
    EDITID: () =>
        (
            document.head.querySelector('meta[name="edit-id"]')
            && document.head.querySelector('meta[name="edit-id"]').content
        )
            ? document.head.querySelector('meta[name="edit-id"]').content
            : null,

    /**
     * Used to determine mode that user is in when visiting KYC flow
     */
    isEditing: ({ completedRoutes=[] }) =>
        completedRoutes.includes('review'),
    /**
     * Used to Date format the dob value
     */
    userDobValue: ({ form={} }) =>
        (form.dob) ? new Date(form.dob) : null,
    /**
     * Used for a friendly Date of Birth display
     */
    dobDisplay: ({ form={} }) =>
        (form.dob) ?
            form.dob.toLocaleString('en-US', {
                timezone: 'UTC',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            }) : null,
    /**
     * Used to extract the Gender value from object
     */
    userGenderValue: ({ form={} }) =>
        (form.gender && form.gender.value) ? form.gender.value : null,
    /**
     * Used to extract the Status value from object
     */
    userStatusValue: ({ form={} }) =>
        (form.status && form.status.value) ? form.status.value : null,
    /**
     * Combine all name values into full name
     */
    fullName: ({ form={} }) => {
        const nameArray = [];
        if(form.first_name) {
            nameArray.push(form.first_name);
        }
        if(form.middle_name) {
            nameArray.push(form.middle_name);
        }
        if(form.last_name) {
            nameArray.push(form.last_name);
        }
        return nameArray.join(' ');
    }
};
