export const getters = {

	// UID: () =>
 //        (
 //            document.head.querySelector('meta[name="user-id"]')
 //            && document.head.querySelector('meta[name="user-id"]').content
 //        )
 //            ? document.head.querySelector('meta[name="user-id"]').content
 //            : null,


    /**
     * Used to calculate a percentage of completion in the KYC flow
     */
    percentageComplete: ({ completedRoutes=[] }) => {
        const percentage = completedRoutes.length/5*100;
        return (percentage > 100) ? '100%' : `${percentage}%`;
    }


};
