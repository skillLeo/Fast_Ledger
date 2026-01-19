{{-- resources/views/emails/verify-email.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Fast Ledger</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f7;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 10px;
        }

        .company-name {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 10px 0 5px 0;
            letter-spacing: 1px;
        }

        .tagline {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 400;
        }

        .email-body {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 20px;
        }

        .user-name {
            color: #667eea;
        }

        .content-text {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 15px;
            line-height: 1.8;
        }

        .email-icon {
            font-size: 64px;
            text-align: center;
            margin: 30px 0;
        }

        .verify-button-container {
            text-align: center;
            margin: 35px 0;
        }

        .verify-button {
            display: inline-block;
            padding: 16px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }

        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .alternative-link-section {
            background-color: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .alternative-link-title {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .alternative-link {
            font-size: 13px;
            color: #667eea;
            word-break: break-all;
            text-decoration: none;
        }

        .security-notice {
            background-color: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 6px;
            padding: 15px;
            margin: 25px 0;
        }

        .security-notice-title {
            color: #c53030;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .security-notice-text {
            color: #742a2a;
            font-size: 13px;
            line-height: 1.6;
        }

        .email-footer {
            background-color: #2d3748;
            color: #cbd5e0;
            padding: 30px;
            text-align: center;
            font-size: 13px;
        }

        .footer-company-name {
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .footer-links {
            margin: 15px 0;
        }

        .footer-link {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }

        .footer-link:hover {
            text-decoration: underline;
        }

        .copyright {
            color: #a0aec0;
            margin-top: 15px;
            font-size: 12px;
        }

        @media only screen and (max-width: 600px) {
            .email-header,
            .email-body,
            .email-footer {
                padding: 25px 20px;
            }

            .company-name {
                font-size: 24px;
            }

            .greeting {
                font-size: 20px;
            }

            .content-text {
                font-size: 15px;
            }

            .verify-button {
                padding: 14px 30px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        
        <!-- Header with Logo -->
        <div class="email-header">
            {{-- Uncomment this line when you have a logo --}}
            {{-- <img src="{{ asset('admin/assets/images/brand-logos/desktop-dark.png') }}" alt="Fast Ledger Logo" class="logo"> --}}
            
            <div class="company-name">Fast Ledger</div>
            <div class="tagline">Cloud Accounting & Invoicing Platform</div>
        </div>

        <!-- Email Body -->
        <div class="email-body">
            
            <!-- Greeting -->
            <div class="greeting">
                Hello <span class="user-name">{{ $user->Full_Name }}</span>! 👋
            </div>

            <!-- Welcome Message -->
            <p class="content-text">
                Welcome to <strong>Fast Ledger</strong>! We're excited to have you on board.
            </p>

            <p class="content-text">
                Before you can start managing your finances and invoices, we need to verify your email address 
                to ensure the security of your account.
            </p>

            <!-- Email Icon -->
            <div class="email-icon">
                📧
            </div>

            <!-- Call to Action -->
            <p class="content-text" style="text-align: center; font-weight: 600;">
                Click the button below to verify your email address:
            </p>

            <!-- Verify Button -->
            <div class="verify-button-container">
                <a href="{{ $verificationUrl }}" class="verify-button">
                    ✓ Verify Email Address
                </a>
            </div>

            <!-- Alternative Link Section -->
            <div class="alternative-link-section">
                <div class="alternative-link-title">
                    Having trouble clicking the button?
                </div>
                <div style="font-size: 13px; color: #4a5568; margin-bottom: 10px;">
                    Copy and paste this URL into your browser:
                </div>
                <a href="{{ $verificationUrl }}" class="alternative-link">
                    {{ $verificationUrl }}
                </a>
            </div>

            <!-- Security Notice -->
            <div class="security-notice">
                <div class="security-notice-title">
                    🔒 Security Notice
                </div>
                <div class="security-notice-text">
                    If you did not create an account with Fast Ledger, please ignore this email. 
                    No account will be created without email verification.
                </div>
            </div>

            <!-- Expiration Notice -->
            <p class="content-text" style="font-size: 14px; color: #718096;">
                This verification link will expire in <strong>60 minutes</strong> for security reasons.
            </p>

        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-company-name">Fast Ledger</div>
            <div>Cloud-Based Accounting & Invoicing Solution</div>
            
            <div class="footer-links">
                <a href="#" class="footer-link">Help Center</a>
                <a href="#" class="footer-link">Contact Support</a>
                <a href="#" class="footer-link">Privacy Policy</a>
            </div>
            
            <div class="copyright">
                © {{ date('Y') }} Fast Ledger. All rights reserved.
            </div>
        </div>

    </div>
</body>
</html>