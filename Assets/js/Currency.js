function newPriceFactor(price, factor) {
    var newPrice = "";
    if (price != "") {
        try {
            return new Big(price).div(currencyFactor).times(factor).round(2, 1).toString();
        } catch(e) {
            console.log(e);
        }
    }
    return "";
}

function changeCurrency(id, factor) {
    currentCurrency = id;
    currencyFactor = factor;
}

function toDefaultCurrency(amount, factor) {
    return amount.toString().trim() == "" ? new Big(0) : new Big(amount).div(factor).times(defaultCurrencyFactor).round(2, 1).toString();
}

function fromDefaultCurrency(amount, factor) {
    return amount.toString().trim() == "" ? new Big(0) : new Big(amount).div(defaultCurrencyFactor).times(factor).round(2, 1).toString();
}

function fromBasePrice(basePrice) {
    if (basePrice != "") {
        try {
            return new Big(basePrice).times(currencyFactor).round(2, 1).toString();
        } catch(e) {
            console.log(e);
        }
    }
    return "";

}

function toBasePrice(price) {
    if (price != "") {
        try {
            return new Big(price).div(currencyFactor).round(2, 1).toString();
        } catch(e) {
            console.log(e);
        }
    }
    return "";
}

