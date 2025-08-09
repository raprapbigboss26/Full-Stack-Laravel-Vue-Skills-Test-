import { io } from 'socket.io-client'

class SocketService {
  constructor() {
    this.socket = null
    this.isConnected = false
    this.eventListeners = new Map()
    this.isAdmin = false
  }

  connect() {
    if (this.socket && this.socket.connected) {
      console.log('Socket already connected:', this.socket.id)
      return this.socket
    }

    // Disconnect existing socket if any
    if (this.socket) {
      console.log('Disconnecting existing socket')
      this.socket.disconnect()
    }

    console.log('Creating new socket connection...')
    this.socket = io('http://localhost:3001', {
      transports: ['websocket', 'polling'],
      autoConnect: true,
      reconnection: true,
      reconnectionDelay: 1000,
      reconnectionAttempts: 5,
      forceNew: true,
      cors: {
        origin: "http://localhost:5173",
        credentials: true
      }
    })

    this.socket.on('connect', () => {
      console.log('✅ Connected to socket server:', this.socket.id)
      this.isConnected = true
      
      // Re-join admin room if user was admin
      if (this.isAdmin) {
        console.log('Re-joining admin room...')
        this.joinAdminRoom()
      }
      
      // Re-attach all event listeners
      console.log('Re-attaching event listeners...')
      this.reattachEventListeners()
    })

    this.socket.on('disconnect', () => {
      console.log('❌ Disconnected from socket server')
      this.isConnected = false
    })

    this.socket.on('connect_error', (error) => {
      console.error('❌ Socket connection error:', error)
      this.isConnected = false
    })

    this.socket.on('reconnect', () => {
      console.log('🔄 Reconnected to socket server')
      this.isConnected = true
    })

    // Add test event listener to verify connection
    this.socket.on('task-status-updated', (data) => {
      console.log('🔥 Socket service received task-status-updated:', data)
    })

    return this.socket
  }

  disconnect() {
    if (this.socket) {
      this.socket.disconnect()
      this.socket = null
      this.isConnected = false
      this.eventListeners.clear()
    }
  }

  reattachEventListeners() {
    // Re-attach all stored event listeners
    for (const [eventName, callback] of this.eventListeners) {
      this.socket.on(eventName, callback)
    }
  }

  // Admin methods
  joinAdminRoom() {
    this.isAdmin = true
    if (this.socket && this.socket.connected) {
      this.socket.emit('join-admin')
      console.log('Joined admin room')
    }
  }

  // Listen for task updates (for admin and users)
  onTaskUpdated(callback) {
    if (this.socket) {
      this.socket.on('task-updated', callback)
      this.eventListeners.set('task-updated', callback)
    }
  }

  onTaskStatusUpdated(callback) {
    if (this.socket) {
      this.socket.on('task-status-updated', callback)
      this.eventListeners.set('task-status-updated', callback)
    }
  }

  onTaskCreated(callback) {
    if (this.socket) {
      this.socket.on('task-created', callback)
      this.eventListeners.set('task-created', callback)
    }
  }

  onTaskDeleted(callback) {
    if (this.socket) {
      this.socket.on('task-deleted', callback)
      this.eventListeners.set('task-deleted', callback)
    }
  }

  onTaskUpdatedData(callback) {
    if (this.socket) {
      this.socket.on('task-updated-data', callback)
      this.eventListeners.set('task-updated-data', callback)
    }
  }

  // Emit events (for users)
  emitTaskStatusUpdate(taskData) {
    if (this.socket && this.isConnected) {
      this.socket.emit('task-status-updated', taskData)
      console.log('Emitted task status update:', taskData)
    }
  }

  emitTaskCreated(taskData) {
    if (this.socket && this.isConnected) {
      this.socket.emit('task-created', taskData)
      console.log('Emitted task created:', taskData)
    }
  }

  emitTaskDeleted(taskData) {
    if (this.socket && this.isConnected) {
      this.socket.emit('task-deleted', taskData)
      console.log('Emitted task deleted:', taskData)
    }
  }

  emitTaskUpdated(taskData) {
    if (this.socket && this.isConnected) {
      this.socket.emit('task-updated-data', taskData)
      console.log('Emitted task updated:', taskData)
    }
  }

  // Remove listeners
  removeAllListeners() {
    if (this.socket) {
      this.socket.removeAllListeners('task-updated')
      this.socket.removeAllListeners('task-status-updated')
      this.socket.removeAllListeners('task-created')
      this.socket.removeAllListeners('task-deleted')
      this.socket.removeAllListeners('task-updated-data')
    }
    // Clear stored event listeners
    this.eventListeners.clear()
  }
}

export const socketService = new SocketService()
export default socketService
