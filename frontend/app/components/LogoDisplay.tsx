"use client";

import { useState } from "react";
import Image from "next/image";
import DarussalamLogo from "./DarussalamLogo";

interface LogoDisplayProps {
  width?: number;
  height?: number;
  className?: string;
  logoPath?: string;
}

export default function LogoDisplay({ width = 100, height = 100, className = "", logoPath = "/logo-drs.png" }: LogoDisplayProps) {
  const [imageError, setImageError] = useState(false);

  if (imageError) {
    // Fallback ke SVG logo jika gambar tidak bisa dimuat
    return <DarussalamLogo width={width} height={height} className={className} />;
  }

  return <Image src={logoPath} alt="Logo Darussalam" width={width} height={height} className={`${className} rounded-lg`} onError={() => setImageError(true)} priority />;
}
