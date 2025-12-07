<div x-data="okrApp()" x-init="init(); window.okrAppInstance = $data">
  <div id="toast-container" class="fixed top-4 right-4 z-[9999] space-y-2"></div>

  <div class="flex items-center justify-between mb-8">
    <div>
      <h1 class="text-4xl font-bold dark:text-white">Chill OKR</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1" x-text="companyInfo.name"></p>
    </div>
    <div class="flex gap-3 items-center">
      <button @click="openCompanyModal()"
        class="px-6 py-4 text-base rounded-xl bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-200 font-semibold transition-all duration-200 hover:shadow-md hover:scale-105">
        🏢 Company
      </button>
      <button @click="toggleAll()"
        class="px-6 py-4 text-base rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-white font-semibold transition-all duration-200 hover:shadow-md hover:scale-105">
        <span x-text="allExpanded ? '🔽 Collapse all' : '▶️ Expand all'"></span>
      </button>
      <button @click="openAddModal()"
        class="px-8 py-4 rounded-xl bg-slate-900 text-white hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600 font-semibold text-xl transition-all duration-200 hover:shadow-lg hover:scale-105">
        ➕ Add
      </button>
      <button onclick="toggleDarkMode()" class="px-6 py-4 text-xl rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 font-semibold transition-all duration-200 hover:shadow-md hover:scale-105" aria-label="Toggle dark mode">
        <span class="dark:hidden">🌙</span>
        <span class="hidden dark:inline">☀️</span>
      </button>
      <form method="POST" action="/auth/logout" class="inline">
        <button type="submit" class="px-6 py-4 text-base rounded-xl bg-red-100 hover:bg-red-200 dark:bg-red-900 dark:hover:bg-red-800 text-red-700 dark:text-red-200 font-semibold transition-all duration-200 hover:shadow-md hover:scale-105">
          🚪 Logout
        </button>
      </form>
    </div>
  </div>

  <template x-if="loading">
    <div class="rounded-xl border dark:border-slate-600 bg-white dark:bg-slate-800 p-8 text-lg text-slate-600 dark:text-slate-300">Loading…</div>
  </template>

  <template x-if="!loading && roots().length===0">
    <div class="rounded-xl border dark:border-slate-600 bg-white dark:bg-slate-800 p-8 text-lg text-slate-600 dark:text-slate-300">No data.</div>
  </template>

  <div class="space-y-4">
    <template x-for="n in roots()" :key="n.id">
      <div>
        <div class="node-card" :style="indentStyle(n)">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
              <div :class="n.type !== 'okr_perso' ? 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700' : ''" @click="n.type !== 'okr_perso' ? toggle(n.id) : null" class="flex items-center gap-4 -mx-3 px-3 py-2 rounded transition-colors group">
                <template x-if="n.type !== 'okr_perso'">
                  <svg class="w-5 h-5 text-slate-400 group-hover:text-slate-600 transition-transform" :class="{'rotate-90': isOpen(n.id)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </template>
                <span class="badge" :class="badgeClass(n.type)" x-text="formatTypeLabel(n.type)"></span>
                <h3 class="text-lg font-semibold truncate dark:text-white" x-text="n.title"></h3>
                <template x-if="n.type==='okr_team' && n.team_id">
                  <span class="text-sm px-3 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 font-medium" x-text="getTeamName(n.team_id)"></span>
                </template>
                <template x-if="n.type==='okr_perso' && n.user_id">
                  <span class="text-sm px-3 py-1 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 font-medium" x-text="getUserName(n.user_id)"></span>
                </template>
              </div>

              <template x-if="n.type==='okr_team' && n.owner">
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Owner: <span x-text="n.owner"></span></p>
              </template>

              <p class="text-base text-slate-600 dark:text-slate-300 mt-2 whitespace-pre-line" x-text="n.description"></p>

              <div class="mt-4 space-y-3">
                <template x-if="computedProgress(n.id)!==null">
                  <div>
                    <div class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">
                      Progress (computed): <span x-text="Math.round(computedProgress(n.id))"></span>%
                    </div>
                    <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                      <div class="h-3 bg-blue-500" :style="`width:${computedProgress(n.id)}%`"></div>
                    </div>
                  </div>
                </template>
                <div>
                  <div class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">
                    Progress (manual): <span x-text="n.progress !== null && n.progress !== undefined ? n.progress : 0"></span>%
                  </div>
                  <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-3 bg-slate-900" :style="`width:${n.progress !== null && n.progress !== undefined ? n.progress : 0}%`"></div>
                  </div>
                </div>
              </div>

              <div class="mt-4 flex flex-wrap gap-3">
                <template x-for="t in allowedChildTypes(n.type)" :key="t">
                  <button @click="openCreate(n.id,t)"
                    class="text-sm px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 dark:text-white font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                    ➕ <span x-text="labelType(t)"></span>
                  </button>
                </template>
                <template x-if="n.type === 'okr_team' || n.type === 'okr_perso'">
                  <button @click="openKeyResultsModal(n.id)"
                    class="text-sm px-4 py-2 rounded-lg bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-800 text-green-700 dark:text-green-200 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                    ➕ Key Results
                  </button>
                </template>
              </div>

              <template x-if="(n.type === 'okr_team' || n.type === 'okr_perso') && getKeyResults(n.id).length > 0">
                <div class="mt-4 space-y-3">
                  <div class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Key Results:</div>
                  <template x-for="kr in getKeyResults(n.id)" :key="kr.id">
                    <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-4 border border-slate-200 dark:border-slate-600">
                      <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                          <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-200" x-text="kr.name"></span>
                            <span class="text-sm text-slate-500">(weight: <span x-text="kr.weight"></span>)</span>
                          </div>
                          <template x-if="kr.description">
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-2" x-text="kr.description"></p>
                          </template>
                          <template x-if="kr.progress !== null && kr.progress !== undefined">
                            <div class="mt-2">
                              <div class="text-sm text-slate-600 dark:text-slate-400 mb-1 font-medium">Progress: <span x-text="kr.progress"></span>%</div>
                              <div class="h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-2.5 bg-green-500" :style="`width:${kr.progress}%`"></div>
                              </div>
                            </div>
                          </template>
                        </div>
                        <div class="flex gap-2">
                          <button @click="openEditKeyResult(kr, n.id)"
                            class="text-sm px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                            ✏️ Edit
                          </button>
                          <button @click="deleteKeyResult(kr.id, n.id)"
                            class="text-sm px-3 py-1.5 rounded bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                            🗑️ Delete
                          </button>
                        </div>
                      </div>
                    </div>
                  </template>
                </div>
              </template>
            </div>

            <div class="flex shrink-0 gap-3">
              <button @click="openComments(n.id)"
                class="text-base px-4 py-2 rounded-lg bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 dark:text-blue-200 font-medium transition-all duration-200 hover:shadow-md hover:scale-105">
                💬 Comment
              </button>
              <button @click="openEdit(n)"
                class="text-base px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white font-medium transition-all duration-200 hover:shadow-md hover:scale-105">
                ✏️ Edit
              </button>
              <button @click="remove(n.id)"
                class="text-base px-4 py-2 rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 font-medium transition-all duration-200 hover:shadow-md hover:scale-105">
                🗑️ Delete
              </button>
            </div>
          </div>
        </div>

        <template x-if="n.type !== 'okr_perso' && isOpen(n.id)">
          <div class="space-y-2">
            <template x-for="c in childrenOf(n.id)" :key="c.id">
              <div>
                <div class="node-card" :style="indentStyle(c)">
                  <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                      <div :class="c.type !== 'okr_perso' ? 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700' : ''" @click="c.type !== 'okr_perso' ? toggle(c.id) : null" class="flex items-center gap-3 -mx-2 px-2 py-1.5 rounded transition-colors group">
                        <template x-if="c.type !== 'okr_perso'">
                          <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform" :class="{'rotate-90': isOpen(c.id)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                          </svg>
                        </template>
                        <span class="badge" :class="badgeClass(c.type)" x-text="formatTypeLabel(c.type)"></span>
                        <h3 class="font-semibold truncate dark:text-white" x-text="c.title"></h3>
                        <template x-if="c.type==='okr_team' && c.team_id">
                          <span class="text-xs px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 font-medium" x-text="getTeamName(c.team_id)"></span>
                        </template>
                        <template x-if="c.type==='okr_perso' && c.user_id">
                          <span class="text-xs px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 font-medium" x-text="getUserName(c.user_id)"></span>
                        </template>
                      </div>
                      <template x-if="c.type==='okr_team' && c.owner">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Owner: <span x-text="c.owner"></span></p>
                      </template>
                      <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line" x-text="c.description"></p>

                      <div class="mt-2 space-y-2">
                        <template x-if="computedProgress(c.id)!==null">
                          <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                              Progress (computed): <span x-text="Math.round(computedProgress(c.id))"></span>%
                            </div>
                            <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                              <div class="h-2 bg-blue-500" :style="`width:${computedProgress(c.id)}%`"></div>
                            </div>
                          </div>
                        </template>
                        <div>
                          <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                            Progress (manual): <span x-text="c.progress !== null && c.progress !== undefined ? c.progress : 0"></span>%
                          </div>
                          <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-2 bg-slate-900" :style="`width:${c.progress !== null && c.progress !== undefined ? c.progress : 0}%`"></div>
                          </div>
                        </div>
                      </div>

                      <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="t in allowedChildTypes(c.type)" :key="t">
                          <button @click="openCreate(c.id,t)"
                            class="text-xs px-2 py-1 rounded-md bg-slate-50 dark:bg-slate-700 border dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                            ➕ <span x-text="labelType(t)"></span>
                          </button>
                        </template>
                        <template x-if="c.type === 'okr_team' || c.type === 'okr_perso'">
                          <button @click="openKeyResultsModal(c.id)"
                            class="text-xs px-2 py-1 rounded-md bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-800 text-green-700 dark:text-green-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                            ➕ Key Results
                          </button>
                        </template>
                      </div>

                      <template x-if="(c.type === 'okr_team' || c.type === 'okr_perso') && getKeyResults(c.id).length > 0">
                        <div class="mt-3 space-y-2">
                          <div class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Key Results:</div>
                          <template x-for="kr in getKeyResults(c.id)" :key="kr.id">
                            <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-2 border border-slate-200 dark:border-slate-600">
                              <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                  <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200" x-text="kr.name"></span>
                                    <span class="text-xs text-slate-500">(weight: <span x-text="kr.weight"></span>)</span>
                                  </div>
                                  <template x-if="kr.description">
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="kr.description"></p>
                                  </template>
                                  <template x-if="kr.progress !== null && kr.progress !== undefined">
                                    <div class="mt-1">
                                      <div class="text-xs text-slate-500 mb-0.5">Progress: <span x-text="kr.progress"></span>%</div>
                                      <div class="h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-1.5 bg-green-500" :style="`width:${kr.progress}%`"></div>
                                      </div>
                                    </div>
                                  </template>
                                </div>
                                <div class="flex gap-1">
                                  <button @click="openEditKeyResult(kr, c.id)"
                                    class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                    ✏️ Edit
                                  </button>
                                  <button @click="deleteKeyResult(kr.id, c.id)"
                                    class="text-xs px-2 py-0.5 rounded bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                    🗑️ Delete
                                  </button>
                                </div>
                              </div>
                            </div>
                          </template>
                        </div>
                      </template>
                    </div>

                    <div class="flex shrink-0 gap-2">
                      <button @click="openComments(c.id)"
                        class="text-sm px-3 py-1 rounded-lg bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 dark:text-blue-200 transition-all duration-200 hover:shadow-md hover:scale-105">
                        💬 Comment
                      </button>
                      <button @click="openEdit(c)"
                        class="text-sm px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-md hover:scale-105">
                        ✏️ Edit
                      </button>
                      <button @click="remove(c.id)"
                        class="text-sm px-3 py-1 rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-md hover:scale-105">
                        🗑️ Delete
                      </button>
                    </div>
                  </div>
                </div>

                <template x-if="c.type !== 'okr_perso' && isOpen(c.id)">
                  <div class="space-y-2">
                    <template x-for="child in childrenOf(c.id)" :key="child.id">
                      <div>
                        <div class="node-card" :style="indentStyle(child)">
                          <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                              <div :class="child.type !== 'okr_perso' ? 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700' : ''" @click="child.type !== 'okr_perso' ? toggle(child.id) : null" class="flex items-center gap-3 -mx-2 px-2 py-1.5 rounded transition-colors group">
                                <template x-if="child.type !== 'okr_perso'">
                                  <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform" :class="{'rotate-90': isOpen(child.id)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                  </svg>
                                </template>
                                <span class="badge" :class="badgeClass(child.type)" x-text="formatTypeLabel(child.type)"></span>
                                <h3 class="font-semibold truncate dark:text-white" x-text="child.title"></h3>
                                <template x-if="child.type==='okr_team' && child.team_id">
                                  <span class="text-xs px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 font-medium" x-text="getTeamName(child.team_id)"></span>
                                </template>
                                <template x-if="child.type==='okr_perso' && child.user_id">
                                  <span class="text-xs px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 font-medium" x-text="getUserName(child.user_id)"></span>
                                </template>
                              </div>
                              <template x-if="child.type==='okr_team' && child.owner">
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Owner: <span x-text="child.owner"></span></p>
                              </template>
                              <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line" x-text="child.description"></p>
                              <template x-if="child.type!=='company'">
                                <div class="mt-2 space-y-2">
                                  <template x-if="computedProgress(child.id)!==null">
                                    <div>
                                      <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                        Progress (computed): <span x-text="Math.round(computedProgress(child.id))"></span>%
                                      </div>
                                      <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-2 bg-blue-500" :style="`width:${computedProgress(child.id)}%`"></div>
                                      </div>
                                    </div>
                                  </template>
                                  <div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                      Progress (manual): <span x-text="child.progress !== null && child.progress !== undefined ? child.progress : 0"></span>%
                                    </div>
                                    <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                      <div class="h-2 bg-slate-900" :style="`width:${child.progress !== null && child.progress !== undefined ? child.progress : 0}%`"></div>
                                    </div>
                                  </div>
                                </div>
                              </template>
                              <div class="mt-2 flex flex-wrap gap-2">
                                <template x-for="t in allowedChildTypes(child.type)" :key="t">
                                  <button @click="openCreate(child.id, t)"
                                    class="text-xs px-2 py-1 rounded-md bg-slate-50 dark:bg-slate-700 border dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                    ➕ <span x-text="labelType(t)"></span>
                                  </button>
                                </template>
                                <template x-if="child.type === 'okr_team' || child.type === 'okr_perso'">
                                  <button @click="openKeyResultsModal(child.id)"
                                    class="text-xs px-2 py-1 rounded-md bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-800 text-green-700 dark:text-green-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                    ➕ Key Results
                                  </button>
                                </template>
                              </div>

                              <template x-if="(child.type === 'okr_team' || child.type === 'okr_perso') && getKeyResults(child.id).length > 0">
                                <div class="mt-3 space-y-2">
                                  <div class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Key Results:</div>
                                  <template x-for="kr in getKeyResults(child.id)" :key="kr.id">
                                    <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-2 border border-slate-200 dark:border-slate-600">
                                      <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                          <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200" x-text="kr.name"></span>
                                            <span class="text-xs text-slate-500">(weight: <span x-text="kr.weight"></span>)</span>
                                          </div>
                                          <template x-if="kr.description">
                                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="kr.description"></p>
                                          </template>
                                          <template x-if="kr.progress !== null && kr.progress !== undefined">
                                            <div class="mt-1">
                                              <div class="text-xs text-slate-500 mb-0.5">Progress: <span x-text="kr.progress"></span>%</div>
                                              <div class="h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                <div class="h-1.5 bg-green-500" :style="`width:${kr.progress}%`"></div>
                                              </div>
                                            </div>
                                          </template>
                                        </div>
                                        <div class="flex gap-1">
                                          <button @click="openEditKeyResult(kr, child.id)"
                                            class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                            ✏️ Edit
                                          </button>
                                          <button @click="deleteKeyResult(kr.id, child.id)"
                                            class="text-xs px-2 py-0.5 rounded bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                            🗑️ Delete
                                          </button>
                                        </div>
                                      </div>
                                    </div>
                                  </template>
                                </div>
                              </template>
                            </div>
                            <div class="flex shrink-0 gap-2">
                              <button @click="openComments(child.id)"
                                class="text-sm px-3 py-1 rounded-lg bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 dark:text-blue-200 transition-all duration-200 hover:shadow-md hover:scale-105">💬 Comment</button>
                              <button @click="openEdit(child)"
                                class="text-sm px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-md hover:scale-105">✏️ Edit</button>
                              <button @click="remove(child.id)"
                                class="text-sm px-3 py-1 rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-md hover:scale-105">🗑️ Delete</button>
                            </div>
                          </div>
                        </div>
                        <template x-if="child.type !== 'okr_perso' && isOpen(child.id)">
                          <div class="space-y-2">
                            <template x-for="grandchild in childrenOf(child.id)" :key="grandchild.id">
                              <div>
                                <div class="node-card" :style="indentStyle(grandchild)">
                                  <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                      <div :class="grandchild.type !== 'okr_perso' ? 'cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700' : ''" @click="grandchild.type !== 'okr_perso' ? toggle(grandchild.id) : null" class="flex items-center gap-3 -mx-2 px-2 py-1.5 rounded transition-colors group">
                                        <template x-if="grandchild.type !== 'okr_perso'">
                                          <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform" :class="{'rotate-90': isOpen(grandchild.id)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                          </svg>
                                        </template>
                                        <span class="badge" :class="badgeClass(grandchild.type)" x-text="formatTypeLabel(grandchild.type)"></span>
                                        <h3 class="font-semibold truncate dark:text-white" x-text="grandchild.title"></h3>
                                        <template x-if="grandchild.type==='okr_team' && grandchild.team_id">
                                          <span class="text-xs px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 font-medium" x-text="getTeamName(grandchild.team_id)"></span>
                                        </template>
                                        <template x-if="grandchild.type==='okr_perso' && grandchild.user_id">
                                          <span class="text-xs px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 font-medium" x-text="getUserName(grandchild.user_id)"></span>
                                        </template>
                                      </div>
                                      <template x-if="grandchild.type==='okr_team' && grandchild.owner">
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Owner: <span x-text="grandchild.owner"></span></p>
                                      </template>
                                      <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line" x-text="grandchild.description"></p>
                                      <template x-if="grandchild.type!=='company'">
                                        <div class="mt-2 space-y-2">
                                          <template x-if="computedProgress(grandchild.id)!==null">
                                            <div>
                                              <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                                Progress (computed): <span x-text="Math.round(computedProgress(grandchild.id))"></span>%
                                              </div>
                                              <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                <div class="h-2 bg-blue-500" :style="`width:${computedProgress(grandchild.id)}%`"></div>
                                              </div>
                                            </div>
                                          </template>
                                          <div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                              Progress (manual): <span x-text="grandchild.progress !== null && grandchild.progress !== undefined ? grandchild.progress : 0"></span>%
                                            </div>
                                            <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                              <div class="h-2 bg-slate-900" :style="`width:${grandchild.progress !== null && grandchild.progress !== undefined ? grandchild.progress : 0}%`"></div>
                                            </div>
                                          </div>
                                        </div>
                                      </template>
                                      <div class="mt-2 flex flex-wrap gap-2">
                                        <template x-for="t in allowedChildTypes(grandchild.type)" :key="t">
                                          <button @click="openCreate(grandchild.id, t)"
                                            class="text-xs px-2 py-1 rounded-md bg-slate-50 dark:bg-slate-700 border dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                            ➕ <span x-text="labelType(t)"></span>
                                          </button>
                                        </template>
                                        <template x-if="grandchild.type === 'okr_team' || grandchild.type === 'okr_perso'">
                                          <button @click="openKeyResultsModal(grandchild.id)"
                                            class="text-xs px-2 py-1 rounded-md bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-800 text-green-700 dark:text-green-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                            ➕ Key Results
                                          </button>
                                        </template>
                                      </div>

                                      <template x-if="(grandchild.type === 'okr_team' || grandchild.type === 'okr_perso') && getKeyResults(grandchild.id).length > 0">
                                        <div class="mt-3 space-y-2">
                                          <div class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Key Results:</div>
                                          <template x-for="kr in getKeyResults(grandchild.id)" :key="kr.id">
                                            <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-2 border border-slate-200 dark:border-slate-600">
                                              <div class="flex items-start justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                  <div class="flex items-center gap-2">
                                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200" x-text="kr.name"></span>
                                                    <span class="text-xs text-slate-500">(weight: <span x-text="kr.weight"></span>)</span>
                                                  </div>
                                                  <template x-if="kr.description">
                                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="kr.description"></p>
                                                  </template>
                                                  <template x-if="kr.progress !== null && kr.progress !== undefined">
                                                    <div class="mt-1">
                                                      <div class="text-xs text-slate-500 mb-0.5">Progress: <span x-text="kr.progress"></span>%</div>
                                                      <div class="h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                        <div class="h-1.5 bg-green-500" :style="`width:${kr.progress}%`"></div>
                                                      </div>
                                                    </div>
                                                  </template>
                                                </div>
                                                <div class="flex gap-1">
                                                  <button @click="openEditKeyResult(kr, grandchild.id)"
                                                    class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                    ✏️ Edit
                                                  </button>
                                                  <button @click="deleteKeyResult(kr.id, grandchild.id)"
                                                    class="text-xs px-2 py-0.5 rounded bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                    🗑️ Delete
                                                  </button>
                                                </div>
                                              </div>
                                            </div>
                                          </template>
                                        </div>
                                      </template>
                                    </div>
                                    <div class="flex shrink-0 gap-2">
                                      <button @click="openComments(grandchild.id)"
                                        class="text-sm px-3 py-1 rounded-lg bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 dark:text-blue-200 transition-all duration-200 hover:shadow-md hover:scale-105">💬 Comment</button>
                                      <button @click="openEdit(grandchild)"
                                        class="text-sm px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-md hover:scale-105">✏️ Edit</button>
                                      <button @click="remove(grandchild.id)"
                                        class="text-sm px-3 py-1 rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-md hover:scale-105">🗑️ Delete</button>
                                    </div>
                                  </div>
                                </div>
                                <template x-if="grandchild.type !== 'okr_perso' && isOpen(grandchild.id)">
                                  <div class="space-y-2">
                                    <template x-for="greatgrandchild in childrenOf(grandchild.id)" :key="greatgrandchild.id">
                                      <div>
                                        <div class="node-card" :style="indentStyle(greatgrandchild)">
                                          <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0 flex-1">
                                              <div :class="greatgrandchild.type !== 'okr_perso' ? 'cursor-pointer hover:bg-slate-50' : ''" @click="greatgrandchild.type !== 'okr_perso' ? toggle(greatgrandchild.id) : null" class="flex items-center gap-3 -mx-2 px-2 py-1.5 rounded transition-colors group">
                                                <template x-if="greatgrandchild.type !== 'okr_perso'">
                                                  <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform" :class="{'rotate-90': isOpen(greatgrandchild.id)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                  </svg>
                                                </template>
                                                <span class="badge" :class="badgeClass(greatgrandchild.type)" x-text="formatTypeLabel(greatgrandchild.type)"></span>
                                                <h3 class="font-semibold truncate dark:text-white" x-text="greatgrandchild.title"></h3>
                                                <template x-if="greatgrandchild.type==='okr_team' && greatgrandchild.team_id">
                                                  <span class="text-xs px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 font-medium" x-text="getTeamName(greatgrandchild.team_id)"></span>
                                                </template>
                                                <template x-if="greatgrandchild.type==='okr_perso' && greatgrandchild.user_id">
                                                  <span class="text-xs px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 font-medium" x-text="getUserName(greatgrandchild.user_id)"></span>
                                                </template>
                                              </div>
                                              <template x-if="greatgrandchild.type==='okr_team' && greatgrandchild.owner">
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Owner: <span x-text="greatgrandchild.owner"></span></p>
                                              </template>
                                              <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line" x-text="greatgrandchild.description"></p>
                                              <template x-if="greatgrandchild.type!=='company'">
                                                <div class="mt-2 space-y-2">
                                                  <template x-if="computedProgress(greatgrandchild.id)!==null">
                                                    <div>
                                                      <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                                        Progress (computed): <span x-text="Math.round(computedProgress(greatgrandchild.id))"></span>%
                                                      </div>
                                                      <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                        <div class="h-2 bg-blue-500" :style="`width:${computedProgress(greatgrandchild.id)}%`"></div>
                                                      </div>
                                                    </div>
                                                  </template>
                                                  <div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                                      Progress (manual): <span x-text="greatgrandchild.progress !== null && greatgrandchild.progress !== undefined ? greatgrandchild.progress : 0"></span>%
                                                    </div>
                                                    <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                      <div class="h-2 bg-slate-900" :style="`width:${greatgrandchild.progress !== null && greatgrandchild.progress !== undefined ? greatgrandchild.progress : 0}%`"></div>
                                                    </div>
                                                  </div>
                                                </div>
                                              </template>
                                              <div class="mt-2 flex flex-wrap gap-2">
                                                <template x-for="t in allowedChildTypes(greatgrandchild.type)" :key="t">
                                                  <button @click="openCreate(greatgrandchild.id, t)"
                                                    class="text-xs px-2 py-1 rounded-md bg-slate-50 dark:bg-slate-700 border dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                    ➕ <span x-text="labelType(t)"></span>
                                                  </button>
                                                </template>
                                                <template x-if="greatgrandchild.type === 'okr_team' || greatgrandchild.type === 'okr_perso'">
                                                  <button @click="openKeyResultsModal(greatgrandchild.id)"
                                                    class="text-xs px-2 py-1 rounded-md bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-800 text-green-700 dark:text-green-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                    ➕ Key Results
                                                  </button>
                                                </template>
                                              </div>

                                              <template x-if="(greatgrandchild.type === 'okr_team' || greatgrandchild.type === 'okr_perso') && getKeyResults(greatgrandchild.id).length > 0">
                                                <div class="mt-3 space-y-2">
                                                  <div class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Key Results:</div>
                                                  <template x-for="kr in getKeyResults(greatgrandchild.id)" :key="kr.id">
                                                    <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-2 border border-slate-200 dark:border-slate-600">
                                                      <div class="flex items-start justify-between gap-2">
                                                        <div class="flex-1 min-w-0">
                                                          <div class="flex items-center gap-2">
                                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200" x-text="kr.name"></span>
                                                            <span class="text-xs text-slate-500">(weight: <span x-text="kr.weight"></span>)</span>
                                                          </div>
                                                          <template x-if="kr.description">
                                                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="kr.description"></p>
                                                          </template>
                                                          <template x-if="kr.progress !== null && kr.progress !== undefined">
                                                            <div class="mt-1">
                                                              <div class="text-xs text-slate-500 mb-0.5">Progress: <span x-text="kr.progress"></span>%</div>
                                                              <div class="h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                                <div class="h-1.5 bg-green-500" :style="`width:${kr.progress}%`"></div>
                                                              </div>
                                                            </div>
                                                          </template>
                                                        </div>
                                                        <div class="flex gap-1">
                                                          <button @click="openEditKeyResult(kr, greatgrandchild.id)"
                                                            class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                            ✏️ Edit
                                                          </button>
                                                          <button @click="deleteKeyResult(kr.id, greatgrandchild.id)"
                                                            class="text-xs px-2 py-0.5 rounded bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                            🗑️ Delete
                                                          </button>
                                                        </div>
                                                      </div>
                                                    </div>
                                                  </template>
                                                </div>
                                              </template>
                                            </div>
                                            <div class="flex shrink-0 gap-2">
                                              <button @click="openComments(greatgrandchild.id)"
                                                class="text-sm px-3 py-1 rounded-lg bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 dark:text-blue-200 transition-all duration-200 hover:shadow-md hover:scale-105">💬 Comment</button>
                                              <button @click="openEdit(greatgrandchild)"
                                                class="text-sm px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-md hover:scale-105">✏️ Edit</button>
                                              <button @click="remove(greatgrandchild.id)"
                                                class="text-sm px-3 py-1 rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-md hover:scale-105">🗑️ Delete</button>
                                            </div>
                                          </div>
                                        </div>
                                        <template x-if="greatgrandchild.type !== 'okr_perso' && isOpen(greatgrandchild.id)">
                                          <div class="space-y-2">
                                            <template x-for="okrPerso in childrenOf(greatgrandchild.id)" :key="okrPerso.id">
                                              <div>
                                                <div class="node-card" :style="indentStyle(okrPerso)">
                                                  <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0 flex-1">
                                                      <div :class="okrPerso.type !== 'okr_perso' ? 'cursor-pointer hover:bg-slate-50' : ''" @click="okrPerso.type !== 'okr_perso' ? toggle(okrPerso.id) : null" class="flex items-center gap-3 -mx-2 px-2 py-1.5 rounded transition-colors group">
                                                        <template x-if="okrPerso.type !== 'okr_perso'">
                                                          <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600 transition-transform" :class="{'rotate-90': isOpen(okrPerso.id)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                          </svg>
                                                        </template>
                                                        <span class="badge" :class="badgeClass(okrPerso.type)" x-text="formatTypeLabel(okrPerso.type)"></span>
                                                        <h3 class="font-semibold truncate dark:text-white" x-text="okrPerso.title"></h3>
                                                        <template x-if="okrPerso.type==='okr_team' && okrPerso.team_id">
                                                          <span class="text-xs px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 font-medium" x-text="getTeamName(okrPerso.team_id)"></span>
                                                        </template>
                                                        <template x-if="okrPerso.type==='okr_perso' && okrPerso.user_id">
                                                          <span class="text-xs px-2 py-0.5 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 font-medium" x-text="getUserName(okrPerso.user_id)"></span>
                                                        </template>
                                                      </div>
                                                      <template x-if="okrPerso.type==='okr_team' && okrPerso.owner">
                                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Owner: <span x-text="okrPerso.owner"></span></p>
                                                      </template>
                                                      <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 whitespace-pre-line" x-text="okrPerso.description"></p>
                                                      <template x-if="okrPerso.type!=='company'">
                                                        <div class="mt-2 space-y-2">
                                                          <template x-if="computedProgress(okrPerso.id)!==null">
                                                            <div>
                                                              <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                                                Progress (computed): <span x-text="Math.round(computedProgress(okrPerso.id))"></span>%
                                                              </div>
                                                              <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                                <div class="h-2 bg-blue-500" :style="`width:${computedProgress(okrPerso.id)}%`"></div>
                                                              </div>
                                                            </div>
                                                          </template>
                                                          <div>
                                                            <div class="text-xs text-slate-500 dark:text-slate-400 mb-1">
                                                              Progress (manual): <span x-text="okrPerso.progress !== null && okrPerso.progress !== undefined ? okrPerso.progress : 0"></span>%
                                                            </div>
                                                            <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                              <div class="h-2 bg-slate-900" :style="`width:${okrPerso.progress !== null && okrPerso.progress !== undefined ? okrPerso.progress : 0}%`"></div>
                                                            </div>
                                                          </div>
                                                        </div>
                                                      </template>
                                                      <div class="mt-2 flex flex-wrap gap-2">
                                                        <template x-for="t in allowedChildTypes(okrPerso.type)" :key="t">
                                                          <button @click="openCreate(okrPerso.id, t)"
                                                            class="text-xs px-2 py-1 rounded-md bg-slate-50 dark:bg-slate-700 border dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                            ➕ <span x-text="labelType(t)"></span>
                                                          </button>
                                                        </template>
                                                        <template x-if="okrPerso.type === 'okr_team' || okrPerso.type === 'okr_perso'">
                                                          <button @click="openKeyResultsModal(okrPerso.id)"
                                                            class="text-xs px-2 py-1 rounded-md bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 hover:bg-green-100 dark:hover:bg-green-800 text-green-700 dark:text-green-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                            ➕ Key Results
                                                          </button>
                                                        </template>
                                                      </div>

                                                      <template x-if="(okrPerso.type === 'okr_team' || okrPerso.type === 'okr_perso') && getKeyResults(okrPerso.id).length > 0">
                                                        <div class="mt-3 space-y-2">
                                                          <div class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-1">Key Results:</div>
                                                          <template x-for="kr in getKeyResults(okrPerso.id)" :key="kr.id">
                                                            <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-2 border border-slate-200 dark:border-slate-600">
                                                              <div class="flex items-start justify-between gap-2">
                                                                <div class="flex-1 min-w-0">
                                                                  <div class="flex items-center gap-2">
                                                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200" x-text="kr.name"></span>
                                                                    <span class="text-xs text-slate-500">(weight: <span x-text="kr.weight"></span>)</span>
                                                                  </div>
                                                                  <template x-if="kr.description">
                                                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="kr.description"></p>
                                                                  </template>
                                                                  <template x-if="kr.progress !== null && kr.progress !== undefined">
                                                                    <div class="mt-1">
                                                                      <div class="text-xs text-slate-500 mb-0.5">Progress: <span x-text="kr.progress"></span>%</div>
                                                                      <div class="h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                                        <div class="h-1.5 bg-green-500" :style="`width:${kr.progress}%`"></div>
                                                                      </div>
                                                                    </div>
                                                                  </template>
                                                                </div>
                                                                <div class="flex gap-1">
                                                                  <button @click="openEditKeyResult(kr, okrPerso.id)"
                                                                    class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                                    ✏️ Edit
                                                                  </button>
                                                                  <button @click="deleteKeyResult(kr.id, okrPerso.id)"
                                                                    class="text-xs px-2 py-0.5 rounded bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                                                                    🗑️ Delete
                                                                  </button>
                                                                </div>
                                                              </div>
                                                            </div>
                                                          </template>
                                                        </div>
                                                      </template>
                                                    </div>
                                                    <div class="flex shrink-0 gap-2">
                                                      <button @click="openComments(okrPerso.id)"
                                                        class="text-sm px-3 py-1 rounded-lg bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 dark:text-blue-200 transition-all duration-200 hover:shadow-md hover:scale-105">💬 Comment</button>
                                                      <button @click="openEdit(okrPerso)"
                                                        class="text-sm px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-md hover:scale-105">✏️ Edit</button>
                                                      <button @click="remove(okrPerso.id)"
                                                        class="text-sm px-3 py-1 rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-md hover:scale-105">🗑️ Delete</button>
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            </template>
                                          </div>
                                        </template>
                                      </div>
                                    </template>
                                  </div>
                                </template>
                              </div>
                            </template>
                          </div>
                        </template>
                      </div>
                    </template>
                  </div>
                </template>
              </div>
            </template>
          </div>
        </template>
      </div>
    </template>
  </div>

  <template x-if="modal.open">
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-[100]">
      <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-800 border dark:border-slate-600 shadow-2xl p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold dark:text-white" x-text="modal.mode==='edit' ? 'Edit' : (modal.mode==='add' ? 'Add' : 'Create')"></h2>
          <button @click="closeModal()" class="text-2xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">✕</button>
        </div>

        <div class="space-y-5">
          <template x-if="modal.mode==='add'">
            <div>
              <label class="text-base font-semibold dark:text-slate-200 dark:text-slate-200">Type</label>
              <select x-model="form.type" @change="updateParentOptions()" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
                <option value="">-- Choose a type --</option>
                <option value="axis">Axis</option>
                <option value="okr_team">OKR Team</option>
                <option value="okr_perso">OKR Perso</option>
              </select>
            </div>
          </template>

          <template x-if="modal.mode==='add' && form.type">
            <div>
              <label class="text-base font-semibold dark:text-slate-200 dark:text-slate-200">Parent</label>
              <select x-model="form.parent_id" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
                <option value="">-- No parent (root) --</option>
                <template x-for="p in availableParents()" :key="p.id">
                  <option :value="String(p.id)" x-text="`${p.title} (${labelType(p.type)})`"></option>
                </template>
              </select>
            </div>
          </template>

          <div>
            <label class="text-base font-semibold dark:text-slate-200 dark:text-slate-200">Title</label>
            <input x-model="form.title" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
          </div>

          <div>
            <label class="text-base font-semibold dark:text-slate-200 dark:text-slate-200">Description</label>
            <textarea x-model="form.description" rows="4" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3"></textarea>
          </div>

          <template x-if="form.type==='okr_team'">
            <div>
              <label class="text-base font-semibold dark:text-slate-200">Owner (User)</label>
              <div class="mt-2 space-y-3">
                <template x-if="!showNewOwnerInput">
                  <div class="flex gap-3">
                    <select x-model="form.owner_id" class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
                      <option value="">-- Select a user --</option>
                      <template x-for="user in users" :key="user.id">
                        <option :value="String(user.id)"
                          x-text="`${user.first_name} ${user.last_name}`"></option>
                      </template>
                    </select>
                    <button @click="showNewOwnerInput = true" type="button"
                      class="px-4 py-3 text-base rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white border dark:border-slate-600 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                      ➕ Invite
                    </button>
                  </div>
                </template>
                <template x-if="showNewOwnerInput">
                  <div class="space-y-3">
                    <div class="flex gap-3">
                      <input x-model="form.owner_first_name" placeholder="First name"
                        class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
                      <input x-model="form.owner_last_name" placeholder="Last name"
                        class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
                    </div>
                    <input x-model="form.owner_email" type="email" placeholder="Email"
                      class="w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
                    <div class="flex gap-3">
                      <button @click="inviteOwner()" type="button"
                        class="flex-1 px-4 py-3 text-base rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-all duration-200 hover:shadow-lg hover:scale-105">
                        ✉️ Send Invitation
                      </button>
                      <button @click="showNewOwnerInput = false; form.owner_first_name = ''; form.owner_last_name = ''; form.owner_email = ''" type="button"
                        class="flex-1 px-4 py-3 text-base rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white border dark:border-slate-600 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                        ❌ Cancel
                      </button>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </template>

          <template x-if="form.type==='okr_team'">
            <div>
              <label class="text-base font-semibold dark:text-slate-200">Team</label>
              <div class="mt-2 space-y-3">
                <template x-if="!showNewTeamInput">
                  <div class="flex gap-3">
                    <select x-model="form.team_id" class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
                      <option value="">-- Select a team --</option>
                      <template x-for="team in teams" :key="team.id">
                        <option :value="String(team.id)" x-text="team.name"></option>
                      </template>
                    </select>
                    <button @click="showNewTeamInput = true" type="button"
                      class="px-4 py-3 text-base rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white border dark:border-slate-600 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                      ➕ New
                    </button>
                  </div>
                </template>
                <template x-if="showNewTeamInput">
                  <div class="flex gap-3">
                    <input x-model="form.team_name" placeholder="Team name"
                      class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" @keyup.enter="createTeam()">
                    <button @click="createTeam()" type="button"
                      class="px-4 py-3 text-base rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-all duration-200 hover:shadow-lg hover:scale-105">
                      ➕ Add
                    </button>
                    <button @click="showNewTeamInput = false; form.team_name = ''" type="button"
                      class="px-4 py-3 text-base rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white border dark:border-slate-600 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                      ❌ Cancel
                    </button>
                  </div>
                </template>
              </div>
            </div>
          </template>

          <template x-if="form.type==='okr_perso'">
            <div>
              <label class="text-base font-semibold dark:text-slate-200">User</label>
              <div class="mt-2 space-y-3">
                <template x-if="!showNewUserInput">
                  <div class="flex gap-3">
                    <select x-model="form.user_id" class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
                      <option value="">-- Select a user --</option>
                      <template x-for="user in users" :key="user.id">
                        <option :value="String(user.id)"
                          x-text="`${user.first_name} ${user.last_name}`"></option>
                      </template>
                    </select>
                    <button @click="showNewUserInput = true" type="button"
                      class="px-4 py-3 text-base rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white border dark:border-slate-600 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                      ➕ New
                    </button>
                  </div>
                </template>
                <template x-if="showNewUserInput">
                  <div class="space-y-3">
                    <div class="flex gap-3">
                      <input x-model="form.user_first_name" placeholder="First name"
                        class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" @keyup.enter="createUser()">
                      <input x-model="form.user_last_name" placeholder="Last name"
                        class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" @keyup.enter="createUser()">
                    </div>
                    <div class="flex gap-3">
                      <button @click="createUser()" type="button"
                        class="flex-1 px-4 py-3 text-base rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-all duration-200 hover:shadow-lg hover:scale-105">
                        ➕ Add
                      </button>
                      <button @click="showNewUserInput = false; form.user_first_name = ''; form.user_last_name = ''" type="button"
                        class="flex-1 px-4 py-3 text-base rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white border dark:border-slate-600 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                        ❌ Cancel
                      </button>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </template>

          <div>
            <label class="text-base font-semibold dark:text-slate-200">Progress (manual)</label>
            <input type="number" min="0" max="100" x-model="form.progress" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" placeholder="Optional">
          </div>
        </div>

        <div class="mt-6 flex gap-3">
          <button @click="submit()"
            class="flex-1 py-3 text-lg font-semibold rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition-all duration-200 hover:shadow-lg hover:scale-105">
            💾 Save
          </button>
          <button @click="closeModal()"
            class="flex-1 py-3 text-lg font-semibold rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
            ❌ Cancel
          </button>
        </div>
      </div>
    </div>
  </template>

  <template x-if="companyModal.open">
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-[100]">
      <div class="w-full max-w-4xl rounded-2xl bg-white dark:bg-slate-800 border dark:border-slate-600 shadow-2xl p-8 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold dark:text-white">Company Settings</h2>
          <button @click="closeCompanyModal()" class="text-2xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">✕</button>
        </div>

        <div class="space-y-6">
          <div>
            <label class="text-base font-semibold dark:text-slate-200">Company Name</label>
            <div class="flex gap-3 mt-2">
              <input x-model="companyModal.name" class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3">
              <button @click="updateCompany()"
                class="px-6 py-3 text-base rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-all duration-200 hover:shadow-lg hover:scale-105">
                💾 Save
              </button>
            </div>
          </div>

          <div class="border-t dark:border-slate-600 pt-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-xl font-bold dark:text-white">Team Members</h3>
              <button @click="showInviteForm = !showInviteForm"
                class="px-4 py-2 text-base rounded-xl bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-700 dark:text-blue-200 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                <span x-text="showInviteForm ? '❌ Cancel' : '➕ Invite User'"></span>
              </button>
            </div>

            <template x-if="showInviteForm">
              <div class="bg-slate-50 dark:bg-slate-700 rounded-xl p-5 mb-4">
                <div class="space-y-3">
                  <div class="flex gap-3">
                    <input x-model="inviteForm.first_name" placeholder="First name"
                      class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-800 dark:text-white p-3">
                    <input x-model="inviteForm.last_name" placeholder="Last name"
                      class="flex-1 text-base rounded-xl border dark:border-slate-600 dark:bg-slate-800 dark:text-white p-3">
                  </div>
                  <input x-model="inviteForm.email" type="email" placeholder="Email"
                    class="w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-800 dark:text-white p-3">
                  <button @click="inviteUser()"
                    class="w-full px-4 py-3 text-base rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-all duration-200 hover:shadow-lg hover:scale-105">
                    ✉️ Send Invitation
                  </button>
                </div>
              </div>
            </template>

            <div class="space-y-2 max-h-96 overflow-y-auto">
              <template x-if="companyModal.users.length === 0">
                <div class="text-base text-slate-500 dark:text-slate-400 text-center py-8">No team members yet</div>
              </template>
              <template x-for="user in companyModal.users" :key="user.id">
                <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-4 flex items-center justify-between">
                  <div>
                    <div class="font-semibold dark:text-white" x-text="`${user.first_name} ${user.last_name}`"></div>
                    <div class="text-sm text-slate-500 dark:text-slate-400" x-text="user.email || 'No email'"></div>
                  </div>
                  <button @click="removeUserFromCompany(user.id)"
                    class="px-4 py-2 text-sm rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-700 dark:text-red-200 font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                    🗑️ Remove
                  </button>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <template x-if="commentsModal.open">
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-[100]">
      <div class="w-full max-w-4xl rounded-2xl bg-white dark:bg-slate-800 border dark:border-slate-600 shadow-2xl p-8 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold dark:text-white">Discussion</h2>
          <button @click="closeCommentsModal()" class="text-2xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto space-y-4 mb-6">
          <template x-if="commentsModal.comments.length === 0">
            <div class="text-base text-slate-500 dark:text-slate-400 text-center py-8">No comments yet</div>
          </template>
          <template x-for="comment in organizedComments()" :key="comment.id">
            <div>
              <div>
                <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-5">
                  <div class="flex items-start justify-between mb-3">
                    <div class="text-sm text-slate-500 dark:text-slate-400" x-text="formatDate(comment.created_at)"></div>
                    <template x-if="comment.progress_update !== null">
                      <span class="text-sm font-semibold text-blue-600 dark:text-blue-400" x-text="`Progress: ${comment.progress_update}%`"></span>
                    </template>
                  </div>
                  <div class="text-base text-slate-700 dark:text-slate-200 prose prose-base max-w-none" x-html="comment.content"></div>
                  <button @click="openReplyModal(comment.id)"
                    class="mt-3 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                    Reply
                  </button>
                </div>
              </div>
              <template x-if="getReplies(comment.id).length > 0">
                <div class="ml-8 mt-3 space-y-3">
                  <template x-for="reply in getReplies(comment.id)" :key="reply.id">
                    <div class="border-l-2 border-slate-200 dark:border-slate-600 pl-5">
                      <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-5">
                        <div class="flex items-start justify-between mb-3">
                          <div class="text-sm text-slate-500 dark:text-slate-400" x-text="formatDate(reply.created_at)"></div>
                          <template x-if="reply.progress_update !== null">
                            <span class="text-sm font-semibold text-blue-600 dark:text-blue-400" x-text="`Progress: ${reply.progress_update}%`"></span>
                          </template>
                        </div>
                        <div class="text-base text-slate-700 dark:text-slate-200 prose prose-base max-w-none" x-html="reply.content"></div>
                      </div>
                    </div>
                  </template>
                </div>
              </template>
            </div>
          </template>
        </div>

        <div class="border-t dark:border-slate-600 pt-6">
          <button @click="openAddCommentModal()"
            class="w-full py-3 text-lg font-semibold rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition-all duration-200 hover:shadow-lg hover:scale-105">
            💬 Add Comment
          </button>
        </div>
      </div>
    </div>
  </template>

  <template x-if="addCommentModal.open">
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-[200]">
      <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-slate-800 border dark:border-slate-600 shadow-2xl p-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold dark:text-white" x-text="addCommentModal.parentCommentId ? 'Add Reply' : 'Add Comment'"></h2>
          <button @click="closeAddCommentModal()" class="text-2xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">✕</button>
        </div>

        <div class="space-y-5">
          <div>
            <label class="text-base font-semibold dark:text-slate-200">Content</label>
            <div class="mt-2 border dark:border-slate-600 rounded-xl overflow-hidden">
              <div class="border-b dark:border-slate-600 bg-slate-50 dark:bg-slate-700 p-3 flex gap-3">
                <button @click="formatText('bold')" type="button" class="px-3 py-2 text-base rounded hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white" title="Bold">
                  <strong>B</strong>
                </button>
                <button @click="formatText('italic')" type="button" class="px-3 py-2 text-base rounded hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white" title="Italic">
                  <em>I</em>
                </button>
                <button @click="formatText('insertUnorderedList')" type="button" class="px-3 py-2 text-base rounded hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white" title="Bullet List">
                  •
                </button>
                <button @click="insertLink()" type="button" class="px-3 py-2 text-base rounded hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white" title="Link">
                  🔗
                </button>
              </div>
              <div x-ref="richEditor"
                contenteditable="true"
                @input="updateRichContent()"
                class="min-h-[150px] p-4 text-base focus:outline-none dark:bg-slate-700 dark:text-white"
                style="white-space: pre-wrap; word-wrap: break-word;"
                data-placeholder="Enter your comment..."></div>
            </div>
          </div>

          <template x-if="!addCommentModal.parentCommentId">
            <div>
              <label class="text-base font-semibold dark:text-slate-200">Progress Update (optional)</label>
              <input type="number" min="0" max="100" x-model="addCommentModal.progress_update"
                class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" placeholder="Leave empty to not update progress">
            </div>
          </template>
        </div>

        <div class="mt-6 flex gap-3">
          <button @click="submitComment()"
            class="flex-1 py-3 text-lg font-semibold rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition-all duration-200 hover:shadow-lg hover:scale-105">
            📮 Post
          </button>
          <button @click="closeAddCommentModal()"
            class="flex-1 py-3 text-lg font-semibold rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
            ❌ Cancel
          </button>
        </div>
      </div>
    </div>
  </template>

  <template x-if="keyResultModal.open">
    <div class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-[100]">
      <div class="w-full max-w-4xl rounded-2xl bg-white dark:bg-slate-800 border dark:border-slate-600 shadow-2xl p-8 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold dark:text-white">Key Results</h2>
          <button @click="closeKeyResultModal()" class="text-2xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto space-y-4 mb-6">
          <template x-if="getKeyResults(keyResultModal.nodeId).length === 0">
            <div class="text-base text-slate-500 dark:text-slate-400 text-center py-8">No key results yet</div>
          </template>
          <template x-for="kr in getKeyResults(keyResultModal.nodeId)" :key="kr.id">
            <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-5 border border-slate-200 dark:border-slate-600">
              <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3 mb-2">
                    <span class="text-base font-semibold text-slate-700 dark:text-slate-200" x-text="kr.name"></span>
                    <span class="text-sm text-slate-500 dark:text-slate-400">(weight: <span x-text="kr.weight"></span>)</span>
                  </div>
                  <template x-if="kr.description">
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-2" x-text="kr.description"></p>
                  </template>
                  <template x-if="kr.progress !== null && kr.progress !== undefined">
                    <div class="mt-3">
                      <div class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-1">Progress: <span x-text="kr.progress"></span>%</div>
                      <div class="h-2.5 bg-slate-200 dark:bg-slate-600 rounded-full overflow-hidden">
                        <div class="h-2.5 bg-green-500" :style="`width:${kr.progress}%`"></div>
                      </div>
                    </div>
                  </template>
                </div>
                <div class="flex gap-2">
                  <button @click="openEditKeyResult(kr, keyResultModal.nodeId)"
                    class="text-sm px-3 py-1.5 rounded bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
                    ✏️ Edit
                  </button>
                  <button @click="deleteKeyResult(kr.id, keyResultModal.nodeId)"
                    class="text-sm px-3 py-1.5 rounded bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 transition-all duration-200 hover:shadow-sm hover:scale-105">
                    🗑️ Delete
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>

        <div class="border-t dark:border-slate-600 pt-6">
          <div class="mb-6">
            <h3 class="text-lg font-semibold dark:text-white mb-4" x-text="keyResultModal.mode === 'create' ? 'Add Key Result' : 'Edit Key Result'"></h3>
            <div class="space-y-5">
              <div>
                <label class="text-base font-semibold dark:text-slate-200">Name *</label>
                <input type="text" x-model="keyResultModal.form.name" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" placeholder="Key result name">
              </div>

              <div>
                <label class="text-base font-semibold dark:text-slate-200">Description</label>
                <textarea x-model="keyResultModal.form.description" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" rows="3" placeholder="Optional description"></textarea>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-base font-semibold dark:text-slate-200">Progress (0-100)</label>
                  <input type="number" min="0" max="100" x-model="keyResultModal.form.progress" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" placeholder="Optional">
                </div>

                <div>
                  <label class="text-base font-semibold dark:text-slate-200">Weight (1-10)</label>
                  <input type="number" min="1" max="10" x-model="keyResultModal.form.weight" class="mt-2 w-full text-base rounded-xl border dark:border-slate-600 dark:bg-slate-700 dark:text-white p-3" placeholder="1">
                </div>
              </div>
            </div>
          </div>

          <div class="flex gap-3">
            <button @click="submitKeyResult()"
              class="flex-1 py-3 text-lg font-semibold rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition-all duration-200 hover:shadow-lg hover:scale-105">
              <span x-text="keyResultModal.mode === 'create' ? '➕ Add' : '💾 Update'"></span>
            </button>
            <button @click="closeKeyResultModal()"
              class="flex-1 py-3 text-lg font-semibold rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 dark:text-white transition-all duration-200 hover:shadow-sm hover:scale-105">
              ❌ Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>
</div>

<style>
  .node-card {
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
  }

  .dark .node-card {
    border: 1px solid #475569;
    background: #1e293b;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3), 0 1px 2px rgba(0, 0, 0, 0.2);
  }

  .node-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.06);
    transform: translateY(-1px);
  }

  .dark .node-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), 0 2px 4px rgba(0, 0, 0, 0.3);
  }

  .badge {
    font-size: 13px;
    padding: 6px 14px;
    border-radius: 999px;
    font-weight: 600;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
  }

  .badge:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
  }

  [contenteditable][data-placeholder]:empty:before {
    content: attr(data-placeholder);
    color: #94a3b8;
    pointer-events: none;
  }

  .dark [contenteditable][data-placeholder]:empty:before {
    color: #64748b;
  }

  [contenteditable]:focus {
    outline: none;
  }

  .prose {
    color: inherit;
  }

  .prose strong {
    font-weight: 600;
  }

  .prose em {
    font-style: italic;
  }

  .prose ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin: 0.5rem 0;
  }

  .prose a {
    color: #3b82f6;
    text-decoration: underline;
  }

  .prose a:hover {
    color: #2563eb;
  }

  .dark .prose a {
    color: #60a5fa;
  }

  .dark .prose a:hover {
    color: #93c5fd;
  }

  .fixed.inset-0 {
    backdrop-filter: blur(2px);
  }

  .fixed.inset-0>div {
    animation: modalFadeIn 0.2s ease-out;
  }

  @keyframes modalFadeIn {
    from {
      opacity: 0;
      transform: scale(0.95) translateY(-10px);
    }

    to {
      opacity: 1;
      transform: scale(1) translateY(0);
    }
  }

  button:active {
    transform: scale(0.98);
  }

  .bg-slate-50.rounded-lg {
    transition: all 0.2s ease;
  }

  .bg-slate-50.rounded-lg:hover {
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
  }

  input,
  textarea,
  select {
    transition: all 0.2s ease;
  }

  input:focus,
  textarea:focus,
  select:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: #3b82f6;
  }

  .dark input:focus,
  .dark textarea:focus,
  .dark select:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    border-color: #60a5fa;
  }
</style>

<script>
  function okrApp() {
    return {
      loading: true,
      byParent: {},
      openSet: new Set(),
      modal: {
        open: false,
        mode: 'create',
        allowedTypes: []
      },

      lockBodyScroll() {
        if (document.body.style.overflow === 'hidden') return;
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        document.body.style.overflow = 'hidden';
        document.body.style.paddingRight = `${scrollbarWidth}px`;
      },

      unlockBodyScroll() {
        const hasOpenModal = this.modal.open || this.companyModal.open || this.commentsModal.open ||
          this.addCommentModal.open || this.keyResultModal.open;
        if (hasOpenModal) return;
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
      },
      form: {
        id: null,
        parent_id: null,
        type: 'axis',
        title: '',
        description: '',
        owner: '',
        owner_id: '',
        owner_first_name: '',
        owner_last_name: '',
        owner_email: '',
        team_id: '',
        team_name: '',
        user_id: '',
        user_first_name: '',
        user_last_name: '',
        progress: ''
      },
      teams: [],
      users: [],
      showNewTeamInput: false,
      showNewUserInput: false,
      showNewOwnerInput: false,
      showInviteForm: false,
      keyResults: {},
      companyInfo: {
        id: null,
        name: ''
      },
      companyModal: {
        open: false,
        name: '',
        users: []
      },
      inviteForm: {
        first_name: '',
        last_name: '',
        email: ''
      },
      commentsModal: {
        open: false,
        nodeId: null,
        comments: []
      },
      addCommentModal: {
        open: false,
        nodeId: null,
        parentCommentId: null,
        content: '',
        progress_update: ''
      },
      keyResultModal: {
        open: false,
        nodeId: null,
        mode: 'create',
        form: {
          id: null,
          name: '',
          description: '',
          progress: '',
          weight: 1
        }
      },
      renderKey: 0,
      allExpanded: true,

      async init() {
        await this.loadCompanyInfo()
        await this.loadTeams()
        await this.loadUsers()
        await this.reload()
        this.loading = false
        window.okrAppInstance = this
        document.addEventListener('click', (e) => {
          const btn = e.target.closest('[data-action]')
          if (!btn || !window.okrAppInstance) return
          const action = btn.dataset.action
          const id = btn.dataset.id ? Number(btn.dataset.id) : null
          const parentId = btn.dataset.parentId ? Number(btn.dataset.parentId) : null
          const type = btn.dataset.type
          if (action === 'toggle' && id !== null) window.okrAppInstance.toggle(id)
          else if (action === 'create' && parentId !== null && type) window.okrAppInstance.openCreate(parentId, type)
          else if (action === 'edit' && id !== null) window.okrAppInstance.openEdit(id)
          else if (action === 'remove' && id !== null) window.okrAppInstance.remove(id)
        })
      },

      async reload() {
        const r = await fetch('/api/nodes')
        const j = await r.json()
        this.byParent = j.byParent || {}
        await this.loadKeyResults()
        this.renderKey++
        const allIds = this.getAllNodeIds()
        allIds.forEach(id => this.openSet.add(id))
        this.updateAllExpanded()
      },

      async loadCompanyInfo() {
        const r = await fetch('/api/company')
        if (r.ok) {
          const company = await r.json()
          this.companyInfo = company
        }
      },

      async openCompanyModal() {
        this.lockBodyScroll()
        this.companyModal = {
          open: true,
          name: this.companyInfo.name,
          users: []
        }
        this.showInviteForm = false
        await this.loadCompanyUsers()
      },

      async loadCompanyUsers() {
        const r = await fetch('/api/company/users')
        if (r.ok) {
          const users = await r.json()
          this.companyModal.users = users
        }
      },

      closeCompanyModal() {
        this.companyModal.open = false
        this.showInviteForm = false
        this.inviteForm = {
          first_name: '',
          last_name: '',
          email: ''
        }
        this.unlockBodyScroll()
      },

      async updateCompany() {
        const r = await fetch('/api/company', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            name: this.companyModal.name
          })
        })
        if (r.ok) {
          await this.loadCompanyInfo()
          showToast('Company name updated successfully', 'success')
        } else {
          const err = await r.json().catch(() => ({}))
          showToast(err.error || 'Failed to update company name', 'error')
        }
      },

      async inviteUser() {
        if (!this.inviteForm.first_name || !this.inviteForm.last_name || !this.inviteForm.email) {
          showToast('First name, last name and email are required', 'warning')
          return
        }
        const r = await fetch('/api/company/invite', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.inviteForm)
        })
        if (r.ok) {
          this.inviteForm = {
            first_name: '',
            last_name: '',
            email: ''
          }
          this.showInviteForm = false
          await this.loadCompanyUsers()
          await this.loadUsers()
          showToast('User invited successfully', 'success')
        } else {
          const err = await r.json().catch(() => ({}))
          showToast(err.error || 'Failed to invite user', 'error')
        }
      },

      async removeUserFromCompany(userId) {
        if (!confirm('Are you sure you want to remove this user from the company?')) {
          return
        }

        try {
          const r = await fetch(`/api/company/users/${userId}`, {
            method: 'DELETE'
          })

          if (r.ok) {
            showToast('User removed from company successfully', 'success')
            await this.loadCompanyUsers()
            await this.loadUsers()
          } else {
            const err = await r.json().catch(() => ({}))
            showToast(err.error || 'Failed to remove user', 'error')
          }
        } catch (e) {
          showToast('Failed to remove user', 'error')
        }
      },

      async loadKeyResults() {
        const allNodes = []
        for (const k in this.byParent) {
          allNodes.push(...(this.byParent[k] || []))
        }
        const okrNodes = allNodes.filter(n => n.type === 'okr_team' || n.type === 'okr_perso')
        for (const node of okrNodes) {
          try {
            const r = await fetch(`/api/nodes/${node.id}/key-results`)
            if (r.ok) {
              const j = await r.json()
              this.keyResults[node.id] = j.key_results || []
            } else {
              this.keyResults[node.id] = []
            }
          } catch (e) {
            console.error(`Error loading key results for node ${node.id}:`, e)
            this.keyResults[node.id] = []
          }
        }
      },

      getKeyResults(nodeId) {
        return this.keyResults[nodeId] || []
      },

      async loadTeams() {
        const r = await fetch('/api/teams')
        const j = await r.json()
        this.teams = [...(j.teams || [])]
      },

      async loadUsers() {
        const r = await fetch('/api/users')
        const j = await r.json()
        this.users = [...(j.users || [])]
      },

      roots() {
        return this.byParent[0] || []
      },
      childrenOf(id) {
        return this.byParent[id] || []
      },

      isOpen(id) {
        return this.openSet.has(id)
      },
      toggle(id) {
        this.isOpen(id) ? this.openSet.delete(id) : this.openSet.add(id)
        this.updateAllExpanded()
      },
      getAllNodeIds() {
        const ids = []
        for (const k in this.byParent) {
          const nodes = this.byParent[k] || []
          for (const node of nodes) {
            ids.push(node.id)
          }
        }
        return ids
      },
      toggleAll() {
        const allIds = this.getAllNodeIds()
        if (this.allExpanded) {
          this.openSet.clear()
        } else {
          allIds.forEach(id => this.openSet.add(id))
        }
        this.allExpanded = !this.allExpanded
      },
      updateAllExpanded() {
        const allIds = this.getAllNodeIds()
        this.allExpanded = allIds.length > 0 && allIds.every(id => this.openSet.has(id))
      },

      allowedChildTypes(t) {
        return {
          company: ['axis'],
          axis: ['okr_team'],
          okr_team: ['okr_perso'],
          okr_perso: []
        } [t] || []
      },

      labelType(t) {
        return {
          company: 'Company',
          axis: 'Strategic axis',
          okr_team: 'Okr team',
          okr_perso: 'Okr perso'
        } [t] || t
      },
      formatTypeLabel(t) {
        return {
          company: 'company',
          axis: 'axis',
          okr_team: 'okr team',
          okr_perso: 'okr perso'
        } [t] || t
      },

      badgeClass(t) {
        return {
          company: 'bg-slate-900 text-white',
          axis: 'bg-indigo-600 text-white',
          okr_team: 'bg-amber-500 text-black',
          okr_perso: 'bg-purple-500 text-white'
        } [t]
      },

      indentStyle(n) {
        const depth = this.depthOf(n)
        return `margin-left:${depth*16}px`
      },

      depthOf(n) {
        let d = 0,
          p = n.parent_id
        while (p) {
          const parent = this.findNode(p)
          if (!parent) break
          d++
          p = parent.parent_id
        }
        return d
      },

      findNode(id) {
        for (const k in this.byParent) {
          const hit = (this.byParent[k] || []).find(x => x.id == id)
          if (hit) return hit
        }
        return null
      },

      computedProgress(id) {
        const node = this.findNode(id)
        if (!node) return null

        if (node.progress !== null && node.progress !== undefined) {
          return node.progress
        }

        if (node.type === 'okr_team' || node.type === 'okr_perso') {
          const krs = this.getKeyResults(id)
          const childOkrs = this.childrenOf(id).filter(c => c.type === 'okr_team' || c.type === 'okr_perso')

          const progresses = []

          if (krs.length > 0) {
            let totalWeight = 0
            let weightedProgress = 0

            for (const kr of krs) {
              const weight = kr.weight || 1
              totalWeight += weight
              const krProgress = kr.progress !== null && kr.progress !== undefined ? kr.progress : 0
              weightedProgress += krProgress * weight
            }

            if (totalWeight > 0) {
              progresses.push(weightedProgress / totalWeight)
            }
          }

          for (const childOkr of childOkrs) {
            const computed = this.computedProgress(childOkr.id)
            progresses.push(computed !== null ? computed : 0)
          }

          if (progresses.length === 0) return null
          return progresses.reduce((a, b) => a + b, 0) / progresses.length
        }

        if (node.type === 'axis') {
          const childOkrTeams = this.childrenOf(id).filter(c => c.type === 'okr_team')
          if (!childOkrTeams.length) return null

          const progresses = []
          for (const childOkrTeam of childOkrTeams) {
            const computed = this.computedProgress(childOkrTeam.id)
            progresses.push(computed !== null ? computed : 0)
          }

          return progresses.reduce((a, b) => a + b, 0) / progresses.length
        }

        const kids = this.childrenOf(id)
        if (!kids.length) return null
        const progresses = []
        for (const child of kids) {
          const computed = this.computedProgress(child.id)
          progresses.push(computed !== null ? computed : 0)
        }
        return progresses.reduce((a, b) => a + b, 0) / progresses.length
      },

      renderSubtree(parentId, key) {
        const kids = this.childrenOf(parentId)
        if (!kids.length) return ''
        const self = this
        return kids.map(c => `
        <div>
          <div class="node-card" style="margin-left:${(this.depthOf(c))*16}px">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-4">
                  <button data-action="toggle" data-id="${c.id}"
                          class="text-slate-400 hover:text-slate-700 text-lg">
                    ${this.isOpen(c.id)?'▾':'▸'}
                  </button>
                  <span class="badge ${this.badgeClass(c.type)}">${this.formatTypeLabel(c.type)}</span>
                  <h3 class="text-lg font-semibold truncate dark:text-white">${this.escape(c.title)}</h3>
                </div>
                ${c.description?`<p class="text-base text-slate-600 dark:text-slate-300 mt-2 whitespace-pre-line">${this.escape(c.description)}</p>`:''}
                ${c.progress!=null?`
                  <div class="mt-4">
                    <div class="text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">Progress: ${c.progress}%</div>
                    <div class="h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                      <div class="h-3 bg-slate-900" style="width:${c.progress}%"></div>
                    </div>
                  </div>`:''}
                <div class="mt-4 flex flex-wrap gap-3">
                  ${(this.allowedChildTypes(c.type)||[]).map(t=>`
                    <button data-action="create" data-parent-id="${c.id}" data-type="${t}"
                            class="text-sm px-4 py-2 rounded-lg bg-slate-50 dark:bg-slate-700 border dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-600 dark:text-white font-medium transition-all duration-200 hover:shadow-sm hover:scale-105">
                      ➕ ${this.labelType(t)}
                    </button>`).join('')}
                </div>
              </div>
              <div class="flex shrink-0 gap-3">
                <button data-action="edit" data-id="${c.id}"
                        class="text-base px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-600 hover:bg-slate-200 dark:hover:bg-slate-500 dark:text-white font-medium transition-all duration-200 hover:shadow-md hover:scale-105">✏️ Edit</button>
                <button data-action="remove" data-id="${c.id}"
                        class="text-base px-4 py-2 rounded-lg bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 dark:text-red-200 font-medium transition-all duration-200 hover:shadow-md hover:scale-105">🗑️ Delete</button>
              </div>
            </div>
          </div>
          ${this.isOpen(c.id)?`<div class="space-y-4">${this.renderSubtree(c.id, this.renderKey)}</div>`:''}
        </div>
      `).join('')
      },

      escape(s) {
        return (s || '').replace(/[&<>"']/g, m => ({
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;'
        } [m]))
      },

      openCreate(parentId, type) {
        const parent = parentId ? this.findNode(parentId) : null
        this.modal = {
          open: true,
          mode: 'create',
          allowedTypes: parent ? this.allowedChildTypes(parent.type) : ['axis']
        }
        this.form = {
          id: null,
          parent_id: parentId ? Number(parentId) : null,
          type: type,
          title: '',
          description: '',
          owner: '',
          owner_id: '',
          owner_first_name: '',
          owner_last_name: '',
          owner_email: '',
          team_id: '',
          team_name: '',
          user_id: '',
          user_first_name: '',
          user_last_name: '',
          progress: ''
        }
        this.showNewTeamInput = false
        this.showNewUserInput = false
        this.showNewOwnerInput = false
      },

      openAddModal() {
        this.lockBodyScroll()
        this.modal = {
          open: true,
          mode: 'add',
          allowedTypes: []
        }
        this.form = {
          id: null,
          parent_id: null,
          type: '',
          title: '',
          description: '',
          owner: '',
          owner_id: '',
          owner_first_name: '',
          owner_last_name: '',
          owner_email: '',
          team_id: '',
          team_name: '',
          user_id: '',
          user_first_name: '',
          user_last_name: '',
          progress: ''
        }
        this.showNewTeamInput = false
        this.showNewUserInput = false
        this.showNewOwnerInput = false
      },

      updateParentOptions() {
        this.form.parent_id = null
      },

      availableParents() {
        if (!this.form.type) return []
        const allNodes = []
        for (const k in this.byParent) {
          allNodes.push(...(this.byParent[k] || []))
        }
        const validParents = allNodes.filter(n => {
          const allowed = this.allowedChildTypes(n.type)
          return allowed && allowed.includes(this.form.type)
        })
        return validParents
      },

      openEdit(n) {
        if (typeof n === 'number') n = this.findNode(n)
        this.lockBodyScroll()
        this.modal = {
          open: true,
          mode: 'edit',
          allowedTypes: []
        }

        let ownerId = ''
        if (n.owner && n.type === 'okr_team') {
          const ownerParts = (n.owner || '').trim().split(' ')
          const matchingUser = this.users.find(u =>
            u.first_name === ownerParts[0] && u.last_name === ownerParts.slice(1).join(' ')
          )
          if (matchingUser) {
            ownerId = matchingUser.id
          }
        }

        this.form = {
          id: n.id,
          parent_id: n.parent_id,
          type: n.type,
          title: n.title || '',
          description: n.description || '',
          owner: n.owner || '',
          owner_id: ownerId,
          owner_first_name: '',
          owner_last_name: '',
          owner_email: '',
          team_id: n.team_id || '',
          team_name: '',
          user_id: n.user_id || '',
          user_first_name: '',
          user_last_name: '',
          progress: n.progress ?? ''
        }
        this.showNewTeamInput = false
        this.showNewUserInput = false
        this.showNewOwnerInput = false
      },

      closeModal() {
        this.modal.open = false
        this.showNewTeamInput = false
        this.showNewUserInput = false
        this.unlockBodyScroll()
      },

      async createTeam() {
        if (!this.form.team_name || !this.form.team_name.trim()) {
          return
        }
        const r = await fetch('/api/teams', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            name: this.form.team_name
          })
        })
        if (!r.ok) {
          return
        }
        const j = await r.json()
        await this.loadTeams()
        this.form.team_id = String(j.id)
        this.form.team_name = ''
        this.showNewTeamInput = false
      },

      async createUser() {
        if (!this.form.user_first_name || !this.form.user_first_name.trim() ||
          !this.form.user_last_name || !this.form.user_last_name.trim()) {
          return
        }
        const r = await fetch('/api/users', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            first_name: this.form.user_first_name,
            last_name: this.form.user_last_name
          })
        })
        if (!r.ok) {
          return
        }
        const j = await r.json()
        await this.loadUsers()
        this.form.user_id = String(j.id)
        this.form.user_first_name = ''
        this.form.user_last_name = ''
        this.showNewUserInput = false
      },

      async inviteOwner() {
        if (!this.form.owner_first_name || !this.form.owner_first_name.trim() ||
          !this.form.owner_last_name || !this.form.owner_last_name.trim() ||
          !this.form.owner_email || !this.form.owner_email.trim()) {
          showToast('First name, last name and email are required', 'warning')
          return
        }
        const r = await fetch('/api/company/invite', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            first_name: this.form.owner_first_name,
            last_name: this.form.owner_last_name,
            email: this.form.owner_email
          })
        })
        if (!r.ok) {
          const err = await r.json().catch(() => ({}))
          showToast(err.error || 'Error inviting user', 'error')
          return
        }
        const j = await r.json()
        await this.loadUsers()
        this.form.owner_id = j.user.id
        this.form.owner_first_name = ''
        this.form.owner_last_name = ''
        this.form.owner_email = ''
        this.showNewOwnerInput = false
        showToast('Invitation sent successfully', 'success')
      },

      async submit() {
        const f = {
          ...this.form
        }
        if (this.modal.mode === 'create' || this.modal.mode === 'add') {
          if (!f.type) {
            showToast('Please select a type', 'warning')
            return
          }
          const {
            id,
            ...createData
          } = f
          if (createData.parent_id === '' || createData.parent_id === null || createData.parent_id === undefined) {
            createData.parent_id = null
          } else {
            createData.parent_id = Number(createData.parent_id)
          }
          if (createData.team_id === '') createData.team_id = null
          else if (createData.team_id) createData.team_id = Number(createData.team_id)
          if (createData.user_id === '') createData.user_id = null
          else if (createData.user_id) createData.user_id = Number(createData.user_id)

          if (createData.type === 'okr_team' && createData.owner_id) {
            createData.owner = this.getUserName(Number(createData.owner_id))
          }
          if (createData.type === 'okr_perso') {
            createData.owner = ''
          }

          delete createData.owner_id
          delete createData.owner_first_name
          delete createData.owner_last_name
          delete createData.owner_email
          const response = await fetch('/api/nodes', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(createData)
          })
          if (!response.ok) {
            const error = await response.text()
            console.error('Error creating node:', error)
            showToast('Failed to create item', 'error')
            return
          }
          if (createData.parent_id) this.openSet.add(createData.parent_id)
          showToast(`${this.labelType(createData.type)} created successfully`, 'success')
        } else {
          const updateData = {
            ...f
          }
          if (updateData.team_id === '') updateData.team_id = null
          else if (updateData.team_id) updateData.team_id = Number(updateData.team_id)
          if (updateData.user_id === '') updateData.user_id = null
          else if (updateData.user_id) updateData.user_id = Number(updateData.user_id)

          if (updateData.type === 'okr_team' && updateData.owner_id) {
            updateData.owner = this.getUserName(Number(updateData.owner_id))
          }
          if (updateData.type === 'okr_perso') {
            updateData.owner = ''
          }

          delete updateData.owner_id
          delete updateData.owner_first_name
          delete updateData.owner_last_name
          delete updateData.owner_email
          const response = await fetch('/api/nodes/' + f.id, {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(updateData)
          })
          if (!response.ok) {
            const error = await response.text()
            console.error('Error updating node:', error)
            showToast('Failed to update item', 'error')
            return
          }
          const progressUpdated = updateData.progress !== '' && updateData.progress !== null && updateData.progress !== undefined
          showToast(
            progressUpdated ?
            `${this.labelType(updateData.type)} and progress updated successfully` :
            `${this.labelType(updateData.type)} updated successfully`,
            'success'
          )
        }
        this.closeModal()
        await this.reload()
      },

      async remove(id) {
        const node = this.findNode(id)
        const title = node ? node.title : 'this item'
        if (!confirm(`Are you sure you want to delete "${title}"? This action cannot be undone.`)) {
          return
        }
        const r = await fetch('/api/nodes/' + id, {
          method: 'DELETE'
        })
        if (r.ok) {
          showToast(`"${title}" deleted successfully`, 'success')
        } else {
          showToast('Failed to delete item', 'error')
        }
        await this.reload()
      },

      async openComments(nodeId) {
        this.lockBodyScroll()
        this.commentsModal = {
          open: true,
          nodeId: nodeId,
          comments: []
        }
        await this.loadComments(nodeId)
      },

      async loadComments(nodeId) {
        const r = await fetch(`/api/nodes/${nodeId}/comments`)
        const j = await r.json()
        this.commentsModal.comments = j.comments || []
      },

      closeCommentsModal() {
        this.commentsModal.open = false
        this.commentsModal.nodeId = null
        this.commentsModal.comments = []
        this.unlockBodyScroll()
      },

      openAddCommentModal(parentCommentId = null) {
        this.lockBodyScroll()
        this.addCommentModal = {
          open: true,
          nodeId: this.commentsModal.nodeId,
          parentCommentId: parentCommentId,
          content: '',
          progress_update: ''
        }
        this.$nextTick(() => {
          if (this.$refs.richEditor) {
            this.$refs.richEditor.innerHTML = ''
          }
        })
      },

      closeAddCommentModal() {
        this.addCommentModal.open = false
        this.addCommentModal.nodeId = null
        this.addCommentModal.parentCommentId = null
        this.addCommentModal.content = ''
        this.addCommentModal.progress_update = ''
        this.unlockBodyScroll()
      },

      openReplyModal(commentId) {
        this.openAddCommentModal(commentId)
      },

      async submitComment() {
        const editor = this.$refs.richEditor
        let content = ''
        if (editor) {
          content = editor.innerHTML.trim()
          if (content === '<br>' || content === '<div><br></div>' || content === '') {
            showToast('Content is required', 'warning')
            return
          }
        } else {
          content = this.addCommentModal.content
          if (!content || content.trim() === '') {
            showToast('Content is required', 'warning')
            return
          }
        }

        const data = {
          content: content,
          parent_comment_id: this.addCommentModal.parentCommentId || null,
          progress_update: this.addCommentModal.parentCommentId ? null : (this.addCommentModal.progress_update || null)
        }

        const r = await fetch(`/api/nodes/${this.addCommentModal.nodeId}/comments`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        })

        if (!r.ok) {
          const error = await r.text()
          console.error('Error creating comment:', error)
          showToast('Failed to add comment', 'error')
          return
        }

        const hasProgressUpdate = data.progress_update !== null && data.progress_update !== ''
        const isReply = data.parent_comment_id !== null

        showToast(
          hasProgressUpdate ?
          `Comment added with progress update (${data.progress_update}%)` :
          isReply ?
          'Reply added successfully' :
          'Comment added successfully',
          'success'
        )

        this.closeAddCommentModal()
        await this.loadComments(this.commentsModal.nodeId)
        await this.reload()
      },

      formatText(command) {
        document.execCommand(command, false, null)
        this.$refs.richEditor.focus()
      },

      insertLink() {
        const url = prompt('Enter URL:')
        if (url) {
          document.execCommand('createLink', false, url)
          this.$refs.richEditor.focus()
        }
      },

      updateRichContent() {
        if (this.$refs.richEditor) {
          this.addCommentModal.content = this.$refs.richEditor.innerHTML
        }
      },

      formatDate(dateString) {
        if (!dateString) return ''
        const date = new Date(dateString)
        return date.toLocaleString('fr-FR', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      },

      organizedComments() {
        return this.commentsModal.comments.filter(c => !c.parent_comment_id)
      },

      getReplies(commentId) {
        return this.commentsModal.comments.filter(c => c.parent_comment_id == commentId)
      },

      getTeamName(teamId) {
        const team = this.teams.find(t => t.id == teamId)
        return team ? team.name : `Team #${teamId}`
      },

      getUserName(userId) {
        const user = this.users.find(u => u.id == userId)
        return user ? `${user.first_name} ${user.last_name}` : `User #${userId}`
      },

      async openKeyResultsModal(nodeId) {
        this.lockBodyScroll()
        await this.loadKeyResultsForNode(nodeId)
        this.keyResultModal = {
          open: true,
          nodeId: nodeId,
          mode: 'create',
          form: {
            id: null,
            name: '',
            description: '',
            progress: '',
            weight: 1
          }
        }
      },

      async loadKeyResultsForNode(nodeId) {
        try {
          const r = await fetch(`/api/nodes/${nodeId}/key-results`)
          if (r.ok) {
            const j = await r.json()
            this.keyResults[nodeId] = j.key_results || []
          } else {
            this.keyResults[nodeId] = []
          }
        } catch (e) {
          console.error(`Error loading key results for node ${nodeId}:`, e)
          this.keyResults[nodeId] = []
        }
      },

      closeKeyResultModal() {
        this.keyResultModal.open = false
        this.keyResultModal.nodeId = null
        this.keyResultModal.form = {
          id: null,
          name: '',
          description: '',
          progress: '',
          weight: 1
        }
        this.unlockBodyScroll()
      },

      openEditKeyResult(kr, nodeId) {
        this.keyResultModal = {
          open: true,
          nodeId: nodeId,
          mode: 'edit',
          form: {
            id: kr.id,
            name: kr.name || '',
            description: kr.description || '',
            progress: kr.progress ?? '',
            weight: kr.weight || 1
          }
        }
      },

      async submitKeyResult() {
        const f = this.keyResultModal.form
        if (!f.name || !f.name.trim()) {
          showToast('Name is required', 'warning')
          return
        }

        const weight = f.weight ? Number(f.weight) : 1
        if (weight < 1 || weight > 10) {
          showToast('Weight must be between 1 and 10', 'warning')
          return
        }

        const data = {
          name: f.name.trim(),
          description: f.description ? f.description.trim() : null,
          progress: f.progress !== '' ? Number(f.progress) : null,
          weight: weight
        }

        if (this.keyResultModal.mode === 'create') {
          const r = await fetch(`/api/nodes/${this.keyResultModal.nodeId}/key-results`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
          })
          if (!r.ok) {
            const error = await r.text()
            console.error('Error creating key result:', error)
            showToast('Error creating key result', 'error')
            return
          }
          showToast('Key result created successfully', 'success')
        } else {
          const r = await fetch(`/api/key-results/${f.id}`, {
            method: 'PATCH',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
          })
          if (!r.ok) {
            const error = await r.text()
            console.error('Error updating key result:', error)
            showToast('Error updating key result', 'error')
            return
          }
          const hasProgress = data.progress !== null && data.progress !== ''
          showToast(
            hasProgress ?
            `Key result and progress (${data.progress}%) updated successfully` :
            'Key result updated successfully',
            'success'
          )
        }

        this.keyResultModal.form = {
          id: null,
          name: '',
          description: '',
          progress: '',
          weight: 1
        }
        this.keyResultModal.mode = 'create'
        await this.loadKeyResultsForNode(this.keyResultModal.nodeId)
        await this.reload()
      },

      async deleteKeyResult(krId, nodeId) {
        if (!confirm('Are you sure you want to delete this key result?')) {
          return
        }
        const r = await fetch(`/api/key-results/${krId}`, {
          method: 'DELETE'
        })
        if (!r.ok) {
          showToast('Error deleting key result', 'error')
          return
        }
        showToast('Key result deleted successfully', 'success')
        await this.loadKeyResultsForNode(nodeId)
        await this.reload()
      }
    }
  }
</script>

<style>
  .toast {
    min-width: 300px;
    max-width: 500px;
    padding: 1rem 1.25rem;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: slideIn 0.3s ease-out, fadeOut 0.3s ease-in 4.7s;
    pointer-events: all;
  }

  .toast-success {
    background-color: #10b981;
    color: white;
  }

  .toast-error {
    background-color: #ef4444;
    color: white;
  }

  .toast-info {
    background-color: #3b82f6;
    color: white;
  }

  .toast-warning {
    background-color: #f59e0b;
    color: white;
  }

  @media (prefers-color-scheme: dark) {
    .toast {
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
  }

  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }

    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  @keyframes fadeOut {
    from {
      opacity: 1;
    }

    to {
      opacity: 0;
    }
  }
</style>

<script>
  function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icons = {
      success: '✓',
      error: '✕',
      info: 'ℹ',
      warning: '⚠'
    };

    toast.innerHTML = `
      <span style="font-size: 1.25rem; font-weight: bold;">${icons[type] || icons.info}</span>
      <span style="flex: 1; font-weight: 500;">${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => {
        container.removeChild(toast);
      }, 300);
    }, 5000);
  }
</script>