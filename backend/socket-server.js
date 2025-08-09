const { createServer } = require('http');
const { Server } = require('socket.io');

// Create HTTP server
const httpServer = createServer();

// Create Socket.IO server with CORS configuration
const io = new Server(httpServer, {
  cors: {
    origin: "http://localhost:5173", // Vue.js dev server
    methods: ["GET", "POST"],
    credentials: true
  }
});

// Handle socket connections
io.on('connection', (socket) => {
  console.log('User connected:', socket.id);

  // Handle user joining admin room
  socket.on('join-admin', () => {
    socket.join('admin-room');
    console.log('User joined admin room:', socket.id);
  });

  // Handle task status updates from users
  socket.on('task-status-updated', (data) => {
    console.log('Task status updated:', data);
    // Broadcast to ALL clients including the sender
    io.emit('task-status-updated', data);
  });

  // Handle task creation
  socket.on('task-created', (data) => {
    console.log('Task created:', data);
    io.emit('task-created', data);
  });

  // Handle task deletion
  socket.on('task-deleted', (data) => {
    console.log('Task deleted:', data);
    io.emit('task-deleted', data);
  });

  // Handle task updates
  socket.on('task-updated-data', (data) => {
    console.log('Task data updated:', data);
    io.emit('task-updated-data', data);
  });

  socket.on('disconnect', () => {
    console.log('User disconnected:', socket.id);
  });
});

// Start the server
const PORT = process.env.SOCKET_PORT || 3001;
httpServer.listen(PORT, () => {
  console.log(`Socket.IO server running on port ${PORT}`);
});

module.exports = io;
