"use server";

export const get = async () => {
  try {
    const response = await fetch(`${process.env.APP_URL}/users`);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const json = await response.json();
    console.log("API Response:", json);
    // Laravel API mengembalikan data dalam format { data: [...] }
    return json.data || json;
  } catch (error) {
    console.error("Error fetching users:", error);
    return [];
  }
};
