<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Help</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>

<body>

    <header class="main-header">
        <a href="homepage.php" class="logo">PARK U</a>
        <nav class="main-nav">
            <a href="homepage.php">Home</a>
            <a href="profile.php">My Profile</a>
            <a href="vehicle.php">My Vehicle</a>
            <a href="reservations.php">My Reservations</a>
            <a href="archived.php">Archived</a>
            <a href="help.php" class="active-link">Help</a>
            <a href="login.php?action=logout">Sign Out</a>
        </nav>
    </header>

    <div class="app-container">

        <section class="welcome-panel">
            <h2>HELP</h2>
            <p>Find quick answers to common questions about parking reservations.</p>
        </section>

        <section class="reservation-panel faq-panel"> 
            <h3>Frequently Asked Questions (FAQ)</h3>
            
            <details class="faq-item">
                <summary class="faq-question">How do I register my vehicle?</summary>
                <div class="faq-answer">
                    <p>Go to the <strong>My Vehicle</strong> page from the navigation bar. Fill in your Plate Number and Vehicle Type, then click the <strong>Register Vehicle</strong> button. Your vehicle will be automatically saved for future reservations.</p>
                </div>
            </details>

            <details class="faq-item">
                <summary class="faq-question">Can I reserve a parking spot in real-time?</summary>
                <div class="faq-answer">
                    <p>Yes. On the <strong>Homepage</strong>, select the desired date and time, and click <strong>Check Availability</strong>. The system will show you all available spots in real-time, allowing you to select and reserve your preferred space.</p>
                </div>
            </details>
            
            <details class="faq-item">
                <summary class="faq-question">How do I cancel an upcoming reservation?</summary>
                <div class="faq-answer">
                    <p>Navigate to the <strong>My Reservations</strong> page. Under the <strong>Upcoming Reservations</strong> section, find the booking you wish to cancel and click the <strong>Cancel Reservation</strong> button. A confirmation message will appear.</p>
                </div>
            </details>
            
            <details class="faq-item">
                <summary class="faq-question">What do I do if I forget my Student ID or Password?</summary>
                <div class="faq-answer">
                    <p>Please contact the CHMSU IT Help Desk or the campus security office directly for Student ID recovery or a password reset. For security purposes, the parking app does not handle these credentials directly.</p>
                </div>
            </details>

        </section>
        
        <section class="reservation-panel contact-info">
            <h3>Need Further Assistance?</h3>
            <p>If your question isn't answered in the FAQ, please contact our support team:</p>
            <ul>
                <li>Email: robert0906@gmail.com</li>
                <li>Phone: +639 0123 456 789 (Campus Security)</li>
                <li>Office: Located at the Main Gate Security Office</li>
            </ul>
        </section>
    </div>

</body>
</html>