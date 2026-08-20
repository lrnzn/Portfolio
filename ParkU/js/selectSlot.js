document.addEventListener('DOMContentLoaded', () => {
    // --- Data from PHP ---
    const appData = window.APP_DATA || {};
    const { vehicleID, date, time, durationHours, baseCost, parkingName, previousSlotID, previousSlotNumber } = appData;

    const params = new URLSearchParams(window.location.search);
    const editReservationID = params.get('editReservationID');

    // Only select slots that are NOT occupied
    const slots = document.querySelectorAll('.slot-box:not(.occupied)');
    const selectedSlotDisplay = document.getElementById('selected-slot-display');
    const estimatedCostDisplay = document.getElementById('estimated-cost-display');
    const proceedButton = document.getElementById('proceed-button');
    
    let selectedSlotElement = null; // Store the actual element
    let selectedSlotNumber = null;
    let selectedDbSlotId = null; 
    
    // Helper function to format currency
    function formatCurrency(amount) {
        return `₱${parseFloat(amount).toFixed(2)}`;
    }

    // Initial state setup
    proceedButton.classList.add('disabled');
    proceedButton.setAttribute('aria-disabled', 'true');
    proceedButton.href = '#';

    slots.forEach(slot => {
        slot.addEventListener('click', (event) => {
            const currentSlot = event.currentTarget;
            const slotNumber = currentSlot.getAttribute('data-slot-number');
            const dbSlotId = currentSlot.getAttribute('data-db-slot-id');
            const originalClass = currentSlot.getAttribute('data-original-class'); // Added this attribute to PHP

            // 1. CLEAR PREVIOUS SELECTION
            if (selectedSlotElement) {
                // Restore the original class from the data attribute
                const prevOriginalClass = selectedSlotElement.getAttribute('data-original-class');
                selectedSlotElement.classList.remove('selected');
                
                // Crucial step: Ensure the old element keeps its original status class
                if (prevOriginalClass) {
                    selectedSlotElement.classList.add(prevOriginalClass);
                }
            }





            // 2. CHECK FOR DESELECTION (Clicking the already selected slot)
            if (currentSlot === selectedSlotElement) {
                // It was deselected, so clear state
                selectedSlotElement = null;
                selectedSlotNumber = null;
                selectedDbSlotId = null;
            } else {
                // 3. SELECT NEW SLOT
                currentSlot.classList.remove(originalClass); // Remove green/pwd background color class
                currentSlot.classList.add('selected'); // Add blue selection class
                
                selectedSlotElement = currentSlot;
                selectedSlotNumber = slotNumber;
                selectedDbSlotId = dbSlotId;
            }
            
            // 4. Update UI and Button
            updateSummary();
        });
    });

    function updateSummary() {
        if (selectedSlotNumber && selectedDbSlotId) {
            selectedSlotDisplay.textContent = selectedSlotNumber;
            estimatedCostDisplay.textContent = formatCurrency(baseCost);
            
            // Set parameters for the next page (payment.php)
            const queryParams = new URLSearchParams({
            vehicleID,
            date,
            time,
            duration: durationHours,
            slotID: selectedDbSlotId,
            slotNumber: selectedSlotNumber,
            parkingName: appData.parkingName,
            cost: parseFloat(baseCost).toFixed(2)
            });

            const params = new URLSearchParams(window.location.search);
            if (params.has('editReservationID')) {
                queryParams.append('editReservationID', params.get('editReservationID'));
            }


            // Proceed to payment.php with all details
            proceedButton.href = `payment.php?${queryParams}`;
            proceedButton.classList.remove('disabled');
            proceedButton.setAttribute('aria-disabled', 'false');

        } else {
            selectedSlotDisplay.textContent = 'None';
            estimatedCostDisplay.textContent =  `₱0.00`;
            proceedButton.href = '#';
            proceedButton.classList.add('disabled');
            proceedButton.setAttribute('aria-disabled', 'true');
        }
    }
    
    // Initialize the summary on load
    updateSummary(); 
});

if (window.APP_DATA.originalSlotID) {
    const prevSlot = document.querySelector(
        `.slot-box[data-db-slot-id="${window.APP_DATA.originalSlotID}"]`
    );

    if (prevSlot) {
        prevSlot.click(); // auto-select + update summary
    }
}

if (editReservationID && previousSlotID) {
    slots.forEach(slot => {
        const dbSlotId = slot.getAttribute('data-db-slot-id');

        if (parseInt(dbSlotId) === parseInt(previousSlotID)) {
            slot.classList.add('selected');

            selectedSlotElement = slot;
            selectedSlotNumber = previousSlotNumber;
            selectedDbSlotId = previousSlotID;

            updateSummary();
        }
    });
}