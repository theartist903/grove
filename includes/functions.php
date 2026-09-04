<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function old(array $data, string $key, string $default = ''): string
{
    return e($data[$key] ?? $default);
}

function old_checked(array $data, string $key, string $value): string
{
    $current = $data[$key] ?? null;
    if (is_array($current)) {
        return in_array($value, $current, true) ? 'checked' : '';
    }
    return $current === $value ? 'checked' : '';
}

/** Bootstrap "is-invalid" class if the field has an error, appended to $base. */
function field_class(array $errors, string $key, string $base = 'form-control'): string
{
    return $base . (isset($errors[$key]) ? ' is-invalid' : '');
}

/** Bootstrap invalid-feedback markup for a field, or empty string if no error. */
function field_error(array $errors, string $key): string
{
    if (!isset($errors[$key])) {
        return '';
    }
    return '<div class="invalid-feedback d-block">' . e($errors[$key]) . '</div>';
}

/**
 * Validates the submitted registration form.
 * Returns [errors => array<string,string>, clean => array<string,mixed>]
 */
function validate_registration(array $post): array
{
    $errors = [];
    $clean = [];

    $requireText = function (string $field, string $label, int $maxLen = 150) use ($post, &$errors, &$clean) {
        $value = trim((string)($post[$field] ?? ''));
        if ($value === '') {
            $errors[$field] = "$label is required.";
        } elseif (mb_strlen($value) > $maxLen) {
            $errors[$field] = "$label is too long.";
        }
        $clean[$field] = $value;
    };

    $requireChoice = function (string $field, string $label, array $allowed) use ($post, &$errors, &$clean) {
        $value = trim((string)($post[$field] ?? ''));
        if (!in_array($value, $allowed, true)) {
            $errors[$field] = "Please select $label.";
        }
        $clean[$field] = $value;
    };

    // Child
    $requireText('child_name', "Child's name");
    $dob = trim((string)($post['child_dob'] ?? ''));
    $clean['child_dob'] = $dob;
    if ($dob === '') {
        $errors['child_dob'] = 'Date of birth is required.';
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$d || $d->format('Y-m-d') !== $dob) {
            $errors['child_dob'] = 'Enter a valid date of birth.';
        } elseif ($d > new DateTime()) {
            $errors['child_dob'] = 'Date of birth cannot be in the future.';
        }
    }
    $requireChoice('gender', 'the gender', ['Male', 'Female']);
    $requireChoice('birth_certificate', 'B-Form / Birth Certificate status', ['Available', 'Pending', 'N/A']);

    // Parents
    $requireText('parent1_name', 'Parent/Guardian 1 name');
    $requireText('parent1_mobile', 'Parent/Guardian 1 mobile', 30);
    $email1 = trim((string)($post['parent1_email'] ?? ''));
    $clean['parent1_email'] = $email1;
    if ($email1 === '' || !filter_var($email1, FILTER_VALIDATE_EMAIL)) {
        $errors['parent1_email'] = 'A valid email is required for Parent/Guardian 1.';
    }

    $clean['parent2_name'] = trim((string)($post['parent2_name'] ?? ''));
    $clean['parent2_mobile'] = trim((string)($post['parent2_mobile'] ?? ''));
    $email2 = trim((string)($post['parent2_email'] ?? ''));
    $clean['parent2_email'] = $email2;
    if ($email2 !== '' && !filter_var($email2, FILTER_VALIDATE_EMAIL)) {
        $errors['parent2_email'] = 'Enter a valid email for Parent/Guardian 2, or leave it blank.';
    }

    $requireText('home_address', 'Home address', 500);
    $requireChoice('child_lives_with', 'who the child lives with', ['Both parents', 'Mother', 'Father', 'Other']);

    $clean['first_language'] = trim((string)($post['first_language'] ?? ''));
    $clean['religion'] = trim((string)($post['religion'] ?? ''));

    // Yes/No + detail groups
    $yesNoGroup = function (string $field, string $detailField, string $label, array $allowed) use ($post, &$errors, &$clean) {
        $choice = trim((string)($post[$field] ?? ''));
        if (!in_array($choice, $allowed, true)) {
            $errors[$field] = "Please answer: $label.";
        }
        $clean[$field] = $choice;
        $detail = trim((string)($post[$detailField] ?? ''));
        $clean[$detailField] = $detail;
        if ($choice === 'Yes' && $detail === '') {
            $errors[$detailField] = 'Please provide details.';
        }
    };

    $yesNoGroup('has_allergies', 'allergies_detail', 'allergies / food allergies', ['None', 'Yes']);
    $yesNoGroup('has_medical_condition', 'medical_detail', 'medical condition / medication', ['None', 'Yes']);
    $yesNoGroup('has_additional_needs', 'additional_needs_detail', 'additional care / developmental needs', ['No', 'Yes']);
    $yesNoGroup('has_dietary_requirements', 'dietary_detail', 'dietary requirements', ['None', 'Yes']);

    // Attendance
    $requireChoice('package', 'the attendance / package', ['Full Day 7:30-5:30', '8 Hours', '6 Hours', '4 Hours']);

    $days = $post['days_required'] ?? [];
    if (!is_array($days)) {
        $days = [];
    }
    $allowedDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $days = array_values(array_intersect($allowedDays, $days));
    if (empty($days)) {
        $errors['days_required'] = 'Select at least one day.';
    }
    $clean['days_required'] = implode(',', $days);
    $clean['days_required_arr'] = $days;

    // Authorized collectors (all optional)
    for ($i = 1; $i <= 3; $i++) {
        $clean["collector{$i}_name"] = trim((string)($post["collector{$i}_name"] ?? ''));
        $clean["collector{$i}_relationship"] = trim((string)($post["collector{$i}_relationship"] ?? ''));
        $clean["collector{$i}_mobile"] = trim((string)($post["collector{$i}_mobile"] ?? ''));
        $clean["collector{$i}_regular"] = !empty($post["collector{$i}_regular"]) ? 1 : 0;
    }

    // Emergency contact
    $requireText('emergency_name', 'Emergency contact name');
    $requireText('emergency_relationship', 'Emergency contact relationship', 100);
    $requireText('emergency_mobile', 'Emergency contact mobile', 30);

    // Important info
    $yesNoGroup('has_important_info', 'important_info_detail', 'important family / social information', ['None', 'Yes']);

    // Consent
    $clean['consent_first_aid'] = !empty($post['consent_first_aid']) ? 1 : 0;
    $clean['consent_activities'] = !empty($post['consent_activities']) ? 1 : 0;
    $clean['consent_communication'] = !empty($post['consent_communication']) ? 1 : 0;
    $clean['consent_photos'] = !empty($post['consent_photos']) ? 1 : 0;

    if (!$clean['consent_first_aid']) {
        $errors['consent_first_aid'] = 'This consent is required to register.';
    }
    if (!$clean['consent_activities']) {
        $errors['consent_activities'] = 'This consent is required to register.';
    }
    if (!$clean['consent_communication']) {
        $errors['consent_communication'] = 'This consent is required to register.';
    }
    // consent_photos is optional (opt-in only).

    // Signature
    $requireText('signed_name', 'Parent/Guardian name (signature)');
    $signedDate = trim((string)($post['signed_date'] ?? ''));
    $clean['signed_date'] = $signedDate;
    if ($signedDate === '') {
        $errors['signed_date'] = "Please enter today's date.";
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $signedDate);
        if (!$d || $d->format('Y-m-d') !== $signedDate) {
            $errors['signed_date'] = 'Enter a valid date.';
        }
    }

    return ['errors' => $errors, 'clean' => $clean];
}
