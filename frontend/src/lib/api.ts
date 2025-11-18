import axios, { AxiosInstance, InternalAxiosRequestConfig, AxiosResponse, AxiosError } from "axios";
import { ApiResponse } from "@/types/auth";
import { API_CONFIG } from "@/config/api";
import http from "http";
import https from "https";

// Retry configuration
const RETRY_CONFIG = {
  retries: 3,
  retryDelay: 1000, // 1 second
  retryCondition: (error: AxiosError) => {
    // Retry on network errors or 5xx server errors
    return !error.response || (error.response.status >= 500 && error.response.status < 600);
  },
};

// Retry function
const retryRequest = async (fn: () => Promise<unknown>, retries = RETRY_CONFIG.retries): Promise<unknown> => {
  try {
    return await fn();
  } catch (error: unknown) {
    if (retries > 0 && RETRY_CONFIG.retryCondition(error as AxiosError)) {
      console.log(`Retrying request... ${retries} attempts left`);
      await new Promise((resolve) => setTimeout(resolve, RETRY_CONFIG.retryDelay));
      return retryRequest(fn, retries - 1);
    }
    throw error;
  }
};

// Create axios instance
const api: AxiosInstance = axios.create({
  baseURL: `${API_CONFIG.BASE_URL}/api`,
  timeout: API_CONFIG.TIMEOUT,
  headers: API_CONFIG.HEADERS,
  withCredentials: true,
  xsrfCookieName: "XSRF-TOKEN",
  xsrfHeaderName: "X-XSRF-TOKEN",
  // Add connection pooling and keep-alive
  httpAgent: new http.Agent({
    keepAlive: true,
    maxSockets: 5,
    timeout: API_CONFIG.TIMEOUT,
  }),
  httpsAgent: new https.Agent({
    keepAlive: true,
    maxSockets: 5,
    timeout: API_CONFIG.TIMEOUT,
  }),
});

console.log("API Config:", {
  baseURL: `${API_CONFIG.BASE_URL}/api`,
  timeout: API_CONFIG.TIMEOUT,
  headers: API_CONFIG.HEADERS,
});

// Request interceptor to add auth token
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // Get token from localStorage
    const token = localStorage.getItem("auth_token");

    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle auth errors
api.interceptors.response.use(
  (response: AxiosResponse) => {
    return response;
  },
  (error) => {
    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      // Clear token and redirect to login
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user_data");

      // Only redirect if we're not already on login page
      if (typeof window !== "undefined" && !window.location.pathname.includes("/login")) {
        window.location.href = "/";
      }
    }

    return Promise.reject(error);
  }
);

// API methods
export const apiClient = {
  // Auth endpoints
  auth: {
    getCsrfToken: async (): Promise<ApiResponse> => {
      const response = await api.get("/csrf-cookie");
      return response.data;
    },

    login: async (email: string, password: string): Promise<ApiResponse> => {
      console.log("API client login called with:", { email, password: "***" });
      console.log("API base URL:", api.defaults.baseURL);
      console.log("API headers:", api.defaults.headers);
      console.log("API timeout:", api.defaults.timeout);

      try {
        // Test connection first with retry
        console.log("Testing API connection...");
        const testResponse = (await retryRequest(() => api.get("/csrf-cookie", { timeout: 5000 }))) as AxiosResponse;
        console.log("Connection test successful:", testResponse.status);

        // Skip CSRF token for now since we're using API routes
        const response = (await retryRequest(() => api.post("/auth/login", { email, password }))) as AxiosResponse;
        console.log("API login response status:", response.status);
        console.log("API login response data:", response.data);
        console.log("API login response headers:", response.headers);
        return response.data;
      } catch (error: unknown) {
        const axiosError = error as AxiosError;
        console.error("API login error:", axiosError);
        console.error("Error type:", axiosError.constructor.name);
        console.error("Error message:", axiosError.message);
        console.error("Error code:", axiosError.code);
        console.error("Error response:", axiosError.response?.data);
        console.error("Error status:", axiosError.response?.status);
        console.error("Error config:", axiosError.config);

        // Provide more specific error messages
        if (axiosError.code === "ECONNREFUSED") {
          throw new Error("Tidak dapat terhubung ke server. Pastikan backend Laravel berjalan di http://localhost:8000");
        } else if (axiosError.code === "ETIMEDOUT" || axiosError.message.includes("timeout")) {
          throw new Error("Koneksi timeout. Server mungkin lambat atau tidak merespons.");
        } else if (axiosError.response?.status === 404) {
          throw new Error("Endpoint tidak ditemukan. Periksa konfigurasi API.");
        } else if (axiosError.response?.status === 500) {
          throw new Error("Server error. Periksa log backend Laravel.");
        } else if (axiosError.response?.status === 401) {
          throw new Error("Email atau password salah.");
        } else if (axiosError.response?.status === 422) {
          throw new Error("Data tidak valid: " + ((axiosError.response?.data as { message?: string })?.message || "Periksa input Anda"));
        }

        throw error;
      }
    },

    logout: async (): Promise<ApiResponse> => {
      const response = await api.post("/auth/logout");
      return response.data;
    },

    me: async (): Promise<ApiResponse> => {
      const response = await api.get("/auth/me");
      return response.data;
    },

    refresh: async (): Promise<ApiResponse> => {
      const response = await api.post("/auth/refresh");
      return response.data;
    },
  },

  // User endpoints
  users: {
    getAll: async (): Promise<ApiResponse> => {
      const response = await api.get("/users");
      return response.data;
    },

    getById: async (id: number): Promise<ApiResponse> => {
      const response = await api.get(`/users/${id}`);
      return response.data;
    },

    update: async (id: number, data: unknown): Promise<ApiResponse> => {
      const response = await api.put(`/users/${id}`, data);
      return response.data;
    },
  },

  // Profile endpoints
  profile: {
    getPersonal: async (): Promise<ApiResponse> => {
      const response = await api.get("/profile/personal");
      return response.data;
    },

    updatePersonal: async (data: unknown): Promise<ApiResponse> => {
      const response = await api.put("/profile/personal", data);
      return response.data;
    },

    getWork: async (): Promise<ApiResponse> => {
      const response = await api.get("/profile/work");
      return response.data;
    },

    updateWork: async (data: unknown): Promise<ApiResponse> => {
      const response = await api.put("/profile/work", data);
      return response.data;
    },
  },

  // Attendance endpoints
  attendance: {
    checkIn: async (data?: { latitude_in?: number; longitude_in?: number; keterangan?: string; file_pendukung?: File }): Promise<ApiResponse> => {
      // If no file upload, use JSON instead of FormData
      if (!data?.file_pendukung) {
        try {
          const response = await api.post("/attendance/checkin", {
            latitude_in: data?.latitude_in,
            longitude_in: data?.longitude_in,
            keterangan: data?.keterangan,
          });
          return response.data;
        } catch (error: any) {
          // Return error response in the same format as success
          if (error.response?.data) {
            return error.response.data;
          }
          throw error;
        }
      }

      // Use FormData only when file is present
      const formData = new FormData();

      if (data?.latitude_in !== undefined) {
        formData.append("latitude_in", data.latitude_in.toString());
      }
      if (data?.longitude_in !== undefined) {
        formData.append("longitude_in", data.longitude_in.toString());
      }
      if (data?.keterangan) {
        formData.append("keterangan", data.keterangan);
      }
      if (data?.file_pendukung) {
        formData.append("file_pendukung", data.file_pendukung);
      }

      try {
        const response = await api.post("/attendance/checkin", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });
        return response.data;
      } catch (error: any) {
        // Return error response in the same format as success
        if (error.response?.data) {
          return error.response.data;
        }
        throw error;
      }
    },

    checkOut: async (data?: { latitude_out?: number; longitude_out?: number; keterangan?: string }): Promise<ApiResponse> => {
      try {
        const response = await api.post("/attendance/checkout", data);
        return response.data;
      } catch (error: any) {
        // Return error response in the same format as success
        if (error.response?.data) {
          return error.response.data;
        }
        throw error;
      }
    },

    getToday: async (): Promise<ApiResponse> => {
      const response = await api.get("/attendance/today");
      return response.data;
    },

    getTodayAll: async (): Promise<ApiResponse> => {
      const response = await api.get("/attendance/today-all");
      return response.data;
    },

    getHistory: async (params?: Record<string, unknown>): Promise<ApiResponse> => {
      const response = await api.get("/attendance/history", { params });
      return response.data;
    },

    getLog: async (id: number): Promise<ApiResponse> => {
      const response = await api.get(`/attendance/${id}/log`);
      return response.data;
    },

    createManual: async (data?: { tanggal_mulai?: string; durasi_hari?: number; status_absensi?: string; keterangan_pendukung?: string; file_pendukung?: File }): Promise<ApiResponse> => {
      // If no file upload, use JSON instead of FormData
      if (!data?.file_pendukung) {
        try {
          const response = await api.post("/attendance/manual", {
            tanggal_mulai: data?.tanggal_mulai,
            durasi_hari: data?.durasi_hari,
            status_absensi: data?.status_absensi,
            keterangan_pendukung: data?.keterangan_pendukung,
          });
          return response.data;
        } catch (error: any) {
          // Return error response in the same format as success
          if (error.response?.data) {
            return error.response.data;
          }
          throw error;
        }
      }

      // Use FormData when file is present
      const formData = new FormData();

      if (data?.tanggal_mulai) {
        formData.append("tanggal_mulai", data.tanggal_mulai);
      }
      if (data?.durasi_hari !== undefined) {
        formData.append("durasi_hari", data.durasi_hari.toString());
      }
      if (data?.status_absensi) {
        formData.append("status_absensi", data.status_absensi);
      }
      if (data?.keterangan_pendukung) {
        formData.append("keterangan_pendukung", data.keterangan_pendukung);
      }
      if (data?.file_pendukung) {
        formData.append("file_pendukung", data.file_pendukung);
      }

      try {
        const response = await api.post("/attendance/manual", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });
        return response.data;
      } catch (error: any) {
        // Return error response in the same format as success
        if (error.response?.data) {
          return error.response.data;
        }
        throw error;
      }
    },
  },

  // Leave endpoints
  leave: {
    getAll: async (): Promise<ApiResponse> => {
      const response = await api.get("/leave");
      return response.data;
    },

    create: async (data: unknown): Promise<ApiResponse> => {
      const response = await api.post("/leave", data);
      return response.data;
    },

    update: async (id: number, data: unknown): Promise<ApiResponse> => {
      const response = await api.put(`/leave/${id}`, data);
      return response.data;
    },

    approve: async (id: number): Promise<ApiResponse> => {
      const response = await api.post(`/leave/${id}/approve`);
      return response.data;
    },

    reject: async (id: number, reason?: string): Promise<ApiResponse> => {
      const response = await api.post(`/leave/${id}/reject`, { reason });
      return response.data;
    },
  },

  // Evaluation endpoints
  evaluation: {
    getAll: async (): Promise<ApiResponse> => {
      const response = await api.get("/evaluation");
      return response.data;
    },

    create: async (data: unknown): Promise<ApiResponse> => {
      const response = await api.post("/evaluation", data);
      return response.data;
    },

    update: async (id: number, data: unknown): Promise<ApiResponse> => {
      const response = await api.put(`/evaluation/${id}`, data);
      return response.data;
    },

    getPersonal: async (): Promise<ApiResponse> => {
      const response = await api.get("/evaluation/personal");
      return response.data;
    },
  },

  kategoriEvaluasi: {
    getAll: async (): Promise<ApiResponse> => {
      const response = await api.get("/kategori-evaluasi");
      return response.data;
    },
    create: async (data: { nama: string }): Promise<ApiResponse> => {
      const response = await api.post("/kategori-evaluasi", data);
      return response.data;
    },
    update: async (id: number, data: { nama: string }): Promise<ApiResponse> => {
      const response = await api.put(`/kategori-evaluasi/${id}`, data);
      return response.data;
    },
    delete: async (id: number): Promise<ApiResponse> => {
      const response = await api.delete(`/kategori-evaluasi/${id}`);
      return response.data;
    },
  },

  // Dashboard endpoints
  dashboard: {
    getStats: async (): Promise<ApiResponse> => {
      const response = await api.get("/dashboard/stats");
      return response.data;
    },
    getPersonal: async (): Promise<ApiResponse> => {
      const response = await api.get("/dashboard/personal");
      return response.data;
    },
    getAttendanceSummary: async (): Promise<ApiResponse> => {
      const response = await api.get("/dashboard/attendance-summary");
      return response.data;
    },
    getUsers: async (): Promise<ApiResponse> => {
      const response = await api.get("/dashboard/users");
      return response.data;
    },
    getJobTitles: async (): Promise<ApiResponse> => {
      const response = await api.get("/dashboard/job-titles");
      return response.data;
    },
    getDepartments: async (): Promise<ApiResponse> => {
      const response = await api.get("/dashboard/departments");
      return response.data;
    },
  },
};

export default api;
