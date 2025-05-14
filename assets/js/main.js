// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Document ready!');
    
    // Add your JavaScript code here
});

// Utility functions
function showMessage(message) {
    alert(message);
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    // Add your form validation logic here
    return true;
} 