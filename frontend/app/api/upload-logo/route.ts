import { NextRequest, NextResponse } from "next/server";
import { writeFile } from "fs/promises";
import path from "path";

export async function POST(request: NextRequest) {
  try {
    const data = await request.formData();
    const file: File | null = data.get("logo") as unknown as File;

    if (!file) {
      return NextResponse.json({ error: "No file uploaded" }, { status: 400 });
    }

    // Validasi file
    const validTypes = ["image/png", "image/jpeg", "image/jpg", "image/svg+xml"];
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (!validTypes.includes(file.type)) {
      return NextResponse.json({ error: "Invalid file type" }, { status: 400 });
    }

    if (file.size > maxSize) {
      return NextResponse.json({ error: "File too large" }, { status: 400 });
    }

    const bytes = await file.arrayBuffer();
    const buffer = Buffer.from(bytes);

    // Tentukan ekstensi file
    const extension = file.type === "image/svg+xml" ? "svg" : file.type === "image/png" ? "png" : "jpg";

    // Simpan file ke public directory
    const filename = `logo-drs.${extension}`;
    const filepath = path.join(process.cwd(), "public", filename);

    await writeFile(filepath, buffer);

    return NextResponse.json({
      message: "File uploaded successfully",
      filename: filename,
    });
  } catch (error) {
    console.error("Upload error:", error);
    return NextResponse.json({ error: "Upload failed" }, { status: 500 });
  }
}
