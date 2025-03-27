// src/components/LoginPage.js
import React, { useState } from "react";
import { useNavigate } from 'react-router-dom';
import axiosInstance from "../../api/axiosInstance";
import ApiStorage from "../../api/apiStorage";
import './LoginPage.css';

const LoginPage = () => {
  // Local state for the form fields
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const navigate = useNavigate(); // Correct placement of useNavigate hook

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

    // Make a POST request to login
    axiosInstance.post('/auth/login', { username, password })
      .then(res => {
        ApiStorage.jwt = res.data.jwt; // Set JWT to local storage
        navigate('/'); // Use navigate to redirect after successful sign-up
      })
      .catch(res => alert(res['message'])) // Handle errors
  };

  return (
    <div>
      <h2>Login Page</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label htmlFor="username">Username:</label>
          <input
            type="text"
            id="username"
            value={username}
            onChange={handleUsernameChange}
            max={40}
            min={8}
            required
            placeholder="Enter your username"
          />
        </div>
        <div>
          <label htmlFor="password">Password:</label>
          <input
            type="password"
            id="password"
            max={40}
            min={8}
            value={password}
            onChange={handlePasswordChange}
            required
            placeholder="Enter your password"
          />
        </div>
        <button type="submit">Login</button>
      </form>
    </div>
  );
};

export default LoginPage;