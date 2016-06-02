#SilverStripe Domain Specific Member Profile Pages Module
This module is a simple extension to the _SilverStripe Member Profile Pages Module_. It adds the ability to limit user
registrations to specific domains.

##Example use case
Let's say you only want employees of a specific organisation organisation to be able to create a user profile, you can
limit registration to users with a _@example.com_ email address. Or let's say you only want New Zealand students or their
teachers to have an account, you can restrict registrations to users with a _*.school.nz_ account.

You can define a single allowed domain or many. You can also explictely disallow domains.

##Requirements

* [SilverStripe Member Profile Pages Module (1.1)](https://github.com/silverstripe-australia/silverstripe-memberprofiles)

_This module is not a fork of the Member Profile Pages Module. It's an extension._

##Installation Instructions
```
composer require firebrandhq/domain-specific-memberprofiles
```

Make sure to run a `dev/build` after installing the module.

##Usage Overview
1. Create your profile page like you normally would.
2. Under the *Profile > Fields* tab, edit the Email profile field. At the bottom of the page, under the _Vlaidation_ header, there should now be a _Domain Validation_ subsection.
3. In the apropriate textarea field, provide a list of allowed and/or disallowed domains.
  * If you leave a field blank, it will be ignored.
  * If you leave both fields blank, there's not going to be any domain validation on the email.
  * You can use wildcards to whitelist or blacklist subdomains. (e.g.: `*.example.com`)
4. If you want the error message to include the list of allowed or disallowed domains, check _Show Domains On Error_.
5. Save your email profile field.

Although not strictly required, you probably want to enable _Email Validation_ for you profile page otherwise users can
pretend to have access to a valid email.
