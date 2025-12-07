<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OKR Flow - Achieve Your Goals with Clarity</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    @keyframes float {

      0%,
      100% {
        transform: translateY(0px);
      }

      50% {
        transform: translateY(-20px);
      }
    }

    .float-animation {
      animation: float 6s ease-in-out infinite;
    }

    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .modal-backdrop {
      backdrop-filter: blur(8px);
      background-color: rgba(0, 0, 0, 0.5);
    }
  </style>
</head>

<body class="bg-gray-50">
  <div x-data="{ 
    showLogin: false, 
    showRegister: false, 
    showForgot: false,
    showReset: false,
    resetToken: new URLSearchParams(window.location.search).get('reset-token') || '',
    openLogin() { this.showLogin = true; this.showRegister = false; this.showForgot = false; this.showReset = false; },
    openRegister() { this.showRegister = true; this.showLogin = false; this.showForgot = false; this.showReset = false; },
    openForgot() { this.showForgot = true; this.showLogin = false; this.showRegister = false; this.showReset = false; },
    openReset() { this.showReset = true; this.showLogin = false; this.showRegister = false; this.showForgot = false; },
    closeAll() { this.showLogin = false; this.showRegister = false; this.showForgot = false; this.showReset = false; }
  }" x-init="if (resetToken) { openReset(); window.history.replaceState({}, '', '/'); }">

    <nav class="bg-white shadow-sm sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span class="ml-2 text-2xl font-bold gradient-text">OKR Flow</span>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="openLogin" class="text-gray-700 hover:text-purple-600 font-medium transition">
              Sign In
            </button>
            <button @click="openRegister" class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-6 py-2 rounded-lg hover:shadow-lg transition transform hover:-translate-y-0.5">
              Get Started
            </button>
          </div>
        </div>
      </div>
    </nav>

    <section class="relative py-20 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-blue-50"></div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <h1 class="text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
              Transform Your Goals Into
              <span class="gradient-text">Measurable Results</span>
            </h1>
            <p class="text-xl text-gray-600 mb-8">
              OKR Flow helps teams align objectives, track progress, and achieve ambitious goals with clarity and focus.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
              <button @click="openRegister" class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:shadow-xl transition transform hover:-translate-y-1">
                Start Free Today
              </button>
              <button @click="openLogin" class="bg-white text-purple-600 border-2 border-purple-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-purple-50 transition">
                Sign In
              </button>
            </div>
          </div>
          <div class="relative float-animation">
            <div class="bg-white rounded-2xl shadow-2xl p-8">
              <div class="space-y-6">
                <div class="flex items-start space-x-4">
                  <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold">
                    O
                  </div>
                  <div class="flex-1">
                    <div class="h-4 bg-purple-200 rounded w-3/4 mb-2"></div>
                    <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                  </div>
                </div>
                <div class="ml-8 space-y-3">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                      KR
                    </div>
                    <div class="flex-1">
                      <div class="h-2 bg-blue-200 rounded w-full"></div>
                    </div>
                    <span class="text-sm font-semibold text-blue-600">75%</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                      KR
                    </div>
                    <div class="flex-1">
                      <div class="h-2 bg-green-200 rounded w-full"></div>
                    </div>
                    <span class="text-sm font-semibold text-green-600">100%</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                      KR
                    </div>
                    <div class="flex-1">
                      <div class="h-2 bg-yellow-200 rounded w-full"></div>
                    </div>
                    <span class="text-sm font-semibold text-yellow-600">50%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Teams Choose OKR Flow</h2>
          <p class="text-xl text-gray-600">Everything you need to set, track, and achieve your objectives</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="bg-gradient-to-br from-purple-50 to-blue-50 p-8 rounded-2xl hover:shadow-xl transition">
            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Visual Progress Tracking</h3>
            <p class="text-gray-600">
              Watch your goals come to life with intuitive visualizations. Track key results in real-time and celebrate wins together.
            </p>
          </div>

          <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-8 rounded-2xl hover:shadow-xl transition">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Team Collaboration</h3>
            <p class="text-gray-600">
              Align everyone with shared objectives. Comment, discuss, and collaborate seamlessly across teams and departments.
            </p>
          </div>

          <div class="bg-gradient-to-br from-green-50 to-blue-50 p-8 rounded-2xl hover:shadow-xl transition">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Measurable Outcomes</h3>
            <p class="text-gray-600">
              Define clear, quantifiable key results. Move from vague goals to concrete achievements with data-driven insights.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-gradient-to-br from-purple-600 to-purple-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-4xl font-bold text-white mb-6">How OKR Flow Works</h2>
            <div class="space-y-6">
              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0">
                  <span class="text-2xl font-bold text-white">1</span>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-white mb-2">Set Clear Objectives</h3>
                  <p class="text-purple-100">
                    Define what you want to achieve. Make it ambitious, inspiring, and time-bound.
                  </p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0">
                  <span class="text-2xl font-bold text-white">2</span>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-white mb-2">Define Key Results</h3>
                  <p class="text-purple-100">
                    Break down objectives into measurable outcomes. Track progress from 0 to 100%.
                  </p>
                </div>
              </div>

              <div class="flex items-start space-x-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0">
                  <span class="text-2xl font-bold text-white">3</span>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-white mb-2">Track & Achieve</h3>
                  <p class="text-purple-100">
                    Update progress regularly, collaborate with your team, and celebrate milestones.
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-2xl p-8">
            <div class="bg-white rounded-xl p-6 shadow-2xl">
              <h4 class="text-lg font-bold text-gray-900 mb-4">Example Objective</h4>
              <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-4 mb-4">
                <p class="font-semibold text-gray-900">Launch new product successfully</p>
              </div>
              <div class="space-y-3">
                <div class="bg-gray-50 rounded-lg p-3">
                  <div class="flex justify-between items-center mb-2">
                    <p class="text-sm text-gray-700">Reach 10,000 users</p>
                    <span class="text-sm font-bold text-green-600">85%</span>
                  </div>
                  <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-green-500 to-green-600" style="width: 85%"></div>
                  </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                  <div class="flex justify-between items-center mb-2">
                    <p class="text-sm text-gray-700">Achieve 4.5★ rating</p>
                    <span class="text-sm font-bold text-blue-600">70%</span>
                  </div>
                  <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600" style="width: 70%"></div>
                  </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                  <div class="flex justify-between items-center mb-2">
                    <p class="text-sm text-gray-700">Generate $100K revenue</p>
                    <span class="text-sm font-bold text-purple-600">60%</span>
                  </div>
                  <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-purple-600" style="width: 60%"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-gray-900 mb-6">Ready to Transform Your Goals?</h2>
        <p class="text-xl text-gray-600 mb-8">
          Join teams that are achieving more with OKR Flow. Get started in minutes.
        </p>
        <button @click="openRegister" class="bg-gradient-to-r from-purple-600 to-purple-700 text-white px-12 py-4 rounded-lg text-lg font-semibold hover:shadow-xl transition transform hover:-translate-y-1">
          Start Free Today
        </button>
      </div>
    </section>

    <footer class="bg-gray-900 text-gray-400 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="flex items-center mb-4 md:mb-0">
            <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span class="ml-2 text-xl font-bold text-white">OKR Flow</span>
          </div>
          <p class="text-sm">© 2025 OKR Flow. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <div x-show="showLogin" x-cloak class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop" @click.self="closeAll">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 relative" @click.stop>
        <button @click="closeAll" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>

        <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h2>
        <p class="text-gray-600 mb-6">Sign in to continue to OKR Flow</p>

        <div id="loginError" class="hidden bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4"></div>
        <div id="loginSuccess" class="hidden bg-green-50 text-green-600 px-4 py-3 rounded-lg mb-4"></div>

        <form id="loginForm">
          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="you@example.com">
          </div>

          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="••••••••">
          </div>

          <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition">
            Sign In
          </button>
        </form>

        <div class="mt-6 text-center space-y-2">
          <button @click="openForgot" class="text-purple-600 hover:text-purple-700 font-medium text-sm">
            Forgot your password?
          </button>
          <div class="text-gray-600">
            Don't have an account?
            <button @click="openRegister" class="text-purple-600 hover:text-purple-700 font-medium">
              Sign up
            </button>
          </div>
        </div>
      </div>
    </div>

    <div x-show="showRegister" x-cloak class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop" @click.self="closeAll">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 relative" @click.stop>
        <button @click="closeAll" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>

        <h2 class="text-3xl font-bold text-gray-900 mb-2">Get Started</h2>
        <p class="text-gray-600 mb-6">Create your account in seconds</p>

        <div id="registerError" class="hidden bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4"></div>
        <div id="registerSuccess" class="hidden bg-green-50 text-green-600 px-4 py-3 rounded-lg mb-4"></div>

        <form id="registerForm">
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">First Name</label>
              <input type="text" name="first_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="John">
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Last Name</label>
              <input type="text" name="last_name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Doe">
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="you@example.com">
          </div>

          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
            <input type="password" name="password" required minlength="8" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="••••••••">
            <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters</p>
          </div>

          <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition">
            Create Account
          </button>
        </form>

        <div class="mt-6 text-center">
          <span class="text-gray-600">Already have an account?</span>
          <button @click="openLogin" class="text-purple-600 hover:text-purple-700 font-medium ml-1">
            Sign in
          </button>
        </div>
      </div>
    </div>

    <div x-show="showForgot" x-cloak class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop" @click.self="closeAll">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 relative" @click.stop>
        <button @click="closeAll" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>

        <h2 class="text-3xl font-bold text-gray-900 mb-2">Reset Password</h2>
        <p class="text-gray-600 mb-6">Enter your email to receive a reset link</p>

        <div id="forgotError" class="hidden bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4"></div>
        <div id="forgotSuccess" class="hidden bg-green-50 text-green-600 px-4 py-3 rounded-lg mb-4"></div>

        <form id="forgotForm">
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <input type="email" name="email" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="you@example.com">
          </div>

          <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition">
            Send Reset Link
          </button>
        </form>

        <div class="mt-6 text-center">
          <button @click="openLogin" class="text-purple-600 hover:text-purple-700 font-medium">
            Back to Sign In
          </button>
        </div>
      </div>
    </div>

    <div x-show="showReset" x-cloak class="fixed inset-0 z-50 flex items-center justify-center modal-backdrop" @click.self="closeAll">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 p-8 relative" @click.stop>
        <button @click="closeAll" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>

        <h2 class="text-3xl font-bold text-gray-900 mb-2">Set Your Password</h2>
        <p class="text-gray-600 mb-6">Choose a new password for your account</p>

        <div id="resetError" class="hidden bg-red-50 text-red-600 px-4 py-3 rounded-lg mb-4"></div>
        <div id="resetSuccess" class="hidden bg-green-50 text-green-600 px-4 py-3 rounded-lg mb-4"></div>

        <form id="resetForm">
          <input type="hidden" name="token" :value="resetToken">

          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
            <input type="password" name="password" required minlength="8" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="••••••••" autocomplete="new-password">
            <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters</p>
          </div>

          <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition">
            Set Password
          </button>
        </form>

        <div class="mt-6 text-center">
          <button @click="openLogin" class="text-purple-600 hover:text-purple-700 font-medium">
            Back to Sign In
          </button>
        </div>
      </div>
    </div>

  </div>

  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>

  <script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const error = document.getElementById('loginError');
      const success = document.getElementById('loginSuccess');
      error.classList.add('hidden');
      success.classList.add('hidden');

      const formData = new FormData(e.target);

      try {
        const response = await fetch('/auth/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(formData)
        });

        const data = await response.json();

        if (response.ok) {
          success.textContent = 'Login successful! Redirecting...';
          success.classList.remove('hidden');
          setTimeout(() => {
            window.location.href = '/dashboard';
          }, 1000);
        } else {
          error.textContent = data.error || 'Login failed';
          error.classList.remove('hidden');
        }
      } catch (err) {
        error.textContent = 'Server connection error';
        error.classList.remove('hidden');
      }
    });

    document.getElementById('registerForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const error = document.getElementById('registerError');
      const success = document.getElementById('registerSuccess');
      error.classList.add('hidden');
      success.classList.add('hidden');

      const formData = new FormData(e.target);

      try {
        const response = await fetch('/auth/register', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(formData)
        });

        const data = await response.json();

        if (response.ok) {
          success.textContent = 'Account created! Redirecting...';
          success.classList.remove('hidden');
          setTimeout(() => {
            window.location.href = '/dashboard';
          }, 1000);
        } else {
          error.textContent = data.error || 'Registration failed';
          error.classList.remove('hidden');
        }
      } catch (err) {
        error.textContent = 'Server connection error';
        error.classList.remove('hidden');
      }
    });

    document.getElementById('forgotForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const error = document.getElementById('forgotError');
      const success = document.getElementById('forgotSuccess');
      error.classList.add('hidden');
      success.classList.add('hidden');

      const formData = new FormData(e.target);

      try {
        const response = await fetch('/auth/forgot-password', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(formData)
        });

        const data = await response.json();

        if (response.ok) {
          success.textContent = data.message;
          success.classList.remove('hidden');
          e.target.reset();
        } else {
          error.textContent = data.error || 'Request failed';
          error.classList.remove('hidden');
        }
      } catch (err) {
        error.textContent = 'Server connection error';
        error.classList.remove('hidden');
      }
    });

    document.getElementById('resetForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const error = document.getElementById('resetError');
      const success = document.getElementById('resetSuccess');
      error.classList.add('hidden');
      success.classList.add('hidden');

      const formData = new FormData(e.target);

      try {
        const response = await fetch('/auth/reset-password', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(formData)
        });

        const data = await response.json();

        if (response.ok) {
          success.textContent = data.message || 'Password set successfully! Redirecting...';
          success.classList.remove('hidden');
          setTimeout(() => {
            window.location.href = '/';
          }, 2000);
        } else {
          error.textContent = data.error || 'Failed to reset password';
          error.classList.remove('hidden');
        }
      } catch (err) {
        error.textContent = 'Server connection error';
        error.classList.remove('hidden');
      }
    });
  </script>
</body>

</html>