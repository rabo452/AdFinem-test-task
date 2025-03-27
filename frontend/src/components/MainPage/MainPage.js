import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import ApiStorage from "../../api/apiStorage"; // Import your ApiStorage for JWT
import axiosInstance from "../../api/axiosInstance";
import "./MainPage.css";

const MainPage = () => {
  // Mapping task status from string to code and vice versa
  const taskStatusToCode = {
    "pending": 1,
    "in progress": 2,
    "finished": 3,
  };

  const codeToTaskStatus = {
    1: "pending",
    2: "in progress",
    3: "finished",
  };

  // State to manage tasks and new task input
  const [tasks, setTasks] = useState([]);
  const [newTask, setNewTask] = useState({
    title: "",
    description: "",
    status: "pending",
  });
  const [editId, setEditId] = useState(null); // State for tracking the task being edited

  const navigate = useNavigate(); // For navigation

  // Check if the user is authorized to access tasks
  useEffect(() => {
    const token = ApiStorage.jwt; // Check for JWT token
    if (!token) {
      alert("You need to be authorized to add/see tasks!");
      // If no token is found, redirect to the login page
      navigate("/login");
      return;
    }

    // Fetch the tasks from the backend
    axiosInstance.get('/tasks')
      .then((res) => {
        const tasks = res.data.map((task) => ({
          id: task.id,
          title: task.title,
          description: task.description,
          status: codeToTaskStatus[task.status], // Convert status code to string
        }));
        setTasks(tasks); // Set the tasks in state
      })
      .catch((err) => console.log(err)); // Log any errors
  }, [navigate]); // The effect will run when `navigate` changes (e.g., on login)

  // Handle input changes for title, description, and status
  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setNewTask({
      ...newTask,
      [name]: value, // Update the corresponding field in state
    });
  };

  // Handle form submission to add a new task or update an existing one
  const handleSubmit = (e) => {
    e.preventDefault(); // Prevent page refresh on form submission
    if (newTask.title) { // Ensure title is not empty
      if (editId !== null) { // Check if we're editing an existing task
        // Update task with PUT request
        axiosInstance.put(`/tasks/${editId}`, {
          title: newTask.title,
          description: newTask.description,
          status: taskStatusToCode[newTask.status], // Convert status to code
        })
          .then(() => {
            // Update the tasks in state with the modified task
            const updatedTasks = tasks.map((task) =>
              task.id === editId ? newTask : task
            );
            setTasks(updatedTasks);
            setEditId(null); // Reset edit mode
          });
      } else {
        // Add new task with POST request
        axiosInstance.post('/tasks', {
          title: newTask.title,
          description: newTask.description,
          status: taskStatusToCode[newTask.status], // Convert status to code
        })
          .then((res) => {
            const task = { ...res.data, status: codeToTaskStatus[res.data.status] }; // Convert response status code to string
            setTasks([...tasks, task]); // Add new task to the list
            setNewTask({ title: "", description: "", status: "pending" }); // Reset form
          });
      }
    }
  };

  // Handle edit button click, populating the form with the task data to edit
  const handleEdit = (id) => {
    const taskToEdit = tasks.find((task) => task.id === id); // Find task by id
    setNewTask(taskToEdit); // Set form data to task to edit
    setEditId(id); // Set editId to indicate editing mode
  };

  // Handle delete button click, removing a task
  const handleDelete = (deletedId) => {
    axiosInstance.delete(`/tasks/${deletedId}`)
      .then(() => {
        // Remove task from the state after deletion
        const updatedTasks = tasks.filter((task) => task.id !== deletedId);
        setTasks(updatedTasks);
      });
  };

  return (
    <div>
      <h2>Main Page - Task App</h2>
      <form onSubmit={handleSubmit} className="task-form">
        <div>
          <label htmlFor="title">Title:</label>
          <input
            type="text"
            id="title"
            name="title"
            value={newTask.title}
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
            value={newTask.description}
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
            value={newTask.status}
            onChange={handleInputChange}
          >
            <option value="pending">Pending</option>
            <option value="in progress">In Progress</option>
            <option value="finished">Finished</option>
          </select>
        </div>
        <button type="submit">
          {editId !== null ? "Update Task" : "Add Task"}
        </button>
      </form>

      <center>
        <h3>Task List:</h3>
      </center>
      <div className="task-container">
        {tasks.length === 0 ? (
          <p>No tasks yet. Add one!</p>
        ) : (
          tasks.map((task) => (
            <div className="task" key={task.id}>
              <h4 className="task-title">{task.title}</h4>
              <p className="task-description">{task.description}</p>
              <span className={`status ${task.status.replace(" ", "_")}`}>
                {task.status}
              </span>
              <button className="edit-button" onClick={() => handleEdit(task.id)}>
                Edit
              </button>
              <button
                className="delete-button"
                onClick={() => handleDelete(task.id)} // Handle delete
              >
                Delete
              </button>
            </div>
          ))
        )}
      </div>
    </div>
  );
};

export default MainPage;