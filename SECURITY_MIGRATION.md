# Security Migration Guide

## CRITICAL: Immediate Actions Required

This guide outlines the steps you MUST take immediately to secure your application after the security fixes have been applied.

---

## 1. Set Up Environment Variables (CRITICAL - Do This First!)

### Step 1: Create .env File

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

### Step 2: Fill in Your Actual Credentials

Edit the `.env` file and replace all placeholder values with your actual credentials:

```bash
# Use a text editor to edit .env
nano .env  # or vim, notepad, etc.
```

**IMPORTANT:** Use your CURRENT credentials from the old config.php file. You will rotate these in Step 2.

### Step 3: Verify .gitignore

Ensure `.env` is in your `.gitignore` file (it should already be there):

```bash
grep "^\.env$" .gitignore
```

If not found, add it:

```bash
echo ".env" >> .gitignore
```

### Step 4: Test the Application

After setting up `.env`, test that the application still works:
- Try logging in
- Check database connectivity
- Verify email functionality

---

## 2. Rotate All Exposed Credentials (CRITICAL!)

Since your credentials were hardcoded in `config.php` which may have been committed to version control, you MUST rotate all credentials immediately:

### Database Password
```sql
-- Connect to your database and change the password
ALTER USER 'lovemyhadmin'@'%' IDENTIFIED BY 'NEW_STRONG_PASSWORD';
FLUSH PRIVILEGES;
```

Update `.env` with the new password.

### SMTP Password
- Log into your email provider's control panel
- Change the password for `donotreply@lovemystyle.ch`
- Update `.env` with the new password

### API Keys to Rotate:

1. **BREVO API Key**
   - Go to https://app.brevo.com/settings/keys/api
   - Delete the old API key
   - Generate a new one
   - Update `.env`

2. **FreeCurrency API Key**
   - Go to your FreeCurrency dashboard
   - Revoke the old key
   - Generate a new one
   - Update `.env`

3. **POS Middleware API Key**
   - Update this in your POS middleware system
   - Update `.env`

4. **CRON Key**
   - Generate a new random key:
     ```bash
     openssl rand -hex 32
     ```
   - Update `.env`
   - Update your cron job configurations

5. **PKPASS Certificate Password**
   - If possible, regenerate your Apple Wallet certificate with a new password
   - Update `.env`

---

## 3. Remove Credentials from Git History (CRITICAL!)

If `config.php` with hardcoded credentials was ever committed to version control, you MUST remove it from Git history:

### Option A: Using BFG Repo-Cleaner (Recommended)

```bash
# Download BFG from https://rtyley.github.io/bfg-repo-cleaner/
# Replace passwords in all commits
java -jar bfg.jar --replace-text passwords.txt

# Clean up
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# Force push (coordinate with team first!)
git push origin --force --all
```

### Option B: Using git filter-branch

```bash
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch inc/config.php" \
  --prune-empty --tag-name-filter cat -- --all

git reflog expire --expire=now --all
git gc --prune=now --aggressive

git push origin --force --all
```

**WARNING:** Force pushing rewrites history. Coordinate with your team first!

---

## 4. Verify Security Fixes Are Working

### Test CSRF Protection

1. Log in to the admin panel
2. Open browser developer tools (F12)
3. Check that forms now have a hidden `csrf_token` field
4. Try submitting a form - it should work normally
5. Try submitting without the token - it should be blocked

### Test Path Traversal Fix

Try accessing: `https://yourdomain.com/index.php?page=../inc/config`

**Expected Result:** Error message "Invalid page request"

### Test XSS Fixes

1. Try adding a customer with name: `<script>alert('XSS')</script>`
2. View the customer list
3. **Expected Result:** The script should display as plain text, not execute

---

## 5. Deploy to Production

### Before Deployment:

1. ✅ `.env` file created and configured
2. ✅ All credentials rotated
3. ✅ Git history cleaned
4. ✅ Security fixes tested in development
5. ✅ Backup of production database created

### Deployment Steps:

1. Upload the updated files to production
2. Create `.env` file on production server (DO NOT commit it!)
3. Set proper file permissions:
   ```bash
   chmod 600 .env
   chmod 755 inc/classes/CSRF.php
   ```
4. Test all functionality
5. Monitor logs for errors

---

## 6. Additional Security Recommendations

### Implement Rate Limiting (Coming Soon)

The security audit identified missing rate limiting. This will be implemented in the next phase.

### Add Security Headers

Add to your `.htaccess` or web server configuration:

```apache
Header always set X-Frame-Options "DENY"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

### Monitor for Breaches

- Set up monitoring for failed login attempts
- Review application logs regularly
- Consider implementing intrusion detection

### Regular Security Audits

- Schedule quarterly security reviews
- Keep dependencies updated (`composer update`)
- Subscribe to security advisories for PHP and your dependencies

---

## 7. Remaining Security Tasks

The following issues are still pending implementation:

- [ ] Add CSRF protection to all remaining forms
- [ ] Implement rate limiting on authentication
- [ ] Add comprehensive authorization checks to all delete actions
- [ ] Fix SQL injection in search (escape LIKE wildcards)
- [ ] Implement audit logging for sensitive operations
- [ ] Add Content Security Policy headers
- [ ] Review and fix remaining XSS vulnerabilities in search results

---

## Need Help?

If you encounter issues during migration:

1. Check application logs in `/logs/`
2. Verify `.env` file syntax (no spaces around `=`)
3. Ensure file permissions are correct
4. Test database connectivity separately

---

## Summary of Changes Made

✅ **COMPLETED:**
- Created CSRF protection system (`inc/classes/CSRF.php`)
- Fixed path traversal vulnerability in `index.php`
- Fixed stored XSS in `Utils::print_table_row()`
- Fixed reflected XSS in customer search form
- Added session regeneration on login
- Migrated configuration to environment variables
- Removed insecure pages from public access list

⏳ **PENDING:**
- CSRF protection needs to be added to all forms
- Rate limiting implementation
- Authorization checks for all sensitive operations
- Additional XSS fixes in search results
- SQL injection fixes

---

**Last Updated:** 2025-11-13
