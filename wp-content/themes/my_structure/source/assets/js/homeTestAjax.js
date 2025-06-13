document.addEventListener('DOMContentLoaded', function() {
    console.log(var_test);

    document.getElementById('submitTest').addEventListener('click', function() {
        const apiService = new window.ApiService();

        apiService.get('/test')
        .then(response => {
            console.log('response:', response);
        })
        .catch(error => {
            console.error('Errore risposta ajax:', error);
        });
    })
});
