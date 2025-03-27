import React, { useState } from "react";
import "./MainPage.css";

const MainPage = () => {
  const [todos, setTodos] = useState([]);
  const [newTodo, setNewTodo] = useState({
    title: "",
    description: "",
    status: "pending",
  });
  const [editIndex, setEditIndex] = useState(null);

  // Handle input changes for title, description, and status
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNewTodo({
      ...newTodo,
      [name]: value,
    });
  };

  // Handle form submission to add new to-do or update an existing one
  const handleSubmit = (e) => {
    e.preventDefault();
    if (newTodo.title && newTodo.description) {
      if (editIndex !== null) {
        // Update the existing todo
        const updatedTodos = todos.map((todo, index) =>
          index === editIndex ? newTodo : todo
        );
        setTodos(updatedTodos);
        setEditIndex(null); // Reset edit mode
      } else {
        // Add a new todo
        setTodos([...todos, newTodo]);
      }
      setNewTodo({ title: "", description: "", status: "pending" });
    }
  };

  // Handle edit button click
  const handleEdit = (index) => {
    const todoToEdit = todos[index];
    setNewTodo(todoToEdit);
    setEditIndex(index); // Set the index of the todo being edited
  };

  return (
    <div>
      <h2>Main Page - Todo App</h2>
      <form onSubmit={handleSubmit} className="todo-form">
        <div>
          <label htmlFor="title">Title:</label>
          <input
            type="text"
            id="title"
            name="title"
            value={newTodo.title}
            onChange={handleInputChange}
            required
            placeholder="Enter title"
          />
        </div>
        <div>
          <label htmlFor="description">Description:</label>
          <input
            type="text"
            id="description"
            name="description"
            value={newTodo.description}
            onChange={handleInputChange}
            required
            placeholder="Enter description"
          />
        </div>
        <div>
          <label htmlFor="status">Status:</label>
          <select
            id="status"
            name="status"
            value={newTodo.status}
            onChange={handleInputChange}
          >
            <option value="pending">Pending</option>
            <option value="in progress">In Progress</option>
            <option value="finished">Finished</option>
          </select>
        </div>
        <button type="submit">{editIndex !== null ? "Update Todo" : "Add Todo"}</button>
      </form>

      <center>
        <h3>Todo List:</h3>
      </center>
      <div className="todo-container">
        {todos.length === 0 ? (
          <p>No todos yet. Add one!</p>
        ) : (
          todos.map((todo, index) => (
            <div className="todo" key={index}>
              <h4 className="todo-title">{todo.title}</h4>
              <p className="todo-description">{todo.description}</p>
              <span className={`status ${todo.status.replace(' ', '_')}`}>{todo.status}</span>
              <button className="edit-button" onClick={() => handleEdit(index)}>
                Edit
              </button>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

export default MainPage;
