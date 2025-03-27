const ApiStorage = {
    // Set JWT to localStorage
    set jwt(token) {
      localStorage.setItem('AUTH_JWT', token);
    },
  
    // Get JWT from localStorage
    get jwt() {
      return localStorage.getItem('AUTH_JWT'); 
    },

    deleteJWT() {
        localStorage.removeItem('AUTH_JWT');
    },
  };
  
  export default ApiStorage;
  