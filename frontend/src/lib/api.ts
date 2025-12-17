import axios, {
  AxiosInstance,
  InternalAxiosRequestConfig,
  AxiosResponse,
  AxiosError,
} from "axios";
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
    return (
      !error.response ||
      (error.response.status >= 500 && error.response.status < 600)
    );
  },
};

// Retry function
const retryRequest = async (
  fn: () => Promise<unknown>,
  retries = RETRY_CONFIG.retries
): Promise<unknown> => {
  try {
    return await fn();
  } catch (error: unknown) {
    if (retries > 0 && RETRY_CONFIG.retryCondition(error as AxiosError)) {
      console.log(`Retrying request... ${retries} attempts left`);
      await new Promise((resolve) =>
        setTimeout(resolve, RETRY_CONFIG.retryDelay)
      );
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
    const status = error.response?.status;
    const requestUrl: string | undefined = error.config?.url;

    // Handle 401 Unauthorized (expired/invalid token, etc.)
    if (status === 401) {
      // Jangan redirect untuk endpoint auth tertentu (login, lupa password, dll)
      const isAuthEndpoint =
        requestUrl?.includes("/auth/login") ||
        requestUrl?.includes("/lupa-password") ||
        requestUrl?.includes("/auth/refresh") ||
        requestUrl?.includes("/auth/me");

      if (!isAuthEndpoint) {
        // Clear token dan data user, lalu paksa kembali ke halaman login
        localStorage.removeItem("auth_token");
        localStorage.removeItem("user_data");

        // Hanya redirect jika kita bukan sudah di halaman login
        if (
          typeof window !== "undefined" &&
          !window.location.pathname.includes("/login") &&
          window.location.pathname !== "/"
        ) {
          window.location.href = "/";
        }
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
      try {
        // Test connection first with retry
        await retryRequest(() => api.get("/csrf-cookie", { timeout: 5000 }));

        // Skip CSRF token for now since we're using API routes
        const response = (await retryRequest(() =>
          api.post("/auth/login", { email, password })
        )) as AxiosResponse;

        return response.data;
      } catch (error: unknown) {
        const axiosError = error as AxiosError;
        console.error("API login error:", axiosError);

        // Provide more specific error messages
        if (axiosError.code === "ECONNREFUSED") {
          throw new Error(
            "Tidak dapat terhubung ke server. Pastikan backend Laravel berjalan di http://localhost:8000"
          );
        } else if (
          axiosError.code === "ETIMEDOUT" ||
          axiosError.message.includes("timeout")
        ) {
          throw new Error(
            "Koneksi timeout. Server mungkin lambat atau tidak merespons."
          );
        } else if (axiosError.response?.status === 404) {
          throw new Error("Endpoint tidak ditemukan. Periksa konfigurasi API.");
        } else if (axiosError.response?.status === 500) {
          throw new Error("Server error. Periksa log backend Laravel.");
        } else if (axiosError.response?.status === 401) {
          throw new Error("Email atau password salah.");
        } else if (axiosError.response?.status === 422) {
          throw new Error(
            "Data tidak valid: " +
              ((axiosError.response?.data as { message?: string })?.message ||
                "Periksa input Anda")
          );
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

    // Lupa Password endpoints
    requestOtp: async (email: string): Promise<ApiResponse> => {
      try {
        const response = await api.post("/lupa-password/request-otp", {
          email,
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

    verifyOtp: async (email: string, kodeOtp: string): Promise<ApiResponse> => {
      const response = await api.post("/lupa-password/verify-otp", {
        email,
        kode_otp: kodeOtp,
      });
      return response.data;
    },

    resetPassword: async (
      email: string,
      kodeOtp: string,
      password: string,
      passwordConfirmation: string
    ): Promise<ApiResponse> => {
      const response = await api.post("/lupa-password/reset-password", {
        email,
        kode_otp: kodeOtp,
        password,
        password_confirmation: passwordConfirmation,
      });
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
    checkIn: async (data?: {
      latitude_in?: number;
      longitude_in?: number;
      keterangan?: string;
      file_pendukung?: File;
    }): Promise<ApiResponse> => {
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

    checkOut: async (data?: {
      latitude_out?: number;
      longitude_out?: number;
      keterangan?: string;
    }): Promise<ApiResponse> => {
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

    getHistory: async (
      params?: Record<string, unknown>
    ): Promise<ApiResponse> => {
      const response = await api.get("/attendance/history", { params });
      return response.data;
    },

    getUserHistory: async (
      userId: number,
      params?: Record<string, unknown>
    ): Promise<ApiResponse> => {
      const response = await api.get(`/attendance/user/${userId}`, { params });
      return response.data;
    },

    getLog: async (id: number): Promise<ApiResponse> => {
      const response = await api.get(`/attendance/${id}/log`);
      return response.data;
    },

    createManual: async (data?: {
      tanggal_mulai?: string;
      durasi_hari?: number;
      status_absensi?: string;
      keterangan_pendukung?: string;
      file_pendukung?: File;
    }): Promise<ApiResponse> => {
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
      const isFormData =
        typeof FormData !== "undefined" && data instanceof FormData;
      const response = await api.post("/leave", data, {
        headers: isFormData
          ? { "Content-Type": "multipart/form-data" }
          : undefined,
      });
      return response.data;
    },

    update: async (id: number, data: unknown): Promise<ApiResponse> => {
      const response = await api.put(`/leave/${id}`, data);
      return response.data;
    },

    approve: async (id: number, komentar?: string): Promise<ApiResponse> => {
      const response = await api.post(
        `/leave/${id}/approve`,
        komentar ? { komentar } : {}
      );
      return response.data;
    },

    reject: async (id: number, reason?: string): Promise<ApiResponse> => {
      const response = await api.post(`/leave/${id}/reject`, { reason });
      return response.data;
    },

    approveKepsek: async (
      id: number,
      komentar?: string
    ): Promise<ApiResponse> => {
      const response = await api.post(
        `/leave/${id}/approve-kepsek`,
        komentar ? { komentar } : {}
      );
      return response.data;
    },

    rejectKepsek: async (id: number, reason?: string): Promise<ApiResponse> => {
      const response = await api.post(`/leave/${id}/reject-kepsek`, { reason });
      return response.data;
    },

    approveDirpen: async (
      id: number,
      komentar?: string
    ): Promise<ApiResponse> => {
      const response = await api.post(
        `/leave/${id}/approve-dirpen`,
        komentar ? { komentar } : {}
      );
      return response.data;
    },

    rejectDirpen: async (id: number, reason?: string): Promise<ApiResponse> => {
      const response = await api.post(`/leave/${id}/reject-dirpen`, { reason });
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
    update: async (
      id: number,
      data: { nama: string }
    ): Promise<ApiResponse> => {
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

  // Slip Gaji endpoints
  slipGaji: {
    getAll: async (params?: Record<string, unknown>): Promise<ApiResponse> => {
      const response = await api.get("/slip-gaji", { params });
      return response.data;
    },

    getById: async (id: number): Promise<ApiResponse> => {
      const response = await api.get(`/slip-gaji/${id}`);
      return response.data;
    },

    create: async (data: {
      id_user: number;
      tanggal: string;
      total_gaji: number;
      nomor_rekening?: string;
      keterangan?: string;
    }): Promise<ApiResponse> => {
      const response = await api.post("/slip-gaji", data);
      return response.data;
    },

    update: async (
      id: number,
      data: {
        id_user?: number;
        tanggal?: string;
        total_gaji?: number;
        nomor_rekening?: string;
        keterangan?: string;
      }
    ): Promise<ApiResponse> => {
      const response = await api.put(`/slip-gaji/${id}`, data);
      return response.data;
    },

    delete: async (id: number): Promise<ApiResponse> => {
      const response = await api.delete(`/slip-gaji/${id}`);
      return response.data;
    },

    getUserHistory: async (
      userId: number,
      params?: Record<string, unknown>
    ): Promise<ApiResponse> => {
      const response = await api.get(`/slip-gaji/user/${userId}`, { params });
      return response.data;
    },

    getEmployeeData: async (userId: number): Promise<ApiResponse> => {
      const response = await api.get(`/slip-gaji/employee/${userId}/data`);
      return response.data;
    },

    getEmployeesByPaymentStatus: async (
      month?: number,
      year?: number
    ): Promise<ApiResponse> => {
      const params: Record<string, unknown> = {};
      if (month) params.month = month;
      if (year) params.year = year;
      const response = await api.get("/slip-gaji/employees-by-payment-status", {
        params,
      });
      return response.data;
    },

    uploadExcel: async (file: File): Promise<ApiResponse> => {
      const formData = new FormData();
      formData.append("file", file);
      const response = await api.post("/slip-gaji/upload-excel", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      return response.data;
    },

    downloadPDF: async (id: number): Promise<void> => {
      try {
        // Get token for authorization
        const token = localStorage.getItem("auth_token");

        // Create a direct axios request to bypass interceptors that might modify blob
        const response = await axios.get(
          `${API_CONFIG.BASE_URL}/api/slip-gaji/${id}/download-pdf`,
          {
            responseType: "blob",
            headers: {
              Accept: "application/pdf",
              Authorization: token ? `Bearer ${token}` : "",
            },
            withCredentials: true,
          }
        );

        // Verify response is blob
        if (!(response.data instanceof Blob)) {
          // Try to read as text to see error message
          const text = await response.data.text();
          try {
            const json = JSON.parse(text);
            throw new Error(json.message || "Gagal download PDF");
          } catch {
            throw new Error("Gagal download PDF");
          }
        }

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement("a");
        link.href = url;

        // Get filename from Content-Disposition header or use default
        const contentDisposition = response.headers["content-disposition"];
        let filename = `slip_gaji_${id}.pdf`;
        if (contentDisposition) {
          const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
          if (filenameMatch) {
            filename = filenameMatch[1];
          }
        }

        link.setAttribute("download", filename);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      } catch (error: any) {
        console.error("Error downloading PDF:", error);
        throw error;
      }
    },

    downloadTemplate: async (): Promise<Blob> => {
      try {
        // Get token for authorization
        const token = localStorage.getItem("auth_token");

        // Create a direct axios request to bypass interceptors that might modify blob
        const response = await axios.get(
          `${API_CONFIG.BASE_URL}/api/slip-gaji/download-template`,
          {
            responseType: "blob",
            headers: {
              Accept:
                "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
              Authorization: token ? `Bearer ${token}` : "",
            },
            withCredentials: true,
          }
        );

        // Verify response is blob
        if (!(response.data instanceof Blob)) {
          // Try to read as text to see error message
          const text = await new Response(response.data).text();
          try {
            const json = JSON.parse(text);
            throw new Error(json.message || "Gagal download template");
          } catch {
            throw new Error("Response bukan file Excel yang valid");
          }
        }

        return response.data;
      } catch (error: any) {
        console.error("Download template error:", error);
        console.error("Error response:", error.response);
        console.error("Error response data:", error.response?.data);

        // If error response is blob, try to parse it as JSON
        if (error.response?.data instanceof Blob) {
          try {
            const text = await error.response.data.text();
            console.log("Blob text content:", text);
            const json = JSON.parse(text);
            throw new Error(
              json.message || json.error?.message || "Gagal download template"
            );
          } catch (parseError) {
            console.error("Failed to parse blob as JSON:", parseError);
            throw new Error("Gagal download template: Response tidak valid");
          }
        }

        // If error response is JSON (from our improved error handling)
        if (error.response?.data) {
          const errorData = error.response.data;

          // Handle if data is already an object
          if (typeof errorData === "object" && !(errorData instanceof Blob)) {
            if (errorData.message) {
              throw new Error(errorData.message);
            }
            if (errorData.error) {
              if (typeof errorData.error === "string") {
                throw new Error(errorData.error);
              }
              if (errorData.error.message) {
                throw new Error(errorData.error.message);
              }
            }
          }

          // Handle if data is a string (JSON string)
          if (typeof errorData === "string") {
            try {
              const json = JSON.parse(errorData);
              throw new Error(
                json.message || json.error?.message || "Gagal download template"
              );
            } catch {
              // If not JSON, use the string as error message
              throw new Error(errorData);
            }
          }
        }

        // Handle different error types
        if (error.response?.status === 403) {
          throw new Error("Anda tidak memiliki izin untuk download template");
        } else if (error.response?.status === 404) {
          throw new Error("Endpoint download template tidak ditemukan");
        } else if (error.response?.status === 500) {
          // Try to get error message from various possible locations
          const errorMsg =
            error.response?.data?.message ||
            error.response?.data?.error?.message ||
            error.response?.data?.error ||
            (typeof error.response?.data === "string"
              ? error.response.data
              : null) ||
            "Server error. Periksa log backend Laravel";
          throw new Error(errorMsg);
        }

        throw new Error(error?.message || "Gagal download template");
      }
    },
  },

  notifications: {
    getAll: async (): Promise<ApiResponse> => {
      const response = await api.get("/notifications");
      return response.data;
    },
    getVerifierNotifications: async (): Promise<ApiResponse> => {
      const response = await api.get("/verifier-notifications");
      return response.data;
    },
  },
};

export default api;
