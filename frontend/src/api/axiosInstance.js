import axios from 'axios';
import { config } from './config';
import ApiStorage from './apiStorage';

// Create an Axios instance
const axiosInstance = axios.create({
  baseURL: config.serverUrl,
  timeout: 3 * 1000, // Timeout after 10 seconds
});

// Request interceptor
axiosInstance.interceptors.request.use(
    config => ({
        ...config,
        headers: {
            ...config.headers,
            ...(ApiStorage.jwt ? {'Authorization': `Bearer ${ApiStorage.jwt}`} : {}),
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    }),
    error => Promise.reject(error)
);

// Response interceptor
axiosInstance.interceptors.response.use(
  res => res,
  (error) => Promise.reject(error)
);

export default axiosInstance;
