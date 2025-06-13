export default class ApiService {
    constructor() {
        this.baseURL = window.location.origin;
    }

    get(endpoint, params = {}, headers = {}) {
        return axios.get(`${this.baseURL}${endpoint}`, {
            params: params,
            headers: {
                ...headers,
                'X-Requested-With': 'XMLHttpRequest' // Header comune per richieste AJAX
            }
        })
            .then(response => response.data)
            .catch(error => {
                console.error('GET Error:', error);
                throw error;
            });
    }

    post(endpoint, data = {}, headers = {}) {
        return axios.post(`${this.baseURL}${endpoint}`, data, {
            headers: {
                'Content-Type': 'application/json',
                ...headers,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then((response) => {
            return response.data;
        })
        .catch(error => {
            console.error('POST Error:', error);
            throw error;
        });
    }
    

    setDefaultHeader(header, value) {
        axios.defaults.headers.common[header] = value;
    }

    setBaseUrl(url) {
        this.baseURL = url;
    }
}
