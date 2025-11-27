"use client";

import ProfileEdit from "@/components/ProfileEdit";

export default function AdminProfileEdit() {
  return <ProfileEdit allowedRoles={["admin"]} />;
}
