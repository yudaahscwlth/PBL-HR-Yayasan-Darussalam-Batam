"use client";

import ProfileEdit from "@/components/ProfileEdit";

export default function HRDProfileEdit() {
  return <ProfileEdit allowedRoles={["kepala hrd", "staff hrd"]} />;
}