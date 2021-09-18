const Web3 = require('web3');

const dotenv = require('dotenv');
dotenv.config();

const testNetHttpUrl = process.env.HTTP;

let web3 = new Web3(new Web3.providers.HttpProvider(testNetHttpUrl));

// node -e 'require("./create-account").trustAnchorCreateAccount()'
module.exports.trustAnchorCreateAccount = function () {
    trustAnchorCreateAccount();
};

function trustAnchorCreateAccount() {

    var result = web3.eth.accounts.create();
    console.log('%j', result);
}