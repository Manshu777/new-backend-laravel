<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Travel Application</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f9; color: #333;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <!-- Header -->
        <tr>
            <td style="background-color: #1a73e8; padding: 20px; text-align: center; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                <img src="https://via.placeholder.com/150x50?text=NextGenTrip+Logo" alt="NextGenTrip Logo" style="max-width: 150px; height: auto;">
                <h1 style="color: #ffffff; font-size: 24px; margin: 10px 0;">New Travel Application Submission</h1>
            </td>
        </tr>
        <!-- Body -->
        <tr>
            <td style="padding: 20px;">
                <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">Dear Team,</p>
                <p style="font-size: 16px; line-height: 1.5; margin: 0 0 20px;">A new travel application has been submitted. Please review the details below and take necessary actions.</p>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="font-size: 16px; line-height: 1.5;">
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold; width: 40%;">Full Name:</td>
                        <td style="padding: 10px 0;">{{ $full_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Email:</td>
                        <td style="padding: 10px 0;"><a href="mailto:{{ $email }}" style="color: #1a73e8; text-decoration: none;">{{ $email }}</a></td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Phone:</td>
                        <td style="padding: 10px 0;">{{ $phone }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Passport Number:</td>
                        <td style="padding: 10px 0;">{{ $passport_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Passport Expiry Date:</td>
                        <td style="padding: 10px 0;">{{ $passport_expiry_date }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Tentative Departure Date:</td>
                        <td style="padding: 10px 0;">{{ $tentative_departure_date }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Tentative Return Date:</td>
                        <td style="padding: 10px 0;">{{ $tentative_return_date }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Destination Country:</td>
                        <td style="padding: 10px 0;">{{ $destination_country }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Purpose of Visit:</td>
                        <td style="padding: 10px 0;">{{ $purpose_of_visit }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Date of Birth:</td>
                        <td style="padding: 10px 0;">{{ $date_of_birth }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Gender:</td>
                        <td style="padding: 10px 0;">{{ $gender }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Address:</td>
                        <td style="padding: 10px 0;">{{ $address }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">City:</td>
                        <td style="padding: 10px 0;">{{ $city }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">State:</td>
                        <td style="padding: 10px 0;">{{ $state }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Pincode:</td>
                        <td style="padding: 10px 0;">{{ $pincode }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Passport Front:</td>
                        <td style="padding: 10px 0;">
                            @if($passport_front_path != 'Not uploaded')
                                <a href="{{ Storage::url($passport_front_path) }}" style="color: #1a73e8; text-decoration: none;">View Document</a>
                            @else
                                Not uploaded
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Passport Back:</td>
                        <td style="padding: 10px 0;">
                            @if($passport_back_path != 'Not uploaded')
                                <a href="{{ Storage::url($passport_back_path) }}" style="color: #1a73e8; text-decoration: none;">View Document</a>
                            @else
                                Not uploaded
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Photograph:</td>
                        <td style="padding: 10px 0;">
                            @if($photograph_path != 'Not uploaded')
                                <a href="{{ Storage::url($photograph_path) }}" style="color: #1a73e8; text-decoration: none;">View Image</a>
                            @else
                                Not uploaded
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; font-weight: bold;">Supporting Document:</td>
                        <td style="padding: 10px 0;">
                            @if($supporting_document_path != 'Not uploaded')
                                <a href="{{ Storage::url($supporting_document_path) }}" style="color: #1a73e8; text-decoration: none;">View Document</a>
                            @else
                                Not uploaded
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background-color: #f4f4f9; padding: 20px; text-align: center; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                <p style="font-size: 14px; color: #666; margin: 0;">&copy; {{ date('Y') }} NextGenTrip. All rights reserved.</p>
                <p style="font-size: 14px; color: #666; margin: 10px 0 0;">
                    <a href="https://nextgentrip.com" style="color: #1a73e8; text-decoration: none;">Visit our website</a> | 
                    <a href="mailto:support@nextgentrip.com" style="color: #1a73e8; text-decoration: none;">Contact Support</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>