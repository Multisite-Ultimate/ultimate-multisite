# Emergency Login Feature

The Emergency Login feature provides a way to access the WordPress login page directly, bypassing any custom login page redirections or Single Sign-On (SSO) mechanisms. This is particularly useful for administrators who need to access the WordPress admin area when the custom login page is not working properly.

## How to Use

To use the Emergency Login feature, add the `emergency_login=1` parameter to your WordPress login URL:

```
https://example.com/wp-login.php?emergency_login=1
```

This will bypass:
- Redirections to the custom login page
- SSO authentication flows
- Caching mechanisms that might interfere with login

## When to Use

Use the Emergency Login feature in the following situations:

1. When you're unable to log in through the custom login page
2. When you need to troubleshoot login issues
3. When you're locked out of your site due to redirection loops
4. When caching plugins are interfering with the login process

## Security Considerations

The Emergency Login feature is designed for administrators and site owners. While it doesn't bypass WordPress authentication (you still need valid credentials), it does bypass custom login page redirections.

Consider implementing additional security measures:

1. Use strong passwords for administrator accounts
2. Implement two-factor authentication
3. Limit login attempts
4. Monitor login activity

## Troubleshooting

If you're experiencing login issues even with the Emergency Login feature:

1. Clear your browser cache and cookies
2. Try using a different browser
3. Disable caching plugins temporarily
4. Check for JavaScript errors in your browser console

## Related Settings

The Emergency Login feature works alongside the following WP Ultimo settings:

- **Custom Login Page**: Found under Settings > Login & Registration
- **Obfuscate Original Login URL**: Found under Settings > Login & Registration

Even when these settings are enabled, the Emergency Login parameter will allow direct access to the WordPress login page.
