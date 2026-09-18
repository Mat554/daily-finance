<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Database Backup</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f4f4f4; margin: 0; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: #2563eb; padding: 24px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 20px;">Daily Finance — Weekly Backup</h1>
        </div>
        <div style="padding: 24px;">
            <p style="color: #374151; margin: 0 0 16px;">Your weekly database backup has been created successfully.</p>

            <div style="background: #f9fafb; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="color: #6b7280; font-size: 13px; padding: 4px 0;">Backup Date</td>
                        <td style="color: #111827; font-size: 13px; text-align: right; font-weight: 500;">{{ $timestamp }}</td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; font-size: 13px; padding: 4px 0;">Included</td>
                        <td style="color: #111827; font-size: 13px; text-align: right; font-weight: 500;">Users, Transactions, Expenses, Reports, Account Balances</td>
                    </tr>
                </table>
            </div>

            <p style="color: #374151; font-size: 13px; margin: 0 0 16px;">The backup contains CSV files for each table: <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px;">users</code>, <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px;">transactions</code>, <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px;">expenses</code>, <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px;">expense_payments</code>, <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px;">monthly_reports</code>, <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px;">account_balances</code>, and a <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px;">manifest.txt</code>. All are attached to this email.</p>

            <div style="border-left: 4px solid #fbbf24; background: #fefce8; padding: 12px; border-radius: 0 8px 8px 0; margin-bottom: 16px;">
                <p style="color: #92400e; font-size: 13px; margin: 0;"><strong>Important:</strong> Keep this backup safe. Store it in a secure location (Google Drive, Dropbox, external drive). Backups older than 7 days are automatically removed from the server.</p>
            </div>

            <p style="color: #9ca3af; font-size: 12px; margin: 0; text-align: center;">This is an automated email from Daily Finance.</p>
        </div>
    </div>
</body>
</html>
