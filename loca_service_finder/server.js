const express = require("express");
const cors = require("cors");

const app = express();
const PORT = 3000;

app.use(cors());
app.use(express.json());

// Test route
app.get("/", (req, res) => {
  res.send("Server chal raha hai 🚀");
});

// Form data receive
app.post("/submit", (req, res) => {
  const { name, service } = req.body;

  console.log("Name:", name);
  console.log("Service:", service);

  res.json({ message: "Data received successfully" });
});

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});