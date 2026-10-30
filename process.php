<?php
/**
 * Apex Institute Admissions Portal - Server Processor
 * Handles data sanitization, backend validation, enrolment ID generation,
 * and dual rendering (JSON for AJAX and HTML fallback).
 */

date_default_timezone_set('Asia/Kolkata');

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
          || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
        exit;
    } else {
        header('Location: index.html');
        exit;
    }
}

function sanitizeText($val) {
    if ($val === null) return '';
    return htmlspecialchars(stripslashes(trim($val)), ENT_QUOTES, 'UTF-8');
}

// Extract inputs
$fullName        = sanitizeText($_POST['fullName'] ?? '');
$emailAddr       = sanitizeText($_POST['emailAddr'] ?? '');
$mobileNo        = sanitizeText($_POST['mobileNo'] ?? '');
$birthDate       = sanitizeText($_POST['birthDate'] ?? '');
$gender          = sanitizeText($_POST['gender'] ?? '');
$courseStream    = sanitizeText($_POST['courseStream'] ?? '');
$prevLevel       = sanitizeText($_POST['prevLevel'] ?? '');
$scorePercentage = sanitizeText($_POST['scorePercentage'] ?? '');
$passingYear     = sanitizeText($_POST['passingYear'] ?? '');
$streetAddress   = sanitizeText($_POST['streetAddress'] ?? '');
$cityTown        = sanitizeText($_POST['cityTown'] ?? '');
$postalIndex     = sanitizeText($_POST['postalIndex'] ?? '');
$careerNote      = sanitizeText($_POST['careerNote'] ?? '');
$consentCheck    = isset($_POST['consentCheck']);

// Validation
$errors = [];

if (empty($fullName) || strlen($fullName) < 3) {
    $errors['fullName'] = 'Candidate full name is required (min 3 characters).';
}
if (empty($emailAddr) || !filter_var($emailAddr, FILTER_VALIDATE_EMAIL)) {
    $errors['emailAddr'] = 'Valid email address is required.';
}
if (empty($mobileNo) || !preg_match('/^[0-9]{10}$/', $mobileNo)) {
    $errors['mobileNo'] = '10-digit mobile number is required.';
}
if (empty($birthDate)) {
    $errors['birthDate'] = 'Date of birth is required.';
}
if (empty($gender)) {
    $errors['gender'] = 'Please select candidate gender.';
}
if (empty($courseStream)) {
    $errors['courseStream'] = 'Please select academic program.';
}
if (empty($prevLevel)) {
    $errors['prevLevel'] = 'Please select qualifying level.';
}
if (empty($scorePercentage)) {
    $errors['scorePercentage'] = 'Aggregate percentage or CGPA required.';
}
if (empty($passingYear)) {
    $errors['passingYear'] = 'Passing out year is required.';
}
if (empty($streetAddress)) {
    $errors['streetAddress'] = 'Postal address is required.';
}
if (empty($cityTown)) {
    $errors['cityTown'] = 'City is required.';
}
if (empty($postalIndex) || !preg_match('/^[0-9]{6}$/', $postalIndex)) {
    $errors['postalIndex'] = '6-digit postal PIN code required.';
}
if (!$consentCheck) {
    $errors['consentCheck'] = 'You must confirm the declaration statement.';
}

if (!empty($errors)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'Validation error encountered in submitted form.',
            'errors' => $errors
        ]);
        exit;
    } else {
        echo "<!DOCTYPE html><html><head><title>Validation Errors</title><link rel='stylesheet' href='style.css'></head><body>";
        echo "<div class='wrapper' style='margin-top:3rem;'><div class='main-card'>";
        echo "<h2 style='color:#dc2626;'>Submission Errors</h2><ul>";
        foreach ($errors as $f => $m) {
            echo "<li><strong>" . htmlspecialchars($f) . ":</strong> " . htmlspecialchars($m) . "</li>";
        }
        echo "</ul><br><a href='index.html' class='action-btn primary'>Back to Form</a></div></div></body></html>";
        exit;
    }
}

// Enrolment Reference Number
$enrolmentId = 'AITM-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
$enrolDate   = date('d M Y');
$timestamp   = date('d M Y, h:i A');
$formattedDob = !empty($birthDate) ? date('d M Y', strtotime($birthDate)) : $birthDate;

$payload = [
    'enrolmentId'   => $enrolmentId,
    'enrolDate'     => $enrolDate,
    'timestamp'     => $timestamp,
    'fullName'      => $fullName,
    'email'         => $emailAddr,
    'phone'         => $mobileNo,
    'dob'           => $formattedDob,
    'gender'        => $gender,
    'course'        => $courseStream,
    'qualification' => $prevLevel,
    'passingYear'   => $passingYear,
    'score'         => $scorePercentage,
    'address'       => $streetAddress,
    'city'          => $cityTown,
    'pincode'       => $postalIndex,
    'note'          => $careerNote
];

if ($isAjax) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status'  => 'success',
        'message' => 'Candidate enrolled successfully.',
        'data'    => $payload
    ]);
    exit;
}

// Fallback HTML render
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enrolment Voucher - <?php echo $enrolmentId; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,600;0,9..40,700;1,9..40,400&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="site-nav no-print">
    <div class="nav-inner">
      <div class="institution-branding">
        <div class="crest-icon"><i class="fa-solid fa-building-columns"></i></div>
        <div class="branding-info">
          <h1 class="inst-name">APEX INSTITUTE</h1>
          <span class="inst-tagline">College of Engineering & Technology</span>
        </div>
      </div>
    </div>
  </header>

  <main class="page-container">
    <div class="wrapper">
      <section class="receipt-section">
        
        <div class="banner-success no-print">
          <div class="banner-glow"><i class="fa-solid fa-circle-check"></i></div>
          <div>
            <h3>Candidate Enrolment Successful!</h3>
            <p>Your admission record has been received and verified by the server.</p>
          </div>
        </div>

        <div class="voucher-doc">
          <div class="voucher-top">
            <div class="univ-crest">
              <div class="crest-symbol"><i class="fa-solid fa-landmark"></i></div>
              <div class="univ-titles">
                <h2>APEX INSTITUTE OF TECHNOLOGY & MANAGEMENT</h2>
                <p>Office of the Board of Technical Admissions & Dean Academic Affairs</p>
                <span class="voucher-tag">PROVISIONAL ADMISSION ACKNOWLEDGEMENT SLIP</span>
              </div>
            </div>
            <div class="voucher-ref">
              <div class="barcode-box">
                <i class="fa-solid fa-barcode barcode-symbol"></i>
                <span class="ref-code"><?php echo $enrolmentId; ?></span>
              </div>
              <div class="ref-stamp"><small>TIMESTAMP:</small> <strong><?php echo $enrolDate; ?></strong></div>
            </div>
          </div>

          <div class="voucher-rule"></div>

          <div class="data-table">
            <div class="table-cell">
              <span class="col-head"><i class="fa-regular fa-user"></i> Registered Student Name</span>
              <span class="col-body strong-text"><?php echo $fullName; ?></span>
            </div>
            <div class="table-cell">
              <span class="col-head"><i class="fa-regular fa-envelope"></i> Email Identifier</span>
              <span class="col-body"><?php echo $emailAddr; ?></span>
            </div>
            <div class="table-cell">
              <span class="col-head"><i class="fa-solid fa-phone"></i> Contact Mobile</span>
              <span class="col-body"><?php echo $mobileNo; ?></span>
            </div>
            <div class="table-cell">
              <span class="col-head"><i class="fa-regular fa-calendar"></i> Date of Birth & Gender</span>
              <span class="col-body"><?php echo $formattedDob . ' | ' . $gender; ?></span>
            </div>
            <div class="table-cell span-two">
              <span class="col-head"><i class="fa-solid fa-graduation-cap"></i> Enrolled Academic Department</span>
              <span class="col-body prog-accent"><?php echo $courseStream; ?></span>
            </div>
            <div class="table-cell">
              <span class="col-head"><i class="fa-solid fa-award"></i> Prior Education & Passing Year</span>
              <span class="col-body"><?php echo $prevLevel . ' (' . $passingYear . ')'; ?></span>
            </div>
            <div class="table-cell">
              <span class="col-head"><i class="fa-solid fa-chart-line"></i> Qualifying Score</span>
              <span class="col-body"><?php echo $scorePercentage; ?></span>
            </div>
            <div class="table-cell span-two">
              <span class="col-head"><i class="fa-solid fa-location-dot"></i> Permanent Postal Address</span>
              <span class="col-body"><?php echo $streetAddress . ', ' . $cityTown . ' - ' . $postalIndex; ?></span>
            </div>
            <?php if (!empty($careerNote)): ?>
            <div class="table-cell span-two">
              <span class="col-head"><i class="fa-regular fa-message"></i> Statement / Aspirational Note</span>
              <span class="col-body italic-text">"<?php echo $careerNote; ?>"</span>
            </div>
            <?php endif; ?>
          </div>

          <div class="voucher-bottom">
            <div class="digital-seal">
              <i class="fa-solid fa-stamp seal-icon"></i>
              <div class="seal-text">
                <span class="seal-title">CERTIFIED PROVISIONAL ENROLMENT</span>
                <span class="seal-desc">Digital Authentication Stamp: <strong><?php echo $timestamp; ?></strong></span>
              </div>
            </div>
            <div class="dean-sign">
              <div class="signature-line"></div>
              <span>Registrar & Admissions Controller</span>
            </div>
          </div>
        </div>

        <div class="bottom-tools no-print">
          <button type="button" onclick="window.print()" class="action-btn primary">
            <i class="fa-solid fa-print"></i> Print Voucher / Download PDF
          </button>
          <a href="index.html" class="action-btn secondary">
            <i class="fa-solid fa-circle-plus"></i> New Application
          </a>
        </div>

      </section>
    </div>
  </main>
</body>
</html>
