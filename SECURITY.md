SPARXSTAR Two-Factor Enforcement Plugin
---------------------------------------

Security Policy

**Component:** SPARXSTAR 2FA Enforcement MU-Plugin\
**Dependency:** Official WordPress Two-Factor Authentication Plugin\
**Applies To:** All WordPress installations where this plugin is active\
**Version:** 1.0

* * * * *

1\. Purpose
-----------

The SPARXSTAR Two-Factor Enforcement Plugin ensures that designated WordPress user roles are required to complete two-factor authentication before accessing authenticated sessions. The plugin provides enforcement only. It does not implement authentication, password handling, or token verification.

* * * * *

2\. Scope
---------

This policy applies to:

- All WordPress sites running the SPARXSTAR 2FA Enforcement Plugin\
- All users assigned to roles configured for mandatory 2FA\
- All WordPress authentication entry points (wp-login.php, XML-RPC, REST authentication)

* * * * *

3\. Enforcement Behavior
------------------------

### Role-Based Enforcement

Roles configured for enforcement cannot authenticate unless the Official WordPress Two-Factor Plugin reports that 2FA is active for the user.

### Enrollment Enforcement

If a user in an enforced role has not completed 2FA enrollment:

- Login is intercepted\
- The user is redirected to the Two-Factor enrollment screen\
- No authenticated session is granted

### Continuous Enforcement

If a user disables 2FA after enrollment:

- Subsequent login attempts are blocked\
- Access is denied until 2FA is re-enabled

### Multisite Behavior

In WordPress multisite environments:

- Enforcement rules apply network-wide\
- Super Administrators are always subject to enforcement\
- Site Administrators inherit network enforcement rules

* * * * *

4\. Data Handling
-----------------

The plugin:

- Does not store passwords\
- Does not generate or store OTP secrets\
- Does not store recovery codes\
- Does not transmit authentication data externally\
- Does not create custom authentication cookies

All sensitive authentication data remains under the control of:

- WordPress Core\
- Official WordPress Two-Factor Plugin

* * * * *

5\. Access Control
------------------

Access controls remains under the control of WordPress core and the Official WordPress Two-Factor Plugin, including access control to:

- Activate or deactivate the plugin\
- Modify enforced role configuration\
- Override enforcement filters

Administrators and users cannot exempt themselves from enforcement. All users logging in will have 2FA enforced.

Accounts that have not setup 2FA will be emailed a one-time code at login.

* * * * *

6\. Logging
-----------

The plugin does not log any data.

No logging of the following data is ever performed:

- Passwords\
- OTP codes\
- Secret keys\
- Recovery codes
* * * * *

7\. Dependency Trust Boundary
-----------------------------

Security guarantees depend on:

- WordPress Core authentication integrity\
- Official WordPress Two-Factor Plugin integrity\
- Server-side HTTPS enforcement\
- Proper server access controls

If any dependency is compromised, enforcement effectiveness may be reduced.

* * * * *

8\. Failure Mode
----------------

If the Official WordPress Two-Factor Plugin is missing, disabled, or corrupted:

- The SPARXSTAR plugin fails silently\
- Users are not blocked from login\
- No notice is displayed to users.\
- Admins are notified in the wp-admin to install Two-Factor.

* * * * *

9\. Attack Surface
------------------

The plugin introduces no new credential or token storage.\
No external network calls are made.\
No custom authentication endpoints are created.\
No front-end exposure exists, only that of the Official Two-Factor plugin.

* * * * *

10\. Compliance Alignment
-------------------------

This plugin supports alignment with:

- NIST SP 800-63B Authentication Assurance guidance\
- OWASP Account Takeover Prevention practices\
- WordPress VIP security recommendations\
- Principle of Least Privilege access control

* * * * *

11\. Update Policy
------------------

- Updates are deployed only by Super Administrators\
- Updates must be tested in staging environments\
- Security fixes take priority over feature updates

* * * * *

12\. Incident Response
----------------------

If unauthorized access is suspected:

1.  Disable affected accounts

2.  Reset passwords

3.  Review enforcement logs

4.  Verify Two-Factor Plugin integrity

5.  Rotate recovery codes if applicable

* * * * *

13\. Reporting Vulnerabilities
------------------------------

Security issues should be reported privately to the SPARXSTAR maintainers.\
Public disclosure should occur only after a patch is released.

(Contact address may be added by repository owner.)

* * * * *

14\. Policy Review
------------------

This policy must be reviewed:

- After major WordPress core updates\
- After Two-Factor Plugin updates\
- After any authentication-related incident

* * * * *

15\. Summary
------------

The SPARXSTAR Two-Factor Enforcement Plugin provides strict role-based enforcement of two-factor authentication while delegating all credential and token handling to trusted WordPress authentication systems. It introduces no new sensitive data stores and operates in fail-closed mode to prevent authentication without verified 2FA.
