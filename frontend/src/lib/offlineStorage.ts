// src/lib/offlineStorage.ts
// Offline Storage Utility using IndexedDB
export class OfflineStorage {
  private dbName = 'HRDarussalamOfflineDB';
  private version = 2; // Increased version for schema updates
  private db: IDBDatabase | null = null;
  private isInitialized = false;

  constructor() {
    this.initDB().catch(console.error);
  }

  private async initDB(): Promise<void> {
    if (this.isInitialized) return;

    return new Promise((resolve, reject) => {
      const request = indexedDB.open(this.dbName, this.version);

      request.onerror = () => {
        console.error('Failed to open IndexedDB:', request.error);
        reject(request.error);
      };

      request.onsuccess = () => {
        this.db = request.result;
        this.isInitialized = true;
        console.log('IndexedDB opened successfully');
        resolve();
      };

      request.onupgradeneeded = (event) => {
        const db = (event.target as IDBOpenDBRequest).result;
        console.log(`Upgrading database to version ${this.version}`);

        // Create object stores for different data types
        if (!db.objectStoreNames.contains('userData')) {
          const userStore = db.createObjectStore('userData', { keyPath: 'id' });
          userStore.createIndex('email', 'email', { unique: false });
        }

        if (!db.objectStoreNames.contains('absensi')) {
          const absensiStore = db.createObjectStore('absensi', { keyPath: 'id', autoIncrement: true });
          absensiStore.createIndex('user_id', 'user_id', { unique: false });
          absensiStore.createIndex('tanggal', 'tanggal', { unique: false });
          absensiStore.createIndex('status', 'status', { unique: false });
        }

        if (!db.objectStoreNames.contains('cuti')) {
          const cutiStore = db.createObjectStore('cuti', { keyPath: 'id', autoIncrement: true });
          cutiStore.createIndex('user_id', 'user_id', { unique: false });
          cutiStore.createIndex('status', 'status', { unique: false });
          cutiStore.createIndex('tanggal_pengajuan', 'tanggal_pengajuan', { unique: false });
        }

        if (!db.objectStoreNames.contains('evaluasi')) {
          const evaluasiStore = db.createObjectStore('evaluasi', { keyPath: 'id', autoIncrement: true });
          evaluasiStore.createIndex('user_id', 'user_id', { unique: false });
          evaluasiStore.createIndex('kategori_id', 'kategori_id', { unique: false });
          evaluasiStore.createIndex('periode', 'periode', { unique: false });
        }

        if (!db.objectStoreNames.contains('syncQueue')) {
          const syncStore = db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
          syncStore.createIndex('timestamp', 'timestamp', { unique: false });
          syncStore.createIndex('action', 'action', { unique: false });
          syncStore.createIndex('status', 'status', { unique: false });
          syncStore.createIndex('endpoint', 'endpoint', { unique: false });
        }

        if (!db.objectStoreNames.contains('appData')) {
          db.createObjectStore('appData', { keyPath: 'key' });
        }

        if (!db.objectStoreNames.contains('cache')) {
          const cacheStore = db.createObjectStore('cache', { keyPath: 'key' });
          cacheStore.createIndex('expires', 'expires', { unique: false });
        }
      };

      request.onblocked = () => {
        console.warn('Database upgrade blocked - please close other tabs');
      };
    });
  }

  private async ensureDB(): Promise<IDBDatabase> {
    if (!this.isInitialized) {
      await this.initDB();
    }
    if (!this.db) {
      throw new Error('Database not initialized');
    }
    return this.db;
  }

  // Retry mechanism for failed operations
  private async withRetry<T>(operation: () => Promise<T>, retries = 3): Promise<T> {
    try {
      return await operation();
    } catch (error) {
      if (retries > 0) {
        console.log(`Retrying operation, ${retries} attempts left`);
        // Reinitialize DB on error
        this.isInitialized = false;
        await new Promise(resolve => setTimeout(resolve, 1000));
        return this.withRetry(operation, retries - 1);
      }
      throw error;
    }
  }

  // Generic method to add data to a store
  async addData(storeName: string, data: any): Promise<any> {
    return this.withRetry(async () => {
      const db = await this.ensureDB();
      
      return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.add(data);

        request.onsuccess = () => {
          console.log(`Data added to ${storeName}:`, data);
          resolve(request.result);
        };

        request.onerror = () => {
          console.error(`Failed to add data to ${storeName}:`, request.error);
          reject(request.error);
        };
      });
    });
  }

  // Generic method to get all data from a store
  async getAllData(storeName: string): Promise<any[]> {
    return this.withRetry(async () => {
      const db = await this.ensureDB();
      
      return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.getAll();

        request.onsuccess = () => {
          resolve(request.result);
        };

        request.onerror = () => {
          console.error(`Failed to get data from ${storeName}:`, request.error);
          reject(request.error);
        };
      });
    });
  }

  // Generic method to get data by ID
  async getDataById(storeName: string, id: string | number): Promise<any> {
    return this.withRetry(async () => {
      const db = await this.ensureDB();
      
      return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readonly');
        const store = transaction.objectStore(storeName);
        const request = store.get(id);

        request.onsuccess = () => {
          resolve(request.result);
        };

        request.onerror = () => {
          console.error(`Failed to get data from ${storeName}:`, request.error);
          reject(request.error);
        };
      });
    });
  }

  // Generic method to update data
  async updateData(storeName: string, data: any): Promise<any> {
    return this.withRetry(async () => {
      const db = await this.ensureDB();
      
      return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.put(data);

        request.onsuccess = () => {
          console.log(`Data updated in ${storeName}:`, data);
          resolve(request.result);
        };

        request.onerror = () => {
          console.error(`Failed to update data in ${storeName}:`, request.error);
          reject(request.error);
        };
      });
    });
  }

  // Generic method to delete data
  async deleteData(storeName: string, id: string | number): Promise<void> {
    return this.withRetry(async () => {
      const db = await this.ensureDB();
      
      return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.delete(id);

        request.onsuccess = () => {
          console.log(`Data deleted from ${storeName}:`, id);
          resolve();
        };

        request.onerror = () => {
          console.error(`Failed to delete data from ${storeName}:`, request.error);
          reject(request.error);
        };
      });
    });
  }

  // Clear all data from a store
  async clearStore(storeName: string): Promise<void> {
    return this.withRetry(async () => {
      const db = await this.ensureDB();
      
      return new Promise((resolve, reject) => {
        const transaction = db.transaction([storeName], 'readwrite');
        const store = transaction.objectStore(storeName);
        const request = store.clear();

        request.onsuccess = () => {
          console.log(`Store ${storeName} cleared`);
          resolve();
        };

        request.onerror = () => {
          console.error(`Failed to clear store ${storeName}:`, request.error);
          reject(request.error);
        };
      });
    });
  }

  // Save user data
  async saveUserData(userData: any): Promise<void> {
    const userDataWithId = { ...userData, id: 'currentUser' };
    await this.updateData('userData', userDataWithId);
    if (typeof window !== 'undefined') {
      localStorage.setItem('lastSyncTime', new Date().toISOString());
    }
  }

  // Get user data
  async getUserData(): Promise<any> {
    return await this.getDataById('userData', 'currentUser');
  }

  // Save last login user data (specifically for offline display)
  async saveLastLoginUser(userData: any): Promise<void> {
    const lastLoginData = {
      ...userData,
      id: 'lastLogin',
      loginTime: new Date().toISOString(),
      savedAt: new Date().toISOString()
    };
    await this.updateData('userData', lastLoginData);
    if (typeof window !== 'undefined') {
      localStorage.setItem('lastLoginTime', lastLoginData.loginTime);
    }
  }

  // Get last login user data
  async getLastLoginUser(): Promise<any> {
    return await this.getDataById('userData', 'lastLogin');
  }

  // Save user attendance data for offline access
  async saveUserAttendance(attendanceData: any[]): Promise<void> {
    const attendanceWithId = {
      id: 'userAttendance',
      data: attendanceData,
      lastUpdated: new Date().toISOString()
    };
    await this.updateData('appData', attendanceWithId);
  }

  // Get user attendance data
  async getUserAttendance(): Promise<any[]> {
    const attendanceRecord = await this.getDataById('appData', 'userAttendance');
    return attendanceRecord ? attendanceRecord.data : [];
  }

  // Save last login user with attendance (comprehensive method)
  async saveLastLoginSession(userData: any, attendanceData: any[] = []): Promise<void> {
    await this.saveLastLoginUser(userData);
    if (attendanceData.length > 0) {
      await this.saveUserAttendance(attendanceData);
    }
    console.log('[OfflineStorage] Last login session saved:', {
      user: userData.email,
      attendanceCount: attendanceData.length,
      savedAt: new Date().toISOString()
    });
  }

  // Get complete last login session
  async getLastLoginSession(): Promise<{
    user: any;
    attendance: any[];
    loginTime: string;
  } | null> {
    try {
      const user = await this.getLastLoginUser();
      const attendance = await this.getUserAttendance();
      
      if (user) {
        return {
          user,
          attendance,
          loginTime: user.loginTime || user.savedAt
        };
      }
      return null;
    } catch (error) {
      console.error('[OfflineStorage] Failed to get last login session:', error);
      return null;
    }
  }

  // Add action to sync queue
  async addToSyncQueue(action: string, data: any, endpoint: string): Promise<void> {
    const syncItem = {
      action,
      data,
      endpoint,
      timestamp: new Date().toISOString(),
      status: 'pending',
      retryCount: 0,
      lastAttempt: null
    };
    await this.addData('syncQueue', syncItem);
  }

  // Get all pending sync actions
  async getPendingSyncActions(): Promise<any[]> {
    const allActions = await this.getAllData('syncQueue');
    return allActions.filter(action => action.status === 'pending');
  }

  // Mark sync action as completed
  async markSyncCompleted(id: number): Promise<void> {
    const action = await this.getDataById('syncQueue', id);
    if (action) {
      action.status = 'completed';
      action.completedAt = new Date().toISOString();
      await this.updateData('syncQueue', action);
    }
  }

  // Mark sync action as failed
  async markSyncFailed(id: number, error: string): Promise<void> {
    const action = await this.getDataById('syncQueue', id);
    if (action) {
      action.retryCount = (action.retryCount || 0) + 1;
      action.lastAttempt = new Date().toISOString();
      action.lastError = error;
      
      // If retry count exceeds limit, mark as failed
      if (action.retryCount >= 3) {
        action.status = 'failed';
      }
      
      await this.updateData('syncQueue', action);
    }
  }

  // Save app configuration
  async saveAppConfig(key: string, value: any): Promise<void> {
    await this.updateData('appData', { key, value, updatedAt: new Date().toISOString() });
  }

  // Get app configuration
  async getAppConfig(key: string): Promise<any> {
    const config = await this.getDataById('appData', key);
    return config ? config.value : null;
  }

  // Get storage usage
  async getStorageUsage(): Promise<{ used: number, available: number }> {
    if (typeof navigator !== 'undefined' && 'storage' in navigator && 'estimate' in navigator.storage) {
      try {
        const estimate = await navigator.storage.estimate();
        return {
          used: estimate.usage || 0,
          available: estimate.quota || 0
        };
      } catch (error) {
        console.error('Failed to estimate storage:', error);
      }
    }
    return { used: 0, available: 0 };
  }

  // Get database size estimate
  async getDBSize(): Promise<number> {
    const db = await this.ensureDB();
    let totalSize = 0;

    const storeNames = Array.from(db.objectStoreNames);
    
    for (const storeName of storeNames) {
      const size = await this.getStoreSize(storeName);
      totalSize += size;
    }

    return totalSize;
  }

  private async getStoreSize(storeName: string): Promise<number> {
    return new Promise((resolve) => {
      const transaction = this.db!.transaction([storeName], 'readonly');
      const store = transaction.objectStore(storeName);
      const request = store.getAll();

      request.onsuccess = () => {
        const data = request.result;
        const jsonString = JSON.stringify(data);
        const size = new Blob([jsonString]).size;
        resolve(size);
      };

      request.onerror = () => {
        resolve(0);
      };
    });
  }
}

// Create singleton instance
export const offlineStorage = new OfflineStorage();

// Sync service for background sync
export class SyncService {
  private static instance: SyncService;
  private offlineStorage: OfflineStorage;

  constructor() {
    this.offlineStorage = offlineStorage;
  }

  static getInstance(): SyncService {
    if (!SyncService.instance) {
      SyncService.instance = new SyncService();
    }
    return SyncService.instance;
  }

  // Sync pending actions
  async syncPendingActions(): Promise<{ success: number; failed: number }> {
    try {
      const pendingActions = await this.offlineStorage.getPendingSyncActions();
      let successCount = 0;
      let failedCount = 0;

      console.log(`[SyncService] Syncing ${pendingActions.length} pending actions`);

      for (const action of pendingActions) {
        try {
          await this.syncAction(action);
          await this.offlineStorage.markSyncCompleted(action.id);
          successCount++;
          console.log(`[SyncService] Action ${action.id} synced successfully`);
        } catch (error) {
          await this.offlineStorage.markSyncFailed(action.id, error instanceof Error ? error.message : 'Unknown error');
          failedCount++;
          console.error(`[SyncService] Failed to sync action ${action.id}:`, error);
        }
      }

      console.log(`[SyncService] Sync completed: ${successCount} success, ${failedCount} failed`);
      return { success: successCount, failed: failedCount };

    } catch (error) {
      console.error('[SyncService] Failed to sync pending actions:', error);
      return { success: 0, failed: 0 };
    }
  }

  private async syncAction(action: any): Promise<void> {
    const { action: actionType, data, endpoint } = action;
    
    // TODO: Integrate with your actual API client
    // This is a placeholder for the actual sync logic
    
    console.log(`[SyncService] Syncing ${actionType} to ${endpoint}:`, data);
    
    // Simulate API call
    await new Promise((resolve, reject) => {
      setTimeout(() => {
        // Simulate random failures for testing
        if (Math.random() < 0.2) { // 20% failure rate for testing
          reject(new Error('Simulated network error'));
        } else {
          resolve({ success: true });
        }
      }, 1000 + Math.random() * 2000); // Random delay between 1-3 seconds
    });
  }

  // Register for background sync if available
  registerBackgroundSync(): void {
    if (typeof navigator !== 'undefined' && 'serviceWorker' in navigator && 'SyncManager' in window) {
      navigator.serviceWorker.ready.then(registration => {
        return registration.sync.register('sync-data');
      }).then(() => {
        console.log('[SyncService] Background sync registered');
      }).catch(error => {
        console.error('[SyncService] Failed to register background sync:', error);
      });
    } else {
      console.log('[SyncService] Background sync not supported');
    }
  }

  // Get sync statistics
  async getSyncStats(): Promise<{
    pending: number;
    completed: number;
    failed: number;
    total: number;
  }> {
    const allActions = await this.offlineStorage.getAllData('syncQueue');
    
    return {
      pending: allActions.filter(a => a.status === 'pending').length,
      completed: allActions.filter(a => a.status === 'completed').length,
      failed: allActions.filter(a => a.status === 'failed').length,
      total: allActions.length
    };
  }
}

export const syncService = SyncService.getInstance();
