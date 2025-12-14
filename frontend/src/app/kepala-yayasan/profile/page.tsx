"use client";

import ProfileMenu from "@/components/ProfileMenu";

export default function KepalaYayasanProfile() {
  return (
    <ProfileMenu
      allowedRoles={["kepala yayasan"]}
      editPath="/kepala-yayasan/profile/edit"
      otherItems={[]}
    />
  );
}
