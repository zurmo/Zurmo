In order to use the user-extensions.js with selinium through command line, you will have to use the following Syntax:

Java -jar selenium-server-standalone-2.7.0.jar -htmlSuite *firefox http://localhost/zurmo/app/ inputTestSuiteFilePath outputTestSuiteResult -userExtensions pathToTheUserExtensionJS

Default Location: app/protected/tests/functional/assets/extensions/user-extensions.js

Purpose: So as to enable the use of global variables in the entire TestSuite.