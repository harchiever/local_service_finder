const express = require("express");
const cors = require("cors");

const app = express();
const PORT = 3000;

app.use(cors());
app.use(express.json());

app.get("/", (req, res) => {
  res.send("Server chal raha hai 🚀");
});

app.post("/submit", (req, res) => {
  console.log(req.body);
  res.json({ message: "Data received" });
});

app.listen(PORT, () => {
  console.log(`Server running on http://localhost:${PORT}`);
});