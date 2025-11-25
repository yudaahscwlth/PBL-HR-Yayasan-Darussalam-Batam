// src/hooks/useOfflineSync.ts
"use client";

import { useEffect, useState, useCallback } from "react";
import { offlineStorage, syncService } from "@/lib/offlineStorage";

interface OfflineSyncState {
  isOnline: boolean;
  pendingActions: number;
  lastSyncTime: string | null;
  storageUsage: { used: number; available: number };
  isSyncing: boolean;
}

// Debounce utility
const debounce = (func: Function, wait: number) => {
  let timeout: NodeJS.Timeout;
  return (...args: any[]) => {
    clearTimeout(timeout);
    timeout = setTimeout(() => func(...args), wait);
  };
};

export function useOfflineSync() {
  const [state, setState] = useState<OfflineSyncState>({
    isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,
    pendingActions: 0,
    lastSyncTime: null,
    storageUsage: { used: 0, available: 0 },
    isSyncing: false,
  });

  // Update online status dengan debounce
  const updateOnlineStatus = useCallback(
    debounce(() => {
      const isOnline = navigator.onLine;
      console.log(`[useOfflineSync] Network status: ${isOnline ? 'Online' : 'Offline'}`);
      setState(prev => ({ ...prev, isOnline }));

      // Trigger sync when coming back online
      if (isOnline) {
        syncData();
      }
    }, 1000),
    []
  );

  // Get storage usage
  const getStorageUsage = useCallback(async () => {
    try {
      const usage = await offlineStorage.getStorageUsage();
      setState(prev => ({ ...prev, storageUsage: usage }));
    } catch (error) {
      console.error("Failed to get storage usage:", error);
    }
  }, []);

  // Get pending actions count
  const getPendingActions = useCallback(async () => {
    try {
      const actions = await offlineStorage.getPendingSyncActions();
      setState(prev => ({ ...prev, pendingActions: actions.length }));
    } catch (error) {
      console.error("Failed to get pending actions:", error);
    }
  }, []);

  // Get last sync time
  const getLastSyncTime = useCallback(() => {
    if (typeof window === 'undefined') return;
    
    const lastSync = localStorage.getItem("lastSyncTime");
    setState(prev => ({ 
      ...prev, 
      lastSyncTime: lastSync ? new Date(lastSync).toLocaleString("id-ID") : null 
    }));
  }, []);

  // Sync data
  const syncData = useCallback(async () => {
    if (typeof navigator === 'undefined' || !navigator.onLine) {
      console.log("Cannot sync: device is offline");
      return;
    }

    console.log("[useOfflineSync] Starting manual sync...");
    setState(prev => ({ ...prev, isSyncing: true }));

    try {
      await syncService.syncPendingActions();
      
      // Update last sync time
      if (typeof window !== 'undefined') {
        localStorage.setItem("lastSyncTime", new Date().toISOString());
      }
      
      getLastSyncTime();
      await getPendingActions();
      
      // Register background sync for future
      syncService.registerBackgroundSync();
      
      console.log("[useOfflineSync] Sync completed successfully");
      
    } catch (error) {
      console.error("Sync failed:", error);
    } finally {
      setState(prev => ({ ...prev, isSyncing: false }));
    }
  }, [getPendingActions, getLastSyncTime]);

  // Save data for offline
  const saveOfflineData = useCallback(async (key: string, data: any) => {
    try {
      await offlineStorage.saveAppConfig(key, data);
      console.log(`[useOfflineSync] Data saved for key: ${key}`);
    } catch (error) {
      console.error("Failed to save offline data:", error);
      throw error;
    }
  }, []);

  // Get offline data
  const getOfflineData = useCallback(async (key: string) => {
    try {
      const data = await offlineStorage.getAppConfig(key);
      console.log(`[useOfflineSync] Data retrieved for key: ${key}`, data);
      return data;
    } catch (error) {
      console.error("Failed to get offline data:", error);
      return null;
    }
  }, []);

  // Queue action for sync
  const queueAction = useCallback(async (action: string, data: any, endpoint: string) => {
    try {
      await offlineStorage.addToSyncQueue(action, data, endpoint);
      await getPendingActions();
      
      console.log(`[useOfflineSync] Action queued: ${action} for ${endpoint}`);
      
      // If online, try to sync immediately
      if (navigator.onLine) {
        syncData();
      }
    } catch (error) {
      console.error("Failed to queue action:", error);
      throw error;
    }
  }, [getPendingActions, syncData]);

  // Clear cached data
  const clearCache = useCallback(async (storeName?: string) => {
    try {
      if (storeName) {
        await offlineStorage.clearStore(storeName);
        console.log(`[useOfflineSync] Store cleared: ${storeName}`);
      } else {
        // Clear all caches
        await Promise.all([
          offlineStorage.clearStore('absensi'),
          offlineStorage.clearStore('cuti'),
          offlineStorage.clearStore('evaluasi'),
          offlineStorage.clearStore('syncQueue'),
          offlineStorage.clearStore('appData'),
        ]);
        console.log("[useOfflineSync] All stores cleared");
      }
      
      await getPendingActions();
      await getStorageUsage();
      
    } catch (error) {
      console.error("Failed to clear cache:", error);
      throw error;
    }
  }, [getPendingActions, getStorageUsage]);

  // Initialize all data
  const initializeData = useCallback(async () => {
    if (typeof window === 'undefined') return;
    
    console.log("[useOfflineSync] Initializing data...");
    await Promise.all([
      updateOnlineStatus(),
      getStorageUsage(),
      getPendingActions(),
      getLastSyncTime(),
    ]);
  }, [updateOnlineStatus, getStorageUsage, getPendingActions, getLastSyncTime]);

  // Initialize and set up event listeners
  useEffect(() => {
    if (typeof window === 'undefined') return;

    // Initial setup
    initializeData();

    // Listen for online/offline events
    window.addEventListener("online", updateOnlineStatus);
    window.addEventListener("offline", updateOnlineStatus);

    // Listen for messages from service worker
    const handleMessage = (event: MessageEvent) => {
      if (event.data && event.data.type === "SYNC_STATUS") {
        console.log(`[useOfflineSync] Sync status: ${event.data.status}`);
        setState(prev => ({ 
          ...prev, 
          isSyncing: event.data.status === "syncing" 
        }));
        
        if (event.data.status === "completed") {
          getLastSyncTime();
          getPendingActions();
        }
      }
    };

    if (navigator.serviceWorker) {
      navigator.serviceWorker.addEventListener("message", handleMessage);
    }

    // Periodic updates (every 30 seconds)
    const interval = setInterval(() => {
      getStorageUsage();
    }, 30000);

    return () => {
      window.removeEventListener("online", updateOnlineStatus);
      window.removeEventListener("offline", updateOnlineStatus);
      
      if (navigator.serviceWorker) {
        navigator.serviceWorker.removeEventListener("message", handleMessage);
      }
      
      clearInterval(interval);
    };
  }, [initializeData, updateOnlineStatus, getStorageUsage, getPendingActions, getLastSyncTime]);

  return {
    ...state,
    syncData,
    saveOfflineData,
    getOfflineData,
    queueAction,
    clearCache,
    refreshData: initializeData,
  };
}

// Hook for specific data types
export function useOfflineData<T>(key: string, defaultValue?: T) {
  const [data, setData] = useState<T | null>(defaultValue || null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const loadData = useCallback(async () => {
    try {
      setLoading(true);
      setError(null);
      
      const offlineData = await offlineStorage.getAppConfig(key);
      setData(offlineData || defaultValue || null);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to load data");
    } finally {
      setLoading(false);
    }
  }, [key, defaultValue]);

  const saveData = useCallback(async (newData: T) => {
    try {
      setError(null);
      await offlineStorage.saveAppConfig(key, newData);
      setData(newData);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Failed to save data");
      throw err;
    }
  }, [key]);

  useEffect(() => {
    loadData();
  }, [loadData]);

  return {
    data,
    loading,
    error,
    saveData,
    refresh: loadData,
  };
}

export default useOfflineSync;