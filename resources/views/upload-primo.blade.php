<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Upload Fichier Primo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: '#3b82f6',
                        success: '#10b981',
                        error: '#ef4444',
                    },
                },
            },
        };
    </script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-800 font-sans p-5">
    <div class="max-w-lg w-full mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <a
                href="{{ route('show.admin.dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-800 transition-all shadow-sm"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Retour
            </a>
        </div>

        <!-- Card -->
        <div class="bg-white border border-gray-200 rounded-xl p-8 shadow-lg">
            <!-- Title Section -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-primary rounded-xl flex items-center justify-center mx-auto mb-4 shadow-md shadow-primary/30">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Upload Fichier Primo</h1>
                <p class="text-gray-500 text-sm">Importez un fichier CSV ou Excel contenant les données CHRONO et MT_TOTAL_CIL</p>
            </div>

            <!-- Alerts -->
            <div id="alert-success" class="hidden items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="success-message"></span>
            </div>

            <div id="alert-error" class="hidden items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span id="error-message"></span>
            </div>

            <!-- Upload Form -->
            <form id="upload-form" action="{{ route('upload.primo.process') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <!-- Upload Area -->
                <div
                    id="upload-area"
                    class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer transition-all hover:border-primary hover:bg-blue-50"
                >
                    <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <p class="font-semibold mb-1 text-gray-700">Glissez-déposez votre fichier ici</p>
                    <p class="text-gray-500 text-sm mb-3">ou cliquez pour parcourir</p>
                    <div class="flex justify-center gap-2">
                        <span class="px-2 py-1 bg-primary/10 text-primary text-xs font-semibold rounded">CSV</span>
                        <span class="px-2 py-1 bg-primary/10 text-primary text-xs font-semibold rounded">XLS</span>
                        <span class="px-2 py-1 bg-primary/10 text-primary text-xs font-semibold rounded">XLSX</span>
                    </div>
                    <input type="file" id="file-input" name="file" accept=".csv,.xls,.xlsx" class="hidden">
                </div>

                <!-- File Info -->
                <div id="file-info" class="hidden items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div id="file-name" class="font-semibold text-gray-800 truncate"></div>
                        <div id="file-size" class="text-xs text-gray-500"></div>
                    </div>
                    <button
                        type="button"
                        id="file-remove"
                        class="w-7 h-7 bg-red-100 border border-red-200 rounded-lg flex items-center justify-center hover:bg-red-200 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-red-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submit-btn"
                    disabled
                    class="w-full bg-primary hover:bg-primary/90 text-white font-semibold py-3 rounded-lg flex items-center justify-center gap-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Importer les données
                </button>
            </form>

            <!-- Stats -->
            <div id="stats" class="hidden grid grid-cols-3 gap-3 mt-6 pt-6 border-t border-gray-200">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div id="stat-total" class="text-2xl font-bold text-gray-800">0</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Total lignes</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div id="stat-success" class="text-2xl font-bold text-green-600">0</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Importés</div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div id="stat-error" class="text-2xl font-bold text-red-600">0</div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Erreurs</div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm">
                <div class="flex items-center gap-2 text-blue-600 font-semibold mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    Informations importantes
                </div>
                <ul class="list-none space-y-1 text-gray-600">
                    <li>• Le fichier doit contenir les colonnes <strong class="text-gray-800">CHRONO</strong> et <strong class="text-gray-800">MT_TOTAL_CIL</strong></li>
                    <li>• Les enregistrements existants seront mis à jour automatiquement</li>
                    <li>• Taille maximale du fichier : 10 Mo</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('file-input');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const fileRemove = document.getElementById('file-remove');
        const submitBtn = document.getElementById('submit-btn');
        const uploadForm = document.getElementById('upload-form');
        const alertSuccess = document.getElementById('alert-success');
        const alertError = document.getElementById('alert-error');
        const stats = document.getElementById('stats');

        // Click sur la zone d'upload
        uploadArea.addEventListener('click', () => fileInput.click());

        // Drag & Drop
        ['dragover', 'dragenter'].forEach(event => {
            uploadArea.addEventListener(event, (e) => {
                e.preventDefault();
                uploadArea.classList.add('border-primary', 'bg-blue-50');
            });
        });

        ['dragleave', 'drop'].forEach(event => {
            uploadArea.addEventListener(event, (e) => {
                e.preventDefault();
                uploadArea.classList.remove('border-primary', 'bg-blue-50');
            });
        });

        uploadArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        // Sélection de fichier
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                handleFileSelect(e.target.files[0]);
            }
        });

        // Supprimer le fichier
        fileRemove.addEventListener('click', () => {
            fileInput.value = '';
            fileInfo.classList.add('hidden');
            submitBtn.disabled = true;
            hideAlerts();
        });

        // Gestion du fichier sélectionné
        function handleFileSelect(file) {
            const validExtensions = ['.csv', '.xls', '.xlsx'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

            if (!validExtensions.includes(fileExtension)) {
                showError('Format de fichier non supporté. Utilisez CSV, XLS ou XLSX.');
                return;
            }

            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileInfo.classList.remove('hidden');
            submitBtn.disabled = false;
            hideAlerts();
        }

        // Formater la taille du fichier
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Afficher/Masquer les alertes
        function showSuccess(message) {
            document.getElementById('success-message').textContent = message;
            alertSuccess.classList.remove('hidden');
            alertError.classList.add('hidden');
        }

        function showError(message) {
            document.getElementById('error-message').textContent = message;
            alertError.classList.remove('hidden');
            alertSuccess.classList.add('hidden');
        }

        function hideAlerts() {
            alertSuccess.classList.add('hidden');
            alertError.classList.add('hidden');
        }

        // Soumission du formulaire
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(uploadForm);
            submitBtn.disabled = true;
            hideAlerts();

            try {
                const response = await fetch(uploadForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showSuccess(data.message);
                    if (data.stats) {
                        document.getElementById('stat-total').textContent = data.stats.total;
                        document.getElementById('stat-success').textContent = data.stats.success;
                        document.getElementById('stat-error').textContent = data.stats.errors;
                        stats.classList.remove('hidden');
                    }
                } else {
                    showError(data.message || 'Une erreur est survenue');
                }
            } catch (error) {
                showError('Erreur de connexion au serveur');
            } finally {
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>