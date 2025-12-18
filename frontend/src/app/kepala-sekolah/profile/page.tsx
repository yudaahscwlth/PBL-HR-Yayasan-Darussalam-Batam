"use client";

import ProfileMenu from "@/components/ProfileMenu";

export default function KSProfile() {
  const otherItems: any[] = [];

  return <ProfileMenu allowedRoles={["kepala sekolah"]} editPath="/kepala-sekolah/profile/edit" otherItems={otherItems} />;
}
