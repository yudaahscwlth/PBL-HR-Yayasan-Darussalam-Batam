"use client";

import ProfileMenu from "@/components/ProfileMenu";

export default function TPProfile() {
  const otherItems = [
    {
      title: "Slip Gaji",
      icon: (
        <svg className="w-6 h-6 text-[#1e4d8b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      ),
      path: "/tenaga-pendidik/slip-gaji",
    },
  ];

  return <ProfileMenu allowedRoles={["tenaga pendidik"]} editPath="/tenaga-pendidik/profile/edit" otherItems={otherItems} />;
}
