# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/).

## [Unreleased]

## [1.2.3] - 2020-11-25
### Added
- Add setting to allow admin editing, add check in profile output #50

### Fixed
- Fix change password & translation improvement #58
- Fix checkbox value #61

## [1.2.2] - 2020-05-19
### Added
- Implement Login Widget #51
- Translation ready #51
- Freemius Integration #55

### Fixed
- Fix Nonce Security Issue #51

## [1.2.0] - 2020-04-04
### Added
- Implement Add/Edit/Remove/Clone Role #36 #37 #39 #38 
- Implement Assign Capabilities to Roles #44 
- Implement Unassign Capabilities of Role #45 
- Implement Shortcode Role Assignment #35

### Fixed
- Fix a warning on new post screen #41
- Fix wpfep_register_scripts disables JS when filtering $style_output to false e0d2531

## [1.1.0] - 2020-01-22
### Added
- Admin should have the ability to create new users from frontend & select roles 0221779
- Email verification for user registration b85701b
- Admin can manually approve users 4aebd4c
- Add Taxonomy/Term in Fields #25

### Changed
- Improve template design

## [1.0.0] - 2019-10-15
### Added
- Captcha #14
- Shortcode in TinyMCE #13
- Register & Login Page #12
- Setup pages by default #11

### Changed
- Return to the tab we’re changing #6

### Fixed
- Warning: Illegal string offset 'id' in wpfepfunctions.php:161 #9
- Checkbox field can not be unchecked #5

## [0.2.2] - 2016-09-13
### Security
- Security: Fix privilege escalation and XSS vulnerabilities.

## [0.2.1] - 2016-07-29
### Added
- Added ability to display form via shortcode.

## [0.2] - 2014-12-06
### Security
- Include a nonce in the frontend editing form for security.

## [0.1] - 2014-12-06
### Added
- Initial launch.

[Unreleased]: https://github.com/glowlogix/wp-frontend-profile/compare/1.2.3...HEAD
[1.2.3]: https://github.com/glowlogix/wp-frontend-profile/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/glowlogix/wp-frontend-profile/compare/1.2.0...1.2.2
[1.2.0]: https://github.com/glowlogix/wp-frontend-profile/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/glowlogix/wp-frontend-profile/compare/v1.0.0...1.1.0
[1.0.0]: https://github.com/glowlogix/wp-frontend-profile/compare/0.2.2...v1.0.0
[0.2.2]: https://github.com/glowlogix/wp-frontend-profile/compare/v0.1...0.2.2
[0.0.1]: https://github.com/glowlogix/wp-frontend-profile/releases/tag/v0.1
