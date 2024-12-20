<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "booking_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$services_sql = "SELECT * FROM Services";
$services_result = $conn->query($services_sql);

if (!$services_result) {
    die("Error fetching services: " . $conn->error);
}

$therapists_sql = "SELECT * FROM Users WHERE role = 'therapist'";
$therapists_result = $conn->query($therapists_sql);

if (!$therapists_result) {
    die("Error fetching therapists: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.css">
    <style>
        body {
            background: linear-gradient(to bottom, #FDF7F4, #8EB486, #997C70, #685752);
            color: #333;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            margin-top: 30px;
            background: #fff;
        }
        .card-body {
            padding: 20px;
        }
        #time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .time-slot {
            padding: 10px 20px;
            border-radius: 5px;
            margin: 5px;
            font-weight: bold;
            background-color: #8EB486;
            color: #fff;
            border: none;
            transition: transform 0.2s ease-in-out, background-color 0.3s;
        }
        .time-slot:hover {
            transform: scale(1.1);
            background-color: #997C70;
        }
        .time-slot.active {
            background-color: #997C70;
            font-weight: bold;
        }
        .payment-method {
            margin: 10px;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            transition: transform 0.2s, background-color 0.3s;
        }
        .payment-method:hover {
            transform: scale(1.1);
            background-color: #997C70;
            color: white;
        }
        .payment-method.active {
            background-color: #8EB486;
            color: white;
            font-weight: bold;
        }
        .navbar {
            background-color: #685752;
            background-color: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .navbar:hover {
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }
        .navbar-brand {
            font-weight: bold;
            color: #000;
            font-size: 1.5rem;
            transition: transform 0.3s ease, color 0.3s ease;
            margin-left: 50px;
        }
        .navbar-brand:hover {
            transform: scale(1.2);
            color: #ff69b4;
        }
    </style>
</head>
<body>
<div class="container">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Winter Spa</a>
        </div>
    </nav>
    <div class="card" id="step1">
        <div class="card-body">
            <h4>Select Service & Therapist</h4>
            <form id="bookingForm">
                <div class="mb-3">
                    <label for="service" class="form-label">Service</label>
                    <select id="service" class="form-select" required>
                        <option value="">Select a service</option>
                        <?php while ($service = $services_result->fetch_assoc()): ?>
                            <option value="<?= $service['service_id'] ?>" data-price="<?= $service['price'] ?>"><?= $service['service_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="therapist" class="form-label">Therapist</label>
                    <select id="therapist" class="form-select" required>
                        <option value="">Select a therapist</option>
                        <?php while ($therapist = $therapists_result->fetch_assoc()): ?>
                            <option value="<?= $therapist['user_id'] ?>"><?= $therapist['full_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="button" id="nextButton" class="btn btn-primary btn-next" disabled>Next</button>
            </form>
        </div>
    </div>

    <div class="card calendar-container" id="step2">
        <div class="card-body">
            <h4>Select Appointment Date</h4>
            <div id="calendar"></div>
            <p id="appointment-date-display" class="mt-3"></p>
            <button type="button" class="btn btn-secondary btn-previous">Previous</button>
        </div>
    </div>

    <div class="card time-container" id="step3">
        <div class="card-body">
            <h4>Select Appointment Time</h4>
            <div id="time-slots"></div>
            <p id="appointment-time-display" class="mt-3"></p>
            <button type="button" class="btn btn-secondary btn-previous-time">Previous</button>
            <button type="button" class="btn btn-primary btn-next-time">Next</button>
        </div>
    </div>

<div class="card payment-container" id="step4">
    <div class="card-body">
        <h4>Select Payment Method</h4>
        <div id="payment-options" class="d-flex justify-content-center">
            <button type="button" class="btn btn-outline-secondary payment-method" data-method="cash">Cash</button>
            <button type="button" class="btn btn-outline-secondary payment-method" data-method="credit_card">Credit Card</button>
            <button type="button" class="btn btn-outline-secondary payment-method" data-method="paypal">PayPal</button>
        </div>
        <p id="payment-method-display" class="mt-3"></p>
        <button type="button" class="btn btn-secondary btn-previous-time">Previous</button>
        <button type="button" class="btn btn-primary btn-next-payment" disabled>Next</button>
    </div>
</div>


<div class="card confirmation-container" id="step5">
    <div class="card-body">
        <h4>Confirm Appointment</h4>
        <p><strong>Service:</strong> <span id="service-summary"></span></p>
        <p><strong>Therapist:</strong> <span id="therapist-name"></span></p>
        <p><strong>Appointment Time:</strong> <span id="appointment-time"></span></p>
        <p><strong>Payment Method:</strong> <span id="payment-method-summary"></span></p>
        <button type="button" class="btn btn-secondary btn-previous-payment">Previous</button>
        <button type="button" class="btn btn-success" id="confirm-appointment">Confirm Appointment</button>
    </div>
</div>


    
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.2.0/fullcalendar.min.js"></script>
<script>
    $(document).ready(function () {
    let selectedService, selectedTherapist, selectedDate, selectedTime, serviceName, therapistName, paymentMethod;

    // Service & Therapist Selection working
    $('#service, #therapist').change(function () {
        selectedService = $('#service').val();
        selectedTherapist = $('#therapist').val();
        serviceName = $('#service option:selected').text();
        therapistName = $('#therapist option:selected').text();
        $('#nextButton').prop('disabled', !selectedService || !selectedTherapist);
    });

    // Next Button to Date Selection working disappear
    $('#nextButton').click(function () {
        $('#step1').hide();
        $('#step2').show();
    });

    // Calendar Setup working
    $('#calendar').fullCalendar({
        selectable: true,
        select: function (start) {
            selectedDate = start.format('YYYY-MM-DD');
            $('#appointment-date-display').text('Selected Date: ' + selectedDate);
            $('#step2').hide();
            $('#step3').show();
            loadAvailableTimeSlots();
        }
    });

    function loadAvailableTimeSlots() {
        const slots = Array.from({ length: 13 }, (_, i) => moment().hours(8 + i).minutes(0).format('HH:mm'));
        $('#time-slots').html(slots.map(slot =>
            `<button type="button" class="btn btn-outline-primary time-slot ${selectedTime === slot ? 'active' : ''}" data-time="${slot}">${slot}</button>`
        ).join(''));
    }

    // Time Slot Selection working
    $(document).on('click', '.time-slot', function () {
        $('.time-slot').removeClass('active');
        $(this).addClass('active');
        selectedTime = $(this).data('time');
        $('#appointment-time-display').text('Selected Time: ' + selectedTime);
    });

    $('.btn-next-time').click(function () {
        if (selectedDate && selectedTime) {
            $('#step3').hide();
            $('#step4').show();
        } else {
            alert('Please select a date and time.');
        }
    });

    // Payment Method Selection working
    $(document).on('click', '.payment-method', function () {
        $('.payment-method').removeClass('active');
        $(this).addClass('active');
        paymentMethod = $(this).data('method');
        $('#payment-method-display').text('Selected Payment Method: ' + $(this).text());
        $('.btn-next-payment').prop('disabled', false);
    });

    $('.btn-next-payment').click(function () {
        $('#step4').hide();
        $('#step5').show();
        // Update confirmation summary final
        $('#service-summary').text(serviceName);
        $('#therapist-name').text(therapistName);
        $('#appointment-time').text(selectedDate + ' at ' + selectedTime);
        $('#payment-method-summary').text(paymentMethod);
    });

    $('#confirm-appointment').click(function () {
    // Prepare the data to send to the server working
    const appointmentData = {
        service_id: selectedService,
        therapist_id: selectedTherapist,
        appointment_date: selectedDate,
        start_time: selectedTime,
        end_time: moment(selectedTime, 'HH:mm').add(1, 'hour').format('HH:mm'),
        payment_method: paymentMethod
    };

    // Send the data to the server working
    $.post('confirm_appointment.php', appointmentData, function (response) {
        try {
            var data = JSON.parse(response);
            if (data.status === 'success') {
                alert('Appointment confirmed successfully!');
                window.location.href = 'index.php';
            } else {
                alert('Failed to confirm appointment: ' + data.message);
            }
        } catch (error) {
            alert('Unexpected server response: ' + response);
        }
    }).fail(function () {
        alert('Error confirming appointment.');
    });
});


    // Navigation Back Buttons working
    $('.btn-previous-time').click(function () {
        $('#step3').hide();
        $('#step2').show();
    });

    $('.btn-previous-payment').click(function () {
        $('#step5').hide();
        $('#step4').show();
    });
});

</script>
</body>
</html>
