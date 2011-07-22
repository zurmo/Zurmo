This directory should only contain TestSuite.php and a few misc tests
that are unrelated to the framework or application.

For example PhpTest is just to confirm certain behaviors of Php,
RedBeanTest likewise for RedBean.

All the RedBean*Test, SqlDataProviderTest, PorletTest, should not
be here, but are here temporarily.

The reason is that 'all' of the tests were here for a time, before being
moved into the tests directories under framework and the modules,
but because they were all here they ended up with depending on
things in ways that they shouldn't.

They are therefore here only until they are fixed to not depend on things
they shouldn't, then they will be moved to their correct locations.
