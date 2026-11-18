document.getElementById('voteForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const phoneNumber = document.getElementById('phoneNumber').value;
    const votes = document.getElementById('votes').value;
    const amount = votes * 10; // KES 10 per vote

    fetch('/process_payment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ phoneNumber, amount, votes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Payment initiated. Please complete the payment on your phone.');
        } else {
            alert('Payment failed. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
