// src/components/SignUpPage.js
import React, { useState } from "react";
import "./SignUpPage.css";

const SignUpPage = () => {
  // Local state for the form fields
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");

  // Handle input changes
  const handleUsernameChange = (event) => {
    setUsername(event.target.value);
  };

  const handlePasswordChange = (event) => {
    setPassword(event.target.value);
  };

  // Handle form submission
  const handleSubmit = (event) => {
    event.preventDefault(); // Prevent the default form submission

    // Perform the sign-up action (this could be an API call or any logic you want)
    console.log("Signing up with:", { username, password });

    // Example API call (uncomment if you want to use axios):
    // axios.post('/signup', { username, password })
    //   .then(response => {
    //     console.log('Signed up successfully', response);
    //   })
    //   .catch(error => {
    //     console.log('Sign up failed', error);
    //   });
  };

  return (
    <div>
      <h2>Sign Up Page</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label htmlFor="username">Username:</label>
          <input
            type="text"
            id="username"
            value={username}
            onChange={handleUsernameChange}
            min={8}
            max={40}
            required
            placeholder="Enter your username"
          />
        </div>
        <div>
          <label htmlFor="password">Password:</label>
          <input
            type="password"
            id="password"
            min={8}
            max={40}
            value={password}
            onChange={handlePasswordChange}
            required
            placeholder="Enter your password"
          />
        </div>
        <button type="submit">Sign Up</button>
      </form>
    </div>
  );
};

export default SignUpPage;
