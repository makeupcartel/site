# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.0.0

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- #98 Convert "bitExpert" namespace to "BitExpert" to fix the Magento 2.2 XSD issue
- #99 Don't store AJAX requests as after login url
- #100 Apply fixes sugested by the EQP tool
- #101 Update docs to fix typos and reflect latest changes
- #102 Rename button "delete"

## 2.3.0

### Added

- #88 Migrated from using observers to hook into router chain
- #89 Enabled edition of default whitelist routes
- #92 Fixed syntax issue in layout xml, invalid block definition

### Deprecated

- Nothing.

### Removed

- #88 Usage of observers (LoginRequiredOnCustomerSessionInitObserver, LoginRequiredOnVisitorInitObserver) and event hooks (customer_session_init, visitor_init) have been removed

### Fixed

- #83 404 pages do not show when logged out
- #84 Disable registration isn't possible
- #91 Whitelist is Not works for Magento 2.2.0

## 2.2.0

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- #85 Incompatible with Magento 2.2

## 2.1.0

### Added

- #82 Fixed invalid position of tag resource in system.xml
- #78 Move backend configuration to customer > customer configuration section
- #77 Provides backwards compatibility by set strategy for existing rules to regex matcher instead of static
- #76 Static matcher now canonicalizes url and rule to omit differences of trailing slashes
- #60 Behavior Setting for Matcher

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- #81 Error in admin pages due to invalid XML
- #79 Invalid system.xml file
- #75 Static matching strategy could ignore ending slash
- #74 Upgrade to 2.1 RC2 from 2 breaks backward compatibility of rules
- #73 Force login top level system config tab is overkill
- #72 Fixes on class resolution

## 2.0.2

### Added

- #71 Moved events.xml to frontend/events.xml

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- #68 Activating the module also blocks the backend

## 2.0.1

### Added

- #67 Resolve redirect loop from login to customer dashboard

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- #66 Redirect loop

## 2.0.0

### Added

- Changed type namings in DI to match best practice.
- Respect configured login option behavior.
- Added own session handler.
- #63 Updated resource ACL
- #62 Move UpgradeSchema to InstallSchema
- #54 Added configuration to enabled or disabled the module
- #43 Added additional default rules for sitemap.xml and robots.txt
- #26 Added cache control to redirecting
- #24 Added configuration to set target url

### Deprecated

- Nothing.

### Removed

- Removed full qualification of namespace representation type name to match best practice.

### Fixed

- Refactored code structure.
- #64 ACL error when accessing Store Configuration
- #61 Setup install then upgrade fails
- #35 Redirection after logging in

## 1.3.1

### Added

-  Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Increased version number in module.xml

## 1.3.0

### Added

-  Unified support for Magento 2.0 and Magento 2.1

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.2.4

### Added

-  [#27](https://github.com/bitExpert/magento2-force-login/pull/27) Allow to edit default paths

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#22](https://github.com/bitExpert/magento2-force-login/issue/22) disable /customer/account/create

## 1.2.3

### Added

-  [#21](https://github.com/bitExpert/magento2-force-login/pull/21) Updated docs to match Magento2 marketplace regulations

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  Nothing.

## 1.2.2

### Added

-  [#15](https://github.com/bitExpert/magento2-force-login/pull/18) Fixed appliance of whitelist repository collection filter

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#14](https://github.com/bitExpert/magento2-force-login/issues/14) Whitelist entire website or store view

## 1.2.1

### Added

-  [#15](https://github.com/bitExpert/magento2-force-login/pull/15) Reduced the quotation of the whitelist entries to allow some regular expressions

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#14](https://github.com/bitExpert/magento2-force-login/issues/14) Whitelist entire website or store view

## 1.2.0

### Added

-  [#11](https://github.com/bitExpert/magento2-force-login/issues/11) Magento 2.1 compatibility added

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.1.7

### Added

-  [#27](https://github.com/bitExpert/magento2-force-login/pull/27) Allow to edit default paths

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#22](https://github.com/bitExpert/magento2-force-login/issue/22) disable /customer/account/create

## 1.1.6

### Added

-  [#20](https://github.com/bitExpert/magento2-force-login/pull/20) Updated docs to match Magento2 marketplace regulations

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  Nothing.

## 1.1.5

### Added

-  [#15](https://github.com/bitExpert/magento2-force-login/pull/18) Fixed appliance of whitelist repository collection filter

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#14](https://github.com/bitExpert/magento2-force-login/issues/14) Whitelist entire website or store view

## 1.1.4

### Added

-  [#15](https://github.com/bitExpert/magento2-force-login/pull/15) Reduced the quotation of the whitelist entries to allow some regular expressions

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#14](https://github.com/bitExpert/magento2-force-login/issues/14) Whitelist entire website or store view

## 1.1.3

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#7](https://github.com/bitExpert/magento2-force-login/pull/9) Fixed new whitlist entry saving fails

## 1.1.2

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#6](https://github.com/bitExpert/magento2-force-login/pull/6) Fix/missing customer session check

## 1.1.1

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#5](https://github.com/bitExpert/magento2-force-login/pull/5) Fixed issue with storefront prefix

## 1.1.0

### Added

-  [#4](https://github.com/bitExpert/magento2-force-login/pull/4) Added an administrative UI for configure the whitelist entries

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.0.1

### Added

-  [#1](https://github.com/bitExpert/magento2-force-login/pull/1) added links to ignore-urls

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

-  [#3](https://github.com/bitExpert/magento2-force-login/pull/3) Fix #2 issues with DI compilation
-  [#2](https://github.com/bitExpert/magento2-force-login/issues/2) DI Compilation fails due to dependency duplicate on older magento2 versions then 2.0.4

## 1.0.0

Initial release of the Force Login module for Magento2.