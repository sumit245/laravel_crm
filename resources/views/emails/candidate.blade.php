<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Offer Letter</title>
    <style>
      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        padding: 40px;
        color: #000;
        max-width: 800px;
        margin: auto;
      }

      h2 {
        text-align: center;
        text-decoration: underline;
      }
    </style>
  </head>

  <body>

    <p>
      <strong>{{ $candidate->name }}</strong><br>
      {{ $candidate->address }}<br>
      Contact No.: <strong>{{ $candidate->contactNo }}</strong><br>
      Email: <strong>{{ $candidate->email }}</strong><br>
    </p>

    <p><strong>Date:</strong> 08<sup>th</sup> April 2025</p>

    <h2>Offer Letter</h2>

    <p>
      Dear {{ $candidate->name }},
    </p>

    <p>
      Congratulations! We are pleased to offer you the position of
      <strong>{{ $candidate->designation }} -{{ $candidate->department }}</strong> at <strong>Sugs
        Lloyd Limited</strong>, with an annual Cost to Company (CTC) of <strong>₹ {{ $candidate->ctc }}/- (Rupees Six
        Lakhs and
        Eighty Four Thousand Only)</strong>. We believe your skills and experience will significantly contribute to the
      growth and success of Sugs Lloyd Limited. Your joining date must be before <strong>08<sup>th</sup> April
        2025</strong>, with your base location being <strong>Noida</strong>. Additionally, you will be on a
      <strong>6-month probation period</strong>, during which your performance will be closely reviewed.
    </p>

    <h3>Place/Transfer:</h3>

    <p>
      Your assigned place of work will be Noida. However, during your tenure with Sugs Lloyd Limited, you may be
      assigned, transferred, or deputed to any other location in India or outside, as required by business needs. In
      such case, you will be governed by the terms and conditions applicable to the place of transfer.
    </p>

    <p>
      This offer is subject to a formal appointment letter you will receive upon joining, which includes more specific
      employment terms and conditions. Please submit the following documents at the time of joining, along with a signed
      copy of this letter as proof of acceptance. Kindly ensure that all the submitted originals are authentic. Sugs
      Lloyd Limited reserves the right to withdraw the offer in case of any discrepancy.
    </p>

    <p><strong>Documents Required:</strong></p>
    <ol>
      <li>2 Passport-size Photographs</li>
      <li>Copy of Valid ID (with Date of Birth in DD/MM/YYYY format)</li>
      <li>Latest Resume</li>
      <li>Relieving/Experience Letter from Previous Organization</li>
      <li>Educational Certificates with Final Year Marksheet</li>
      <li>Copy of PAN Card & Aadhar Card (if 18<sup>th</sup>-standard mandatory)</li>
      <li>Bank Account details with Passbook Copy</li>
    </ol>

    <p>
      We request you to <a href="https://slldm.com/apply-now">Reply (by clicking here)</a> to this letter to confirm your
      acceptance of this offer within
      <strong>three (3)</strong> business days from the date of receipt. We are excited to welcome you to the team and
      look forward to your positive contributions at <strong>Sugs Lloyd Limited</strong>.
    </p>

    <p>
      Sincerely,<br><br>
      <strong>For Sugs Lloyd Limited</strong><br><br>
      <em>Authorized Signatory</em>
    </p>

  </body>

</html>
