import Image from "next/image";
import Link from "next/link";
import { get } from "@/server/users";

export default async function Home() {
  const data = await get();

  return (
    <div className="p-8">
      <h1 className="text-2xl text-white font-bold mb-4">HR Darussalam System</h1>

      <div className="mb-6">
        <Link href="/login" className="bg-sky-600 hover:bg-sky-700 text-white px-6 py-3 rounded-md transition-colors duration-200 inline-block">
          Go to Login Page
        </Link>
      </div>

      <div>
        <h2 className="text-xl text-white mb-2">Users:</h2>
        {data && data.length > 0 ? (
          <ul className="list-disc pl-5 ">
            {data.map((user: any, index: number) => (
              <li key={user.id_user || index} className="mb-2 p-2 bg-gray-100 rounded">
                <div>
                  <strong>Username:</strong> {user.username}
                </div>
                <div>
                  <strong>Role:</strong> {user.role}
                </div>
                <div>
                  <strong>ID Pegawai:</strong> {user.id_pegawai}
                </div>
              </li>
            ))}
          </ul>
        ) : (
          <p className="text-gray-500">No users found or unable to fetch data.</p>
        )}
      </div>
    </div>
  );
}
