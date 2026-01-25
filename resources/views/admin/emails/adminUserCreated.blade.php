<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Created</title>
</head>
<body>
   <h1>Agent Admin Created</h1>
<p>Hello,</p>
<p>An agent admin account has been created with the following details:</p>

<table>
    <tr>
        <td><strong>User Name:</strong></td>
        <td>{{ $adminUserName }}</td>
    </tr>
    <tr>
        <td><strong>Password:</strong></td>
        <td>{{ $adminPassword }}</td>
    </tr>
</table>

<p>We recommend you change the password immediately for security reasons.</p>
<p>Thank you,</p>
<p>The Team</p>

</body>
</html>
