const API_URL = import.meta.env.VITE_API_URL
const JWT_SECRET = import.meta.env.VITE_JWT_SECRET

export default{
    getEndpoint(path){
        return `${API_URL}/${path}`;
    },
    getJwtSecret() {
        return JWT_SECRET;
    }
}