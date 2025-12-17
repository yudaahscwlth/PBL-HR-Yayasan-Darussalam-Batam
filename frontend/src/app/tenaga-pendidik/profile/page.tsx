"use client";

import ProfileMenu from "@/components/ProfileMenu";

export default function TPProfile() {
  const otherItems: any[] = [];

  return <ProfileMenu allowedRoles={["tenaga pendidik"]} editPath="/tenaga-pendidik/profile/edit" otherItems={otherItems} />;
}
