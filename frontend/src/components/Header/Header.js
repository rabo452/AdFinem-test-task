// src/components/Header.js
import React from "react";
import { Link } from "react-router-dom";
import "./Header.css";
import ApiStorage from "../../api/apiStorage";
import { useNavigate } from 'react-router-dom';


const Header = () => {
  const navigate = useNavigate(); // Correct placement of useNavigate hook

  const logOutOnClick = e => {
    ApiStorage.deleteJWT();
    navigate('/login');
  }

  if (ApiStorage.jwt) {
    return (
      <header>
        <nav>
          <ul>
            <li>
              <Link to="/">Main</Link>
            </li>
            <li>
              <a style={{cursor: 'pointer'}} onClick={logOutOnClick}>Log out</a>
            </li>
          </ul>
        </nav>
      </header>
    )
  }

  return (
    <header>
      <nav>
        <ul>
          <li>
            <Link to="/">Main</Link>
          </li>
          <li>
            <Link to="/login">Login</Link>
          </li>
          <li>
            <Link to="/signup">Sign Up</Link>
          </li>
        </ul>
      </nav>
    </header>
  );
};

export default Header;