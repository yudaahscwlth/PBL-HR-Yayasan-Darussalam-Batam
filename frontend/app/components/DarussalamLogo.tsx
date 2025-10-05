import React from "react";

interface DarussalamLogoProps {
  width?: number;
  height?: number;
  className?: string;
}

export default function DarussalamLogo({ width = 120, height = 120, className = "" }: DarussalamLogoProps) {
  return (
    <div className={`inline-block ${className}`} style={{ width, height }}>
      <svg width={width} height={height} viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
        {/* Outer yellow oval */}
        <ellipse cx="200" cy="200" rx="190" ry="120" fill="#FFD700" stroke="#000000" strokeWidth="4" />

        {/* Inner white oval */}
        <ellipse cx="200" cy="180" rx="140" ry="80" fill="#FFFFFF" stroke="#000000" strokeWidth="2" />

        {/* Star at top */}
        <path d="M200 60 L210 90 L240 90 L218 108 L228 138 L200 120 L172 138 L182 108 L160 90 L190 90 Z" fill="#FFD700" stroke="#000000" strokeWidth="2" />

        {/* Letters "als" in the center */}
        <g transform="translate(200, 180)">
          {/* Letter 'a' - blue */}
          <path d="M-60 -20 Q-60 -35 -45 -35 Q-30 -35 -30 -20 L-30 20 L-40 20 L-40 -5 Q-40 -15 -45 -15 Q-50 -15 -50 -5 L-50 20 L-60 20 Z" fill="#1E40AF" />
          <path d="M-50 0 L-30 0 L-30 10 L-50 10 Z" fill="#1E40AF" />

          {/* Letter 'l' - blue */}
          <rect x="-15" y="-35" width="10" height="55" fill="#1E40AF" />

          {/* Letter 's' - green */}
          <path d="M10 -20 Q10 -35 25 -35 Q40 -35 40 -20 Q40 -10 25 -10 Q15 -10 15 0 Q15 10 25 10 Q40 10 40 20 Q40 35 25 35 Q10 35 10 20" fill="none" stroke="#059669" strokeWidth="8" strokeLinecap="round" />
        </g>

        {/* Bottom text "DARUSSALAM" */}
        <path d="M50 280 L50 320 L70 320 Q85 320 85 300 Q85 280 70 280 Z M60 290 L70 290 Q75 290 75 300 Q75 310 70 310 L60 310 Z" fill="#000000" />
        <path d="M95 280 L95 320 L105 320 L105 305 L115 320 L125 320 L115 305 Q125 305 125 292.5 Q125 280 115 280 Z M105 290 L115 290 Q115 295 115 295 Q115 300 115 300 L105 300 Z" fill="#000000" />
        <path d="M135 280 L135 320 L155 320 Q170 320 170 300 Q170 280 155 280 Z M145 290 L155 290 Q160 290 160 300 Q160 310 155 310 L145 310 Z" fill="#000000" />
        <path d="M180 280 L180 320 L190 320 L190 305 L200 320 L210 320 L200 305 Q210 305 210 292.5 Q210 280 200 280 Z M190 290 L200 290 Q200 295 200 295 Q200 300 200 300 L190 300 Z" fill="#000000" />
        <path d="M220 280 L220 320 L240 320 Q255 320 255 310 Q255 300 240 300 L230 300 L230 290 L240 290 Q255 290 255 280 L245 280 Q230 280 230 290 L230 300 Q230 310 240 310 L240 320" fill="#000000" />
        <path d="M265 280 L265 320 L285 320 Q300 320 300 310 Q300 300 285 300 L275 300 L275 290 L285 290 Q300 290 300 280 L290 280 Q275 280 275 290 L275 300 Q275 310 285 310 L285 320" fill="#000000" />
        <path d="M310 280 L310 320 L320 320 L320 305 L330 320 L340 320 L330 305 Q340 305 340 292.5 Q340 280 330 280 Z M320 290 L330 290 Q330 295 330 295 Q330 300 330 300 L320 300 Z" fill="#000000" />
        <path d="M350 280 L350 320 L360 320 L360 290 L370 290 L370 320 L380 320 L380 280 L370 280 L370 290 L360 290 L360 280 Z" fill="#000000" />
      </svg>
    </div>
  );
}
