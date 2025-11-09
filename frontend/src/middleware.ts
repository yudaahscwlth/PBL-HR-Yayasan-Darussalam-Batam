import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

// Define protected routes and their required roles
const protectedRoutes = {
  "/admin": ["superadmin"],
  "/hrd": ["kepala hrd", "staff hrd"],
  "/kepala-departemen": ["kepala departemen"],
  "/kepala-sekolah": ["kepala sekolah"],
  "/tenaga-pendidik": ["tenaga pendidik"],
};

// Define public routes that don't require authentication
const publicRoutes = ["/", "/unauthorized", "/profile"];

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;

  // Allow public routes
  if (publicRoutes.includes(pathname)) {
    return NextResponse.next();
  }

  // Check if the path starts with any protected route
  const matchedRoute = Object.keys(protectedRoutes).find((route) => pathname.startsWith(route));

  if (matchedRoute) {
    console.log("🔍 Middleware: Protected route accessed:", pathname);
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     */
    "/((?!api|_next/static|_next/image|favicon.ico).*)",
  ],
};
