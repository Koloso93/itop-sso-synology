# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-15

### Added

- `SynologySSOOauth2Client` iTop class, a subclass of `Oauth2Client` (from
  `combodo-oauth2-client`) that lets administrators configure a Synology SSO
  Server from the iTop UI.
- `Hybridauth\Provider\SynologySSO` provider implementing the OAuth 2.0 /
  OpenID Connect flow against Synology's `/webman/sso/` endpoints.
- Optional `allowed_groups` configuration to restrict login to members of
  specific DSM groups.
- Self-contained autoloader that does not require modifications to other
  modules' classmaps.
- Dictionaries for English (EN US), Spanish (ES CR), and French (FR FR).
