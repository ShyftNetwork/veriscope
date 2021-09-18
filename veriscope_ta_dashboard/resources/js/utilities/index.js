/**
 * Used to detect fields that are required AND NOT valid
 * @param {Object} fields - Fields object provided from vee-validate
 * @returns {int} Total count of errors
 */
const hasInvalidRequiredFields = fields =>
    Object.keys(fields).filter(
        key => fields[key].required && !fields[key].valid
    ).length;

/**
 * Used to detect fields that are NOT required AND dirty AND NOT valid
 * @param {Object} fields - Fields object provided from vee-validate
 * @returns {int} Total count of errors
 */
const hasInvalidDirtyNotRequiredFields = fields =>
    Object.keys(fields).filter(
        key => !fields[key].required && fields[key].dirty && !fields[key].valid
    ).length;

/**
 * Used to determine validity of vee-validate field group
 * @param {Object} fields - Fields object provided from vee-validate
 * @returns {Boolean} - True: Form is NOT valid / False: Form IS Valid
 */
export const formNotValid = fields => {
    if(
        !hasInvalidRequiredFields(fields)
        && !hasInvalidDirtyNotRequiredFields(fields)
    ) {
        return false;
    }
    return true;
};

/**
 * Used by Good Table to format the data of each column.
 * This is used be Good Table as slots for proper HTML formatting.
 * @param {*} val - Value of column in row
 * @param {*} type - Type of formatter to use
 */
export const tableFormatter = function(val,type) {
    switch(type) {
        case 'credits':
            return getCreditsTableCellData(val);
        case 'numberFormat':
            return numberWithCommas(val);
        case 'currencyFormat':
            return getNumberWithCurrency(val);
    }
}

/**
 * Used to get bonus w/icons for tabular data
 * @param {Object} - A complete transaction object
 * @returns {String} - A string of HTML for UI display
 */
export const getCreditsTableCellData = function(credits=null) {
    if(!credits) return '<span class="flex items-center">-</span>';
    return `<span class="flex items-center"><img src="/images/shyft-cred-icon.svg" alt="Shyft icon" class="mr-2">${numberWithCommas(credits)}</span>`;
 };

export const getNumberWithCurrency = function(val) {
    return `$${numberWithCommas(val)}`;
}

/**
 * Used for making comma separated integers
 * Can accept decimals
 * @param {*} int - Can actually be a string OR int
 * @param {*} interval
 */
export const numberWithCommas = function(int=null, interval=3) {
    if(!int) return ''; // Return nothing if int non existent
    if(typeof interval !== 'number') return int; // Return original if interval not a number

    const intAsString = String(int); // Always to string for splits
    const intWithoutCommas = intAsString.replace(/,/g,''); // Remove commas, just in case
    const strToArray = intWithoutCommas.split('.'); // In case of decimals
    const partOneToReversedArray = strToArray[0].split('').reverse(); // String->Array->Reverse
    let partOneWithCommas = []; // Store for new array
    let incrementor = 0; // Initialize incrementor

    // Insert commas at interval
    partOneToReversedArray.forEach((v,i) => {
        incrementor++;
        partOneWithCommas.push(v);
        if(incrementor % interval === 0 && i != partOneToReversedArray.length-1) {
            partOneWithCommas.push(',');
        }
    });

    // Array back to reverse back to string
    const partOneAsString = partOneWithCommas.reverse().join('');

    // Splice back in with commas
    strToArray.splice(0,1,partOneAsString)

    // Join back if decimals
    return strToArray.join('.');
}
