# Quick Reference - URL Changes

## Before & After Examples

### Authentication Pages
| Page | Old URL | New URL |
|------|---------|---------|
| Login | `/index.php` | `/index` |
| Signup | `/signup.php` | `/signup` |
| Login Handler | `/auth/login.php` | `/auth/login` |
| Register Handler | `/auth/register.php` | `/auth/register` |
| Logout | `/auth/logout.php` | `/auth/logout` |

### Dashboard Pages
| Page | Old URL | New URL |
|------|---------|---------|
| Dashboard | `/dashboard/client_dashboard.php` | `/dashboard/client_dashboard` |
| Profile | `/dashboard/profile.php` | `/dashboard/profile` |
| Settings | `/dashboard/setting.php` | `/dashboard/setting` |
| View Products | `/dashboard/view_products.php` | `/dashboard/view_products` |
| View Investments | `/dashboard/view_investments.php` | `/dashboard/view_investments` |
| Recharge | `/dashboard/recharge.php` | `/dashboard/recharge` |
| Withdrawal | `/dashboard/withdrawal.php` | `/dashboard/withdrawal` |
| My Wallet | `/dashboard/my_wallet.php` | `/dashboard/my_wallet` |
| My Team | `/dashboard/my_team.php` | `/dashboard/my_team` |
| Invite | `/dashboard/invite.php` | `/dashboard/invite` |
| Edit Profile | `/dashboard/edit_profile.php` | `/dashboard/edit_profile` |
| Binding Bank | `/dashboard/binding_bank.php` | `/dashboard/binding_bank` |

### Admin Pages
| Page | Old URL | New URL |
|------|---------|---------|
| Admin Dashboard | `/dashboard/admin/admin_dashboard.php` | `/dashboard/admin/admin_dashboard` |
| Manage Users | `/dashboard/admin/manage_users.php` | `/dashboard/admin/manage_users` |
| Manage Products | `/dashboard/admin/manage_products.php` | `/dashboard/admin/manage_products` |
| Manage Transactions | `/dashboard/admin/manage_transactions.php` | `/dashboard/admin/manage_transactions` |
| Settings | `/dashboard/admin/settings.php` | `/dashboard/admin/settings` |

### Agent Pages
| Page | Old URL | New URL |
|------|---------|---------|
| Agent Dashboard | `/dashboard/agent/agent_dashboard.php` | `/dashboard/agent/agent_dashboard` |
| Update Name | `/dashboard/agent/update_name.php` | `/dashboard/agent/update_name` |
| Update Password | `/dashboard/agent/update_password.php` | `/dashboard/agent/update_password` |

## How to Use in Code

### When Creating Links in HTML
```html
<!-- Old Way -->
<a href="profile.php">My Profile</a>
<a href="../auth/logout.php">Logout</a>

<!-- New Way -->
<a href="profile">My Profile</a>
<a href="../auth/logout">Logout</a>
```

### When Creating Form Actions
```html
<!-- Old Way -->
<form method="POST" action="profile.php">
    <!-- form fields -->
</form>

<!-- New Way -->
<form method="POST" action="profile">
    <!-- form fields -->
</form>
```

### With Query Parameters
```html
<!-- Query parameters work the same way -->
<a href="user?id=123">View User</a>
<a href="edit_user?id=<?= $id ?>">Edit User</a>
```

## Important Notes

✅ **Query parameters are preserved** - `?id=123` still works normally
✅ **POST requests work** - Forms submit to clean URLs
✅ **Redirects are automatic** - Old `.php` URLs redirect to new clean URLs
✅ **Sessions persist** - Session data works normally
✅ **All file types included** - CSS, JS, and image links unchanged

## What NOT to Change

❌ DO NOT remove `.php` from:
- CSS files: `href="../assets/css/style.php"` → Keep as is
- JavaScript files: `src="../assets/js/script.php"` → Keep as is
- Image paths: `src="../assets/images/logo.png"` → Keep as is
- Include statements: `include 'header.php'` → Keep as is (PHP includes)

---

**Ready to Use!** All changes have been applied and tested.
