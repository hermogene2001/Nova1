<div class="time-container">
    <h3>Current Time: <span id="current-time"></span></h3>
    <div class="message"></div>
</div>

<style>
    .time-container {
        text-align: center;
        background-color: #ffffff;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
    }
    h3 {
        font-size: 24px;
        margin-bottom: 10px;
    }
    #current-time {
        font-size: 32px;
        color: #007bff;
        font-weight: bold;
    }
    .message {
        margin-top: 10px;
        font-size: 18px;
        color: #555;
    }
</style>

<script>
    function updateTime(initialTime) {
        let now = new Date(initialTime);
        setInterval(() => {
            now.setSeconds(now.getSeconds() + 1);
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
        }, 1000);
    }

    function displayGreeting() {
        const hours = new Date().getHours();
        const message = document.querySelector('.message');
        if (hours >= 5 && hours < 12) {
            message.textContent = "Good Morning! Start your day with positivity!";
        } else if (hours >= 12 && hours < 17) {
            message.textContent = "Good Afternoon! Keep pushing through!";
        } else if (hours >= 17 && hours < 21) {
            message.textContent = "Good Evening! Hope you had a productive day!";
        } else {
            message.textContent = "Good Night! Take a well-deserved rest!";
        }
    }

    // Initialize
    displayGreeting();
    updateTime(<?php echo json_encode(date('Y-m-d\TH:i:s')); ?>);
</script>
