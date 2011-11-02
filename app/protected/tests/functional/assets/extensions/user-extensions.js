/**
 * storeValue, storeText, storeAttribute and store actions now 
 * have 'global' equivalents.
 * Use storeValueGlobal, storeTextGlobal, storeAttributeGlobal or storeGlobal
 * will store the variable globally, making it available it subsequent tests.
 *
 * See the Reference.html for storeValue, storeText, storeAttribute and store
 * for the arguments you should send to the new Global functions.
 *
 * example of use
 * in testA.html:
 * +------------------+----------------------+----------------------+
 * |storeGlobal       | http://localhost/    | baseURL              |
 * +------------------+----------------------+----------------------+
 * 
 * in textB.html (executed after testA.html):
 * +------------------+-----------------------+--+
 * |open              | ${baseURL}Main.jsp    |  |
 * +------------------+-----------------------+--+
 *
 * Note: Selenium.prototype.replaceVariables from selenium-api.js has been replaced
 *       here to make it use global variables if no local variable is found.
 *       This might cause issues if you upgraded Selenium in the future and this function 
 *       has been changed.
 *
 * @author Guillaume Boudreau
 */
 
globalStoredVars = new Object();

/*
 * Globally store the value of a form input in a variable
 */
Selenium.prototype.doStoreValueGlobal = function(target, varName) 
{
    if (!varName) {
        // Backward compatibility mode: read the ENTIRE text of the page
        // and stores it in a variable with the name of the target
        value = this.page().bodyText();
        globalStoredVars[target] = value;
        return;
    }
    var element = this.page().findElement(target);
    globalStoredVars[varName] = getInputValue(element);
};

/*
 * Globally store the text of an element in a variable
 */
Selenium.prototype.doStoreTextGlobal = function(target, varName) 
{
    var element = this.page().findElement(target);
    globalStoredVars[varName] = getText(element);
};

/*
 * Globally store the value of an element attribute in a variable
 */
Selenium.prototype.doStoreAttributeGlobal = function(target, varName) 
{
    globalStoredVars[varName] = this.page().findAttribute(target);
};

/*
 * Globally store the result of a literal value
 */
Selenium.prototype.doStoreGlobal = function(value, varName) 
{
    globalStoredVars[varName] = value;
};

/*
 * Search through str and replace all variable references ${varName} with their
 * value in storedVars (or globalStoredVars).
 */
Selenium.prototype.replaceVariables = function(str) 
{
    var stringResult = str;

    // Find all of the matching variable references
    var match = stringResult.match(/\$\{\w+\}/g);
    if (!match) 
    {
        return stringResult;
    }

    // For each match, lookup the variable value, and replace if found
    for (var i = 0; match && i < match.length; i++) 
    {
        var variable = match[i]; // The replacement variable, with ${}
        var name = variable.substring(2, variable.length - 1); // The replacement variable without ${}
        var replacement = storedVars[name];
        if (replacement != undefined) 
        {
            stringResult = stringResult.replace(variable, replacement);
        }
        var replacement = globalStoredVars[name];
        if (replacement != undefined) 
        {
            stringResult = stringResult.replace(variable, replacement);
        }
    }
    return stringResult;
};

/*
 * The below prototype combines the focus, select and click commands of selinium
 * so as to support different browser.
 */
Selenium.prototype.doFocusSelectAndClick = function(selectLocator, optionLocator) 
{

    //Focus on a Combox element.
    this.doFocus(selectLocator);

    //Select an option from a Combobox element.
    this.doSelect(selectLocator, optionLocator);

    //Click the Combobox element.
    this.doClick(selectLocator);
};

/*
 * The below prototype combines the type and typeKeys commands of selinium
 * so as to support different browser.
 */
Selenium.prototype.doTypeAndTypeKeys = function(locator, value) 
{
    var typeValue = (navigator.appName == "Microsoft Internet Explorer" || navigator.userAgent.toLowerCase().indexOf('chrome') > -1) ? value : "";

    //Type a particulat value in the element.
    this.doType(locator, typeValue);

    //Type a particulat value in the element, so as to support the auto-complete functionality.
    this.doTypeKeys(locator, value);
};

/*
 * The below prototype compares the text of the element locator and the
 * corresponding text provided.
 */
Selenium.prototype.doCompareText = function(locator, value)
{
    //Fetch the locator value and also filter the content based on the regex Object.    
    var regex = (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) ? new RegExp("\\n", 'gi') : "\\n/gi";
    var locatorValue = this.getText(locator).replace(regex,"");

    //Asset the the values match.
    Assert.matches(locatorValue, value);
};