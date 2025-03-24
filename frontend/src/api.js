const API_URL = import.meta.env.VITE_API_URL

export default{
    getEndpoint(path){
        return `${API_URL}/${path}`;
    }
}