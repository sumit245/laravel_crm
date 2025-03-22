<!DOCTYPE html>
<html>

  <head>
    <title>Job Opportunity</title>
  </head>

  <body>
    <p>Hello {{ $candidate->name }},</p>
    <p>We are excited to inform you that you've been shortlisted for a job opportunity at our company.</p>
    <p>Click the link below to upload your documents:</p>
    <a href="{{ url("/upload-documents/" . $candidate->id) }}">Upload Documents</a>
    <p>Best Regards,</p>
    <p>Your Company</p>
  </body>

</html>
