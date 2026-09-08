<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$clean = [];
$submitted = false;
$parentEmailSent = false;
$serverError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = validate_registration($_POST);
    $errors = $result['errors'];
    $clean = $result['clean'];

    if (empty($errors)) {
      try {
        $pdo = get_db();
        $stmt = $pdo->prepare('INSERT INTO registrations (
            child_name, child_dob, gender, birth_certificate,
            parent1_name, parent1_mobile, parent1_email,
            parent2_name, parent2_mobile, parent2_email,
            home_address, child_lives_with,
            first_language, religion,
            has_allergies, allergies_detail,
            has_medical_condition, medical_detail,
            has_additional_needs, additional_needs_detail,
            has_dietary_requirements, dietary_detail,
            package, days_required,
            collector1_name, collector1_relationship, collector1_mobile, collector1_regular,
            collector2_name, collector2_relationship, collector2_mobile, collector2_regular,
            collector3_name, collector3_relationship, collector3_mobile, collector3_regular,
            emergency_name, emergency_relationship, emergency_mobile,
            has_important_info, important_info_detail,
            consent_first_aid, consent_activities, consent_communication, consent_photos,
            signed_name, signed_date
        ) VALUES (
            :child_name, :child_dob, :gender, :birth_certificate,
            :parent1_name, :parent1_mobile, :parent1_email,
            :parent2_name, :parent2_mobile, :parent2_email,
            :home_address, :child_lives_with,
            :first_language, :religion,
            :has_allergies, :allergies_detail,
            :has_medical_condition, :medical_detail,
            :has_additional_needs, :additional_needs_detail,
            :has_dietary_requirements, :dietary_detail,
            :package, :days_required,
            :collector1_name, :collector1_relationship, :collector1_mobile, :collector1_regular,
            :collector2_name, :collector2_relationship, :collector2_mobile, :collector2_regular,
            :collector3_name, :collector3_relationship, :collector3_mobile, :collector3_regular,
            :emergency_name, :emergency_relationship, :emergency_mobile,
            :has_important_info, :important_info_detail,
            :consent_first_aid, :consent_activities, :consent_communication, :consent_photos,
            :signed_name, :signed_date
        )');

        $params = $clean;
        unset($params['days_required_arr']);
        $stmt->execute($params);
      } catch (\Throwable $e) {
        error_log('Registration save failed: ' . $e->getMessage());
        $serverError = true;
      }

      if (!$serverError) {
        // Email to parent, only if they gave one
        $parentEmailSent = false;
        if ($clean['parent1_email'] !== '') {
            $parentHtml = '<p>Dear ' . e($clean['parent1_name']) . ',</p>'
                . '<p>Thank you for registering <strong>' . e($clean['child_name']) . '</strong> with The Grove.</p>'
                . '<p>Your application is currently <strong>under review</strong>. Our team will get back to you shortly.</p>'
                . '<p>Warm regards,<br>The Grove</p>';
            $parentEmailSent = send_mail($clean['parent1_email'], $clean['parent1_name'], 'Your registration is under review — The Grove', $parentHtml);
        }

        // Email to admin/registration inbox
        $config = require __DIR__ . '/config/config.php';
        $adminHtml = '<p>A new registration has been submitted.</p><ul>'
            . '<li><strong>Child:</strong> ' . e($clean['child_name']) . ' (DOB: ' . e($clean['child_dob']) . ')</li>'
            . '<li><strong>Parent/Guardian 1:</strong> ' . e($clean['parent1_name']) . ' — ' . e($clean['parent1_mobile']) . ' — ' . e($clean['parent1_email'] !== '' ? $clean['parent1_email'] : 'no email provided') . '</li>'
            . '<li><strong>Package:</strong> ' . e($clean['package']) . '</li>'
            . '<li><strong>Days:</strong> ' . e($clean['days_required']) . '</li>'
            . '</ul><p>Log in to the admin panel to view the full application.</p>';
        send_mail($config['mail']['admin_notify_address'], 'The Grove Admissions', 'New registration: ' . $clean['child_name'], $adminHtml);

        $submitted = true;
        $clean = [];
      }
    }
}
?>
<!doctype html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Register Your Child | The Grove</title>
  <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="stylesheet" href="assets/css/bootstrap.css" media="all" />
  <link rel="stylesheet" href="assets/css/fonts.css" media="all" />
  <link rel="stylesheet" href="assets/css/pages/registration-form.css" media="all" />

  <link rel="icon" href="assets/images/06/Favicon.png" sizes="32x32" />
  <link rel="apple-touch-icon" href="assets/images/06/Favicon.png" />
</head>
<body class="reg-page">

  <main class="reg-main">
    <div class="container">
      <div class="reg-topbar">
        <a href="index.html" class="reg-brand">
          <img src="assets/images/06/Logo-Final.png" alt="The Grove" >
        </a>
        <a href="index.html" class="btn btn-sm reg-home-btn">
          &#8962; Home
        </a>
      </div>

      <div class="reg-card">
        <div class="reg-card-header">
          <h1>Child Registration Form</h1>
          <p class="mb-0">Please complete every required field to register your child with The Grove.</p>
        </div>

        <div class="reg-card-body">
          <?php if ($submitted): ?>
            <div class="alert alert-success" role="alert">
              <strong>Thank you!</strong> Your registration has been submitted and is now
              <strong>under review</strong>.<?= $parentEmailSent ? " We've emailed you a confirmation." : '' ?>
            </div>
          <?php else: ?>
            <?php if ($serverError): ?>
              <div class="alert alert-danger" role="alert">
                Sorry, something went wrong while saving your registration. Please try again in a moment,
                or contact us directly if the problem continues.
              </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger" role="alert">
                Please correct the errors highlighted below and resubmit.
              </div>
            <?php endif; ?>

            <form method="post" action="registeration.php" novalidate>

              <h5 class="reg-section-title">Child Details</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Child's Name</label>
                  <input type="text" name="child_name" class="<?= field_class($errors, 'child_name') ?>" value="<?= old($clean, 'child_name') ?>">
                  <?= field_error($errors, 'child_name') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label">D.O.B.</label>
                  <input type="date" name="child_dob" class="<?= field_class($errors, 'child_dob') ?>" value="<?= old($clean, 'child_dob') ?>">
                  <?= field_error($errors, 'child_dob') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label d-block">Gender</label>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" <?= old_checked($clean, 'gender', 'Male') ?>>
                    <label class="form-check-label" for="genderMale">Male</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" <?= old_checked($clean, 'gender', 'Female') ?>>
                    <label class="form-check-label" for="genderFemale">Female</label>
                  </div>
                  <?= field_error($errors, 'gender') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label d-block">B-Form / Birth Certificate</label>
                  <?php foreach (['Available', 'Pending', 'N/A'] as $opt): ?>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="birth_certificate" id="bform<?= $opt ?>" value="<?= $opt ?>" <?= old_checked($clean, 'birth_certificate', $opt) ?>>
                      <label class="form-check-label" for="bform<?= $opt ?>"><?= $opt ?></label>
                    </div>
                  <?php endforeach; ?>
                  <?= field_error($errors, 'birth_certificate') ?>
                </div>
              </div>

              <h5 class="reg-section-title">Parents / Guardians</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Parent/Guardian 1</label>
                  <input type="text" name="parent1_name" class="<?= field_class($errors, 'parent1_name') ?>" value="<?= old($clean, 'parent1_name') ?>">
                  <?= field_error($errors, 'parent1_name') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Parent/Guardian 2</label>
                  <input type="text" name="parent2_name" class="form-control" value="<?= old($clean, 'parent2_name') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Mobile / WhatsApp</label>
                  <input type="text" name="parent1_mobile" class="<?= field_class($errors, 'parent1_mobile') ?>" value="<?= old($clean, 'parent1_mobile') ?>">
                  <?= field_error($errors, 'parent1_mobile') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Mobile / WhatsApp</label>
                  <input type="text" name="parent2_mobile" class="form-control" value="<?= old($clean, 'parent2_mobile') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email <small class="text-muted">(optional)</small></label>
                  <input type="email" name="parent1_email" class="<?= field_class($errors, 'parent1_email') ?>" value="<?= old($clean, 'parent1_email') ?>">
                  <?= field_error($errors, 'parent1_email') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Email</label>
                  <input type="email" name="parent2_email" class="<?= field_class($errors, 'parent2_email') ?>" value="<?= old($clean, 'parent2_email') ?>">
                  <?= field_error($errors, 'parent2_email') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Home Address</label>
                  <input type="text" name="home_address" class="<?= field_class($errors, 'home_address') ?>" value="<?= old($clean, 'home_address') ?>">
                  <?= field_error($errors, 'home_address') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label d-block">Child lives with</label>
                  <?php foreach (['Both parents', 'Mother', 'Father', 'Other'] as $opt): ?>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="child_lives_with" id="lives<?= str_replace(' ', '', $opt) ?>" value="<?= $opt ?>" <?= old_checked($clean, 'child_lives_with', $opt) ?>>
                      <label class="form-check-label" for="lives<?= str_replace(' ', '', $opt) ?>"><?= $opt ?></label>
                    </div>
                  <?php endforeach; ?>
                  <?= field_error($errors, 'child_lives_with') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label">First language</label>
                  <input type="text" name="first_language" class="form-control" value="<?= old($clean, 'first_language') ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Religion (optional)</label>
                  <input type="text" name="religion" class="form-control" value="<?= old($clean, 'religion') ?>">
                </div>
              </div>

              <h5 class="reg-section-title">Health &amp; Care</h5>
              <div class="row g-3">
                <?php
                $yesNoFields = [
                    ['has_allergies', 'allergies_detail', 'Allergies / food allergies', 'None'],
                    ['has_medical_condition', 'medical_detail', 'Medical condition / medication', 'None'],
                    ['has_additional_needs', 'additional_needs_detail', 'Additional care / developmental needs?', 'No'],
                    ['has_dietary_requirements', 'dietary_detail', 'Dietary requirements', 'None'],
                ];
                foreach ($yesNoFields as [$field, $detailField, $label, $negative]):
                ?>
                  <div class="col-md-6">
                    <label class="form-label d-block"><?= $label ?></label>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="<?= $field ?>" id="<?= $field ?>No" value="<?= $negative ?>" <?= old_checked($clean, $field, $negative) ?>>
                      <label class="form-check-label" for="<?= $field ?>No"><?= $negative ?></label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="<?= $field ?>" id="<?= $field ?>Yes" value="Yes" <?= old_checked($clean, $field, 'Yes') ?>>
                      <label class="form-check-label" for="<?= $field ?>Yes">Yes:</label>
                    </div>
                    <input type="text" name="<?= $detailField ?>" class="<?= field_class($errors, $detailField, 'form-control mt-2') ?>" placeholder="Please give details" value="<?= old($clean, $detailField) ?>">
                    <?= field_error($errors, $field) ?>
                    <?= field_error($errors, $detailField) ?>
                  </div>
                <?php endforeach; ?>
              </div>

              <h5 class="reg-section-title">Attendance / Package</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label d-block">Package</label>
                  <?php foreach (['Full Day 7:30-5:30' => 'Full Day 7:30–5:30', '8 Hours' => '8 Hours', '6 Hours' => '6 Hours', '4 Hours' => '4 Hours'] as $val => $labelText): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="package" id="pkg<?= str_replace(' ', '', $val) ?>" value="<?= $val ?>" <?= old_checked($clean, 'package', $val) ?>>
                      <label class="form-check-label" for="pkg<?= str_replace(' ', '', $val) ?>"><?= $labelText ?></label>
                    </div>
                  <?php endforeach; ?>
                  <?= field_error($errors, 'package') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label d-block">Days Required</label>
                  <?php foreach (['Mon' => 'Mon', 'Tue' => 'Tue', 'Wed' => 'Wed', 'Thu' => 'Thu', 'Fri' => 'Fri', 'Sat' => 'Sat (additional cost)'] as $val => $labelText): ?>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="checkbox" name="days_required[]" id="day<?= $val ?>" value="<?= $val ?>" <?= in_array($val, $clean['days_required_arr'] ?? [], true) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="day<?= $val ?>"><?= $labelText ?></label>
                    </div>
                  <?php endforeach; ?>
                  <?= field_error($errors, 'days_required') ?>
                </div>
              </div>

              <h5 class="reg-section-title">Authorized Persons for Pick-up <small class="text-muted">(optional)</small></h5>
              <?php for ($i = 1; $i <= 3; $i++): ?>
                <div class="row g-3 align-items-end mb-2">
                  <div class="col-md-4">
                    <label class="form-label"><?= $i ?>. Name</label>
                    <input type="text" name="collector<?= $i ?>_name" class="form-control" value="<?= old($clean, "collector{$i}_name") ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Relationship</label>
                    <input type="text" name="collector<?= $i ?>_relationship" class="form-control" value="<?= old($clean, "collector{$i}_relationship") ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="collector<?= $i ?>_mobile" class="form-control" value="<?= old($clean, "collector{$i}_mobile") ?>">
                  </div>
                  <div class="col-md-2">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="collector<?= $i ?>_regular" id="regular<?= $i ?>" value="1" <?= !empty($clean["collector{$i}_regular"]) ? 'checked' : '' ?>>
                      <label class="form-check-label" for="regular<?= $i ?>">Regular collector</label>
                    </div>
                  </div>
                </div>
              <?php endfor; ?>

              <h5 class="reg-section-title">Emergency Contact</h5>
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Name</label>
                  <input type="text" name="emergency_name" class="<?= field_class($errors, 'emergency_name') ?>" value="<?= old($clean, 'emergency_name') ?>">
                  <?= field_error($errors, 'emergency_name') ?>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Relationship</label>
                  <input type="text" name="emergency_relationship" class="<?= field_class($errors, 'emergency_relationship') ?>" value="<?= old($clean, 'emergency_relationship') ?>">
                  <?= field_error($errors, 'emergency_relationship') ?>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Mobile</label>
                  <input type="text" name="emergency_mobile" class="<?= field_class($errors, 'emergency_mobile') ?>" value="<?= old($clean, 'emergency_mobile') ?>">
                  <?= field_error($errors, 'emergency_mobile') ?>
                </div>
                <div class="col-12">
                  <label class="form-label d-block">Important family / social information</label>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="has_important_info" id="infoNone" value="None" <?= old_checked($clean, 'has_important_info', 'None') ?>>
                    <label class="form-check-label" for="infoNone">None</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="has_important_info" id="infoYes" value="Yes" <?= old_checked($clean, 'has_important_info', 'Yes') ?>>
                    <label class="form-check-label" for="infoYes">Yes — please briefly explain:</label>
                  </div>
                  <input type="text" name="important_info_detail" class="<?= field_class($errors, 'important_info_detail', 'form-control mt-2') ?>" value="<?= old($clean, 'important_info_detail') ?>">
                  <?= field_error($errors, 'has_important_info') ?>
                  <?= field_error($errors, 'important_info_detail') ?>
                </div>
              </div>

              <h5 class="reg-section-title">Parent / Guardian Declaration &amp; Consent</h5>
              <p class="reg-declaration">
                I confirm that the information provided is accurate and I will inform The Grove of any changes.
                I understand that children will only be released to authorised persons, subject to prior notification and verification.
              </p>
              <div class="reg-consents">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="consent_first_aid" id="consentFirstAid" value="1" <?= !empty($clean['consent_first_aid']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="consentFirstAid">I consent to basic first aid / emergency medical assistance when required.</label>
                  <?= field_error($errors, 'consent_first_aid') ?>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="consent_activities" id="consentActivities" value="1" <?= !empty($clean['consent_activities']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="consentActivities">I consent to age-appropriate indoor and outdoor activities.</label>
                  <?= field_error($errors, 'consent_activities') ?>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="consent_communication" id="consentCommunication" value="1" <?= !empty($clean['consent_communication']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="consentCommunication">I consent to routine communication regarding my child.</label>
                  <?= field_error($errors, 'consent_communication') ?>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="consent_photos" id="consentPhotos" value="1" <?= !empty($clean['consent_photos']) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="consentPhotos">I consent to photos/videos regarding my child by The Grove. <small class="text-muted">(optional)</small></label>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Parent/Guardian Name (signature)</label>
                  <input type="text" name="signed_name" class="<?= field_class($errors, 'signed_name') ?>" value="<?= old($clean, 'signed_name') ?>">
                  <?= field_error($errors, 'signed_name') ?>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Date</label>
                  <input type="date" name="signed_date" class="<?= field_class($errors, 'signed_date') ?>" value="<?= old($clean, 'signed_date', date('Y-m-d')) ?>">
                  <?= field_error($errors, 'signed_date') ?>
                </div>
              </div>

              <div class="text-center mt-4">
                <button type="submit" class="btn btn-lg reg-submit-btn">Submit Registration</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <footer class="reg-footer">
    <div class="container text-center py-3">
      Copyright &copy; <?= date('Y') ?> - The Grove - All Rights Reserved
    </div>
  </footer>

</body>
</html>
