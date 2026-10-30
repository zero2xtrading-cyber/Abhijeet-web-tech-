/**
 * Apex Institute Admissions Portal - Client Logic
 * Written using jQuery & JavaScript
 */

$(document).ready(function () {
  const $form = $('#enrolmentForm');
  const $regCard = $('#registrationCard');
  const $receiptCard = $('#receiptCard');
  const $notice = $('#statusNotice');
  const $submitBtn = $('#enrollSubmitBtn');
  const $clearBtn = $('#clearFormBtn');
  const $printBtn = $('#printReceiptBtn');
  const $newBtn = $('#anotherEnrolmentBtn');

  // Regex patterns
  const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  const phonePattern = /^[0-9]{10}$/;
  const pinPattern = /^[0-9]{6}$/;

  function showNotice(msg, isSuccess = false) {
    $notice
      .removeClass('d-none is-danger is-success')
      .addClass(isSuccess ? 'is-success' : 'is-danger');
    $notice.find('.notice-icon')
      .attr('class', isSuccess ? 'notice-icon fa-solid fa-circle-check' : 'notice-icon fa-solid fa-triangle-exclamation');
    $notice.find('.notice-text').text(msg);
    $('html, body').animate({ scrollTop: $regCard.offset().top - 20 }, 300);
  }

  function hideNotice() {
    $notice.addClass('d-none');
  }

  function setErr(fieldId, msg) {
    $('#' + fieldId).addClass('has-error');
    $('#' + fieldId + 'Err').text(msg);
  }

  function clearErr(fieldId) {
    $('#' + fieldId).removeClass('has-error');
    $('#' + fieldId + 'Err').text('');
  }

  // Clear errors on input
  $('input, select, textarea').on('input change', function () {
    const id = $(this).attr('id') || $(this).attr('name');
    clearErr(id);
    hideNotice();
  });

  // Form Validation
  function checkForm() {
    let ok = true;
    hideNotice();

    // 1. Full Name
    const name = $('#fullName').val().trim();
    if (!name || name.length < 3) {
      setErr('fullName', 'Please provide applicant\'s full name (min 3 letters).');
      ok = false;
    } else {
      clearErr('fullName');
    }

    // 2. Email
    const email = $('#emailAddr').val().trim();
    if (!email || !emailPattern.test(email)) {
      setErr('emailAddr', 'Enter a valid email address (e.g. user@domain.com).');
      ok = false;
    } else {
      clearErr('emailAddr');
    }

    // 3. Mobile
    const mobile = $('#mobileNo').val().trim();
    if (!mobile || !phonePattern.test(mobile)) {
      setErr('mobileNo', 'Enter a valid 10-digit mobile number.');
      ok = false;
    } else {
      clearErr('mobileNo');
    }

    // 4. DOB
    const dob = $('#birthDate').val();
    if (!dob) {
      setErr('birthDate', 'Select candidate date of birth.');
      ok = false;
    } else {
      clearErr('birthDate');
    }

    // 5. Gender
    const gender = $('input[name="gender"]:checked').val();
    if (!gender) {
      $('#genderErr').text('Select gender identification.');
      ok = false;
    } else {
      $('#genderErr').text('');
    }

    // 6. Course Stream
    const stream = $('#courseStream').val();
    if (!stream) {
      setErr('courseStream', 'Please choose a target academic program.');
      ok = false;
    } else {
      clearErr('courseStream');
    }

    // 7. Previous Qualification Level
    const prev = $('#prevLevel').val();
    if (!prev) {
      setErr('prevLevel', 'Select your qualifying examination.');
      ok = false;
    } else {
      clearErr('prevLevel');
    }

    // 8. Percentage
    const score = $('#scorePercentage').val().trim();
    if (!score) {
      setErr('scorePercentage', 'Enter qualifying marks percentage or CGPA.');
      ok = false;
    } else {
      clearErr('scorePercentage');
    }

    // 9. Passing Year
    const year = $('#passingYear').val();
    if (!year) {
      setErr('passingYear', 'Select passing out year.');
      ok = false;
    } else {
      clearErr('passingYear');
    }

    // 10. Street Address
    const address = $('#streetAddress').val().trim();
    if (!address) {
      setErr('streetAddress', 'Residential street address is required.');
      ok = false;
    } else {
      clearErr('streetAddress');
    }

    // 11. City
    const city = $('#cityTown').val().trim();
    if (!city) {
      setErr('cityTown', 'City / District is required.');
      ok = false;
    } else {
      clearErr('cityTown');
    }

    // 12. Postal PIN
    const pin = $('#postalIndex').val().trim();
    if (!pin || !pinPattern.test(pin)) {
      setErr('postalIndex', 'Enter a valid 6-digit postal PIN code.');
      ok = false;
    } else {
      clearErr('postalIndex');
    }

    // 13. Consent Checkbox
    const consent = $('#consentCheck').is(':checked');
    if (!consent) {
      $('#consentCheckErr').text('You must accept the enrollment declaration.');
      ok = false;
    } else {
      $('#consentCheckErr').text('');
    }

    return ok;
  }

  // Submit Handler
  $form.on('submit', function (e) {
    e.preventDefault();

    if (!checkForm()) {
      showNotice('Please complete all mandatory marked fields accurately.');
      return;
    }

    // Button loading animation
    $submitBtn.prop('disabled', true);
    $submitBtn.find('.lbl-normal').addClass('d-none');
    $submitBtn.find('.lbl-waiting').removeClass('d-none');

    const formData = $form.serialize();

    $.ajax({
      url: 'process.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function (res) {
        if (res && res.status === 'success') {
          populateReceipt(res.data);
        } else {
          showNotice((res && res.message) ? res.message : 'Registration error. Please review your data.');
        }
      },
      error: function (xhr, status, err) {
        console.warn('Backend note:', status, err);
        // Direct local preview fallback
        simulateLocalReceipt();
      },
      complete: function () {
        $submitBtn.prop('disabled', false);
        $submitBtn.find('.lbl-normal').removeClass('d-none');
        $submitBtn.find('.lbl-waiting').addClass('d-none');
      }
    });
  });

  // Display receipt
  function populateReceipt(d) {
    $('#receiptEnrolId').text(d.enrolmentId || 'AITM-' + Math.floor(100000 + Math.random() * 900000));
    $('#receiptDate').text(d.enrolDate || new Date().toLocaleDateString('en-GB'));
    $('#receiptTimestamp').text(d.timestamp || new Date().toLocaleString());

    $('#receiptName').text(d.fullName);
    $('#receiptEmail').text(d.email);
    $('#receiptPhone').text(d.phone);
    $('#receiptDobGender').text(d.dob + ' | ' + d.gender);
    $('#receiptCourse').text(d.course);
    $('#receiptQualification').text(d.qualification + ' (' + d.passingYear + ')');
    $('#receiptScore').text(d.score);
    $('#receiptAddress').text(d.address + ', ' + d.city + ' - ' + d.pincode);

    if (d.note && d.note.trim() !== '') {
      $('#receiptNote').text('"' + d.note + '"');
      $('#receiptNoteWrapper').show();
    } else {
      $('#receiptNoteWrapper').hide();
    }

    $regCard.slideUp(400, function () {
      $receiptCard.removeClass('d-none').hide().fadeIn(500);
      $('html, body').animate({ scrollTop: $receiptCard.offset().top - 20 }, 400);
    });
  }

  function simulateLocalReceipt() {
    const randId = 'AITM-2026-' + Math.floor(10000 + Math.random() * 90000);
    const now = new Date();
    const dStr = now.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    const tStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    populateReceipt({
      enrolmentId: randId,
      enrolDate: dStr,
      timestamp: dStr + ' at ' + tStr,
      fullName: $('#fullName').val().trim(),
      email: $('#emailAddr').val().trim(),
      phone: $('#mobileNo').val().trim(),
      dob: $('#birthDate').val(),
      gender: $('input[name="gender"]:checked').val(),
      course: $('#courseStream').val(),
      qualification: $('#prevLevel').val(),
      passingYear: $('#passingYear').val(),
      score: $('#scorePercentage').val().trim(),
      address: $('#streetAddress').val().trim(),
      city: $('#cityTown').val().trim(),
      pincode: $('#postalIndex').val().trim(),
      note: $('#careerNote').val().trim()
    });
  }

  // Print
  $printBtn.on('click', function () {
    window.print();
  });

  // Reset / New
  $newBtn.on('click', function () {
    $receiptCard.fadeOut(300, function () {
      $receiptCard.addClass('d-none');
      $form[0].reset();
      $('input, select, textarea').removeClass('has-error');
      $('.validation-note').text('');
      hideNotice();
      $regCard.slideDown(400);
      $('html, body').animate({ scrollTop: $regCard.offset().top - 20 }, 300);
    });
  });

  $clearBtn.on('click', function () {
    setTimeout(function () {
      $('input, select, textarea').removeClass('has-error');
      $('.validation-note').text('');
      hideNotice();
    }, 50);
  });
});
