# itop-sso-synology

iTop extension that adds **Synology SSO Server** (DSM native package) as an OpenID Connect identity provider.

## What it does

- Registers a new iTop class, **`SynologySSOOauth2Client`**, a subclass of `Oauth2Client` that lets administrators configure a Synology SSO Server from the iTop UI (**Administration → OAuth 2.0 Clients**).
- Ships a **Hybridauth provider** (`Hybridauth\Provider\SynologySSO`) that knows how to talk to Synology SSO (`/webman/sso/SSOOauth.cgi`, ...), which are **not OIDC-standard paths** and therefore not handled by any upstream Hybridauth provider.
- Can be used both:
  - by **`combodo-oauth2-client`** for server-to-server OAuth flows (iTop calling DSM APIs on behalf of a user), and
  - by **`combodo-hybridauth`** to let end users **log into iTop with their Synology account** (single sign-on).
- Optional **group-based access filter**: only authenticated users who belong to a specific DSM group are allowed in.
- Translations: English, Spanish, French.

## Requirements

- iTop 3.2 or later.
- [`combodo-oauth2-client`](https://www.itophub.io/wiki/page?id=extensions%3Acombodo-oauth2-client) extension installed (provides the base `Oauth2Client` class).
- [`combodo-hybridauth`](https://www.itophub.io/wiki/page?id=extensions%3Acombodo-hybridauth) extension installed (**required only if you want user login**; not needed for pure S2S use).
- A running **Synology SSO Server** package on DSM (3.0.x or later) with the OIDC service enabled.

## Installation

### 1. Drop the module into iTop

Copy this repository into your iTop `extensions/` directory:

```bash
cp -r itop-sso-synology /path/to/itop/extensions/
```

### 2. Run the setup wizard

Open `https://<your-itop>/setup/` and run **"Upgrade an existing iTop instance"**. When the module list appears, tick **"Synology SSO OAuth 2.0 client"** and complete the wizard.

The setup will:

- create the SQL table `priv_oauth2_client_synologysso`,
- compile the `SynologySSOOauth2Client` class into the datamodel,
- load the bundled dictionaries,
- regenerate `env-production/autoload.php` to include this module's PHP autoloader.

> **Note for existing installations:** the setup wizard needs write access to `conf/production/config-itop.php`. If you see *"the configuration file already exists and cannot be overwritten"*, temporarily grant write permission (`chmod 664`) to that file, run the setup, and restore it to read-only afterwards.

### 3. Register the client in Synology SSO Server

In DSM, go to **SSO Server → Application → Add**:

| Field        | Value                                                                                                |
|--------------|------------------------------------------------------------------------------------------------------|
| Protocol     | OIDC                                                                                                 |
| Name         | `iTop` (or whatever you prefer)                                                                      |
| Redirect URI | depends on use case, see below                                                                       |

Redirect URIs:

- **For user login via `combodo-hybridauth`**:
  `https://<your-itop>/env-production/combodo-hybridauth/landing.php`
- **For S2S OAuth via `combodo-oauth2-client`**:
  `https://<your-itop>/env-production/combodo-oauth2-client/landing.php`

You can add both redirect URIs to the same SSO Server application if you need both modes.

After saving, copy the generated **Client ID** and **Client Secret**.

## Usage

### A) User login via `combodo-hybridauth`

Edit `conf/production/config-itop.php` and add a `SynologySSO` provider inside the `combodo-hybridauth → providers` array:

```php
'combodo-hybridauth' => array(
    // ... existing settings ...
    'providers' => array(
        // ... existing providers (Google, Facebook, ...) ...
        'SynologySSO' => array(
            'enabled' => true,
            'keys' => array(
                'id'     => 'YOUR-CLIENT-ID',
                'secret' => 'YOUR-CLIENT-SECRET',
            ),
            'url'   => 'https://sso.example.com', // base URL, no path
            'scope' => 'openid email groups',
            // Optional: restrict access to members of at least one of these DSM groups.
            'allowed_groups' => array('my_sso_group'),
            'synchronize_user'     => true,
            'synchronize_contact'  => true,
            'default_organization' => 'My Org',
            'default_profile'      => 'Portal User',
        ),
    ),
),
```

Enable the new login mode in the same file:

```php
'allowed_login_types' => 'form|external|basic|token|hybridauth-SynologySSO',
```

Flush the iTop cache and reload the web server:

```bash
rm -rf /path/to/itop/data/cache-production/*
# reload apache / php-fpm as appropriate for your setup
```

A **"Sign in with SynologySSO"** button should now appear on the iTop login page.

### B) S2S OAuth flow via `combodo-oauth2-client`

From iTop's **Administration → OAuth 2.0 Clients → New**, pick **"Synology SSO OAuth 2.0 client"** from the type dropdown. Fill in:

- Name: free text
- URL: `https://sso.example.com` (base only, no path)
- Client ID / Client Secret: from step 3
- Scope: `openid email groups`

Click **Create**. This client can then be used programmatically from your own iTop extensions to call Synology APIs on behalf of the user.

## Group-based access filter

The provider supports an optional `allowed_groups` array in its Hybridauth configuration. When present and non-empty, users whose `groups` claim does not intersect with that list will be rejected with an `Access denied` error.

This is the recommended way to restrict iTop access to a subset of DSM users (for instance, internal staff only, excluding external collaborators who only have access to shared folders).

To populate the `groups` claim, create a group in DSM (Control Panel → Group) and add the relevant users. Reference its name in `allowed_groups`.

## Troubleshooting

- **"Unknown Provider (name: SynologySSO / configured: Hybridauth\Provider\SynologySSO)"**
  The module's autoloader is not being loaded at iTop startup. Confirm that `env-production/autoload.php` contains a line like
  `MetaModel::IncludeModule(MODULESROOT.'/itop-sso-synology/src/autoload.php');`.
  If missing, re-run the iTop setup wizard to regenerate it.

- **Sign-in button redirects back to the login page**
  Check `log/error.log`. Typical causes: missing or incorrect `client_secret`, redirect URI mismatch, or incorrect `url` in the provider config.

- **"Access denied: user does not belong to any of the allowed groups"**
  The user did authenticate successfully against DSM but is not a member of any group listed in `allowed_groups`. Add them to the group in DSM.

- **Dropdown still shows the raw class name `SynologySSOOauth2Client`**
  The consolidated dictionaries in `env-production/dictionaries/` have not been regenerated. Re-run the setup wizard.

- **"Parse error: unexpected identifier 'version' in datamodel.*.xml on line 1"** during setup
  Some PHP configurations have `short_open_tag = On`, which causes `<?xml version="1.0"?>` to be interpreted as a PHP opening tag. Set `short_open_tag = Off` in your `php.ini` (the PHP 8.x default).

## Contributing

Contributions are welcome. Please:

1. Open an issue describing the change you want to make before sending a pull request.
2. Keep changes focused: one feature or fix per PR.
3. Preserve compatibility with iTop 3.2+ and keep the module's footprint minimal.

## License

MIT - see [LICENSE](LICENSE).

## Disclaimer

This project is not affiliated with, endorsed by, or sponsored by Synology Inc. or Combodo SAS. "Synology" is a trademark of Synology Inc. "iTop" is a trademark of Combodo SAS.
