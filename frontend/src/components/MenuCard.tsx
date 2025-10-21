"use client";

interface MenuCardProps {
  title: string;
  icon: React.ReactNode;
  onClick?: () => void;
}

export default function MenuCard({ title, icon, onClick }: MenuCardProps) {
  return (
    <button onClick={onClick} className="bg-white rounded-2xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col items-center justify-center min-h-[140px]">
      <div className="mb-3">{icon}</div>
      <p className="text-sm text-gray-700 text-center font-medium">{title}</p>
    </button>
  );
}
