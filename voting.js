<script>
    function fetchNominees() {
        const searchQuery = document.getElementById('search').value;
        // Add AJAX request to fetch nominees based on search query
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_nominees.php?search=' + encodeURIComponent(searchQuery), true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                document.getElementById('nominees').innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    function vote(nomineeId) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'voting_process.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    // Reload the page to show updated vote counts
                    fetchNominees();
                } else {
                    console.error('Voting failed');
                }
            }
        };
        xhr.send('nominee_id=' + nomineeId);
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchNominees();
    });
</script>
