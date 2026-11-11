/**
 * KasiVo Audio Recorder Module (Task KV-027)
 * PIC: Putri & Angel (Frontend dan UI/UX)
 *
 * Mengelola perekaman suara via Web API MediaRecorder & getUserMedia,
 * visualisasi audio aktual via Web Audio API AnalyserNode,
 * unggah berkas audio alternatif, serta pembersihan resource memori & hardware.
 */

export function audioRecorder() {
    return {
        // State Machine: 'idle' | 'requesting_permission' | 'recording' | 'stopped' | 'uploading' | 'success' | 'error'
        state: 'idle',
        activeTab: 'record', // 'record' | 'upload'

        // Media Stream & Recording Objects
        mediaStream: null,
        mediaRecorder: null,
        audioChunks: [],
        audioBlob: null,
        audioUrl: null,
        mimeType: '',

        // Web Audio API for Real-time Visualizer
        audioContext: null,
        analyser: null,
        animationFrame: null,
        visualizerBars: [15, 25, 45, 60, 40, 20],

        // Timer & Duration (Batas sementara 120 detik / 2 menit)
        seconds: 0,
        maxDuration: 120,
        timerInterval: null,

        // File Upload Properties (Batas 15 MB)
        uploadedFile: null,
        fileName: '',
        fileSizeFormatted: '',
        maxFileSizeBytes: 15 * 1024 * 1024,

        // Error & Feedback
        errorMessage: '',
        showIntegrationModal: false,

        /**
         * Inisialisasi dan pendaftaran lifecycle cleanup listener.
         */
        init() {
            // Bersihkan hardware mikrofon saat pengguna navigasi via wire:navigate
            window.addEventListener('livewire:navigating', () => {
                this.cleanup();
                this.clearAudioPreview();
            }, { once: true });

            // Bersihkan saat tab ditutup atau di-refresh
            window.addEventListener('beforeunload', () => {
                this.cleanup();
            });
        },

        /**
         * Format durasi detik ke MM:SS
         */
        get formattedDuration() {
            const mins = Math.floor(this.seconds / 60);
            const secs = this.seconds % 60;
            return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        },

        get formattedMaxDuration() {
            const mins = Math.floor(this.maxDuration / 60);
            const secs = this.maxDuration % 60;
            return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        },

        /**
         * Berpindah antar tab Rekam Mikrofon vs Unggah Audio
         */
        switchTab(tab) {
            if (this.state === 'recording') {
                this.cancelRecording();
            }
            this.stopAllAudioPlayback();
            this.activeTab = tab;
            if (this.state !== 'stopped') {
                this.state = 'idle';
                this.errorMessage = '';
            }
        },

        /**
         * Cek kompatibilitas browser & mulai proses izin mikrofon
         */
        async startRecording() {
            this.errorMessage = '';
            this.clearAudioPreview();

            // 1. Validasi secure context (HTTPS / localhost)
            if (typeof window !== 'undefined' && !window.isSecureContext && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
                this.state = 'error';
                this.errorMessage = 'Perekaman mikrofon membutuhkan koneksi aman (HTTPS). Silakan akses aplikasi melalui URL HTTPS atau localhost.';
                return;
            }

            // 2. Validasi ketersediaan Web MediaDevices API
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.state = 'error';
                this.errorMessage = 'Browser Anda belum mendukung Web MediaDevices API. Silakan gunakan browser modern seperti Google Chrome, Microsoft Edge, atau Safari, atau gunakan opsi Unggah File Audio.';
                return;
            }

            // 3. Validasi MediaRecorder
            if (typeof MediaRecorder === 'undefined') {
                this.state = 'error';
                this.errorMessage = 'Browser Anda belum mendukung MediaRecorder. Silakan gunakan opsi Unggah File Audio di bawah.';
                return;
            }

            this.mimeType = this.getSupportedMimeType();
            this.state = 'requesting_permission';

            try {
                this.mediaStream = await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                    }
                });

                this.audioChunks = [];
                const recorderOptions = this.mimeType ? { mimeType: this.mimeType } : {};
                this.mediaRecorder = new MediaRecorder(this.mediaStream, recorderOptions);

                this.mediaRecorder.ondataavailable = (event) => {
                    if (event.data && event.data.size > 0) {
                        this.audioChunks.push(event.data);
                    }
                };

                this.mediaRecorder.onstop = () => {
                    const actualType = this.mimeType || (this.audioChunks[0] ? this.audioChunks[0].type : 'audio/webm');
                    this.audioBlob = new Blob(this.audioChunks, { type: actualType });
                    this.audioUrl = URL.createObjectURL(this.audioBlob);
                    this.fileName = `rekaman-suara-${new Date().toISOString().slice(0, 10)}.webm`;
                    this.fileSizeFormatted = this.formatBytes(this.audioBlob.size);
                    this.state = 'stopped';
                    this.cleanupMediaTracks();
                };

                // Inisialisasi visualizer audio aktual
                this.setupRealVisualizer(this.mediaStream);

                // Mulai rekam
                this.mediaRecorder.start(100);
                this.seconds = 0;
                this.state = 'recording';

                // Jalankan timer counter
                this.timerInterval = setInterval(() => {
                    this.seconds++;
                    if (this.seconds >= this.maxDuration) {
                        this.stopRecording();
                    }
                }, 1000);

            } catch (err) {
                this.cleanup();
                this.state = 'error';

                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    this.errorMessage = 'Izin mikrofon belum diberikan atau ditolak oleh browser. Silakan klik ikon gembok pada address bar browser Anda lalu ubah setelan mikrofon ke "Izinkan" / "Allow".';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    this.errorMessage = 'Perangkat mikrofon tidak ditemukan di komputer/smartphone Anda. Pastikan mikrofon terhubung atau gunakan opsi Unggah File Audio.';
                } else if (err.name === 'NotReadableError' || err.name === 'TrackStartError') {
                    this.errorMessage = 'Mikrofon sedang digunakan oleh aplikasi lain atau tidak dapat diakses.';
                } else {
                    this.errorMessage = `Gagal mengaktifkan mikrofon: ${err.message || 'Terjadi kesalahan sistem'}.`;
                }
            }
        },

        /**
         * Setup visualizer level audio nyata menggunakan Web Audio API
         */
        setupRealVisualizer(stream) {
            try {
                const AudioCtxClass = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtxClass) return;

                this.audioContext = new AudioCtxClass();
                const source = this.audioContext.createMediaStreamSource(stream);
                this.analyser = this.audioContext.createAnalyser();
                this.analyser.fftSize = 32;
                source.connect(this.analyser);

                const dataArray = new Uint8Array(this.analyser.frequencyBinCount);

                const updateWaveform = () => {
                    if (this.state !== 'recording') return;
                    this.analyser.getByteFrequencyData(dataArray);

                    const step = Math.max(1, Math.floor(dataArray.length / 6));
                    this.visualizerBars = [
                        Math.max(15, Math.min(100, Math.round(dataArray[0 * step] / 2.55))),
                        Math.max(15, Math.min(100, Math.round(dataArray[1 * step] / 2.55))),
                        Math.max(15, Math.min(100, Math.round(dataArray[2 * step] / 2.55))),
                        Math.max(15, Math.min(100, Math.round(dataArray[3 * step] / 2.55))),
                        Math.max(15, Math.min(100, Math.round(dataArray[4 * step] / 2.55))),
                        Math.max(15, Math.min(100, Math.round(dataArray[5 * step] / 2.55))),
                    ];

                    this.animationFrame = requestAnimationFrame(updateWaveform);
                };

                this.animationFrame = requestAnimationFrame(updateWaveform);
            } catch (e) {
                // Audio visualizer opsional, graceful fallback
            }
        },

        /**
         * Hentikan perekaman dan siapkan preview audio
         */
        stopRecording() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
                this.animationFrame = null;
            }
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            } else {
                this.cleanupMediaTracks();
                this.state = 'stopped';
            }
        },

        /**
         * Batalkan perekaman dan buang audio sementara
         */
        cancelRecording() {
            this.stopAllAudioPlayback();
            this.cleanup();
            this.clearAudioPreview();
            this.seconds = 0;
            this.state = 'idle';
        },

        /**
         * Ulangi rekaman dari awal
         */
        resetRecording() {
            this.stopAllAudioPlayback();
            this.cleanup();
            this.clearAudioPreview();
            this.seconds = 0;
            this.state = 'idle';
            this.startRecording();
        },

        /**
         * Hapus rekaman audio atau berkas unggahan
         */
        deleteAudio() {
            this.stopAllAudioPlayback();
            this.cleanup();
            this.clearAudioPreview();
            this.seconds = 0;
            this.state = 'idle';
            this.errorMessage = '';
        },

        /**
         * Menangani unggahan file audio dari perangkat pengguna
         */
        handleFileUpload(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;

            this.errorMessage = '';
            this.clearAudioPreview();

            // 1. Validasi file tidak kosong
            if (file.size === 0) {
                this.state = 'error';
                this.errorMessage = 'Berkas audio yang dipilih kosong (0 bytes). Silakan pilih file audio lain.';
                event.target.value = '';
                return;
            }

            // 2. Validasi format file
            const validExtensions = ['mp3', 'wav', 'm4a', 'webm', 'ogg', 'aac', 'flac'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            const isValidType = (file.type && file.type.startsWith('audio/')) || validExtensions.includes(fileExt);

            if (!isValidType) {
                this.state = 'error';
                this.errorMessage = `Format berkas ".${fileExt}" tidak didukung. Harap pilih berkas audio seperti MP3, WAV, M4A, WEBM, atau OGG.`;
                event.target.value = '';
                return;
            }

            // 3. Validasi batas ukuran berkas
            if (file.size > this.maxFileSizeBytes) {
                this.state = 'error';
                this.errorMessage = `Ukuran berkas (${this.formatBytes(file.size)}) melebihi batas maksimum 15 MB. Silakan pilih berkas yang lebih kecil.`;
                event.target.value = '';
                return;
            }

            // Berhasil validasi
            this.uploadedFile = file;
            this.audioBlob = file;
            this.fileName = file.name;
            this.fileSizeFormatted = this.formatBytes(file.size);
            this.audioUrl = URL.createObjectURL(file);
            this.seconds = 0;
            this.state = 'stopped';
            event.target.value = '';
        },

        /**
         * Trigger klik dialog input file
         */
        triggerFileInput() {
            if (this.$refs && this.$refs.fileInput) {
                this.$refs.fileInput.click();
            }
        },

        /**
         * Buka dialog konfirmasi kesiapan integrasi backend
         */
        submitAudio() {
            this.showIntegrationModal = true;
        },

        /**
         * Deteksi MIME type audio yang didukung browser
         */
        getSupportedMimeType() {
            if (typeof MediaRecorder === 'undefined') return '';
            const candidates = [
                'audio/webm;codecs=opus',
                'audio/webm',
                'audio/mp4',
                'audio/ogg',
                'audio/wav',
            ];
            for (const candidate of candidates) {
                if (MediaRecorder.isTypeSupported(candidate)) {
                    return candidate;
                }
            }
            return '';
        },

        /**
         * Helper format ukuran bytes ke KB/MB
         */
        formatBytes(bytes) {
            if (!bytes || bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },

        /**
         * Hentikan pemutaran audio yang sedang aktif pada elemen audio player
         */
        stopAllAudioPlayback() {
            try {
                const audios = document.querySelectorAll('audio');
                audios.forEach(audio => {
                    audio.pause();
                    audio.currentTime = 0;
                    audio.removeAttribute('src');
                    audio.load();
                });
            } catch (e) {}
        },

        /**
         * Bersihkan object URL untuk mencegah memory leak dan hentikan suara
         */
        clearAudioPreview() {
            this.stopAllAudioPlayback();
            if (this.audioUrl) {
                URL.revokeObjectURL(this.audioUrl);
                this.audioUrl = null;
            }
            this.audioBlob = null;
            this.uploadedFile = null;
            this.fileName = '';
            this.fileSizeFormatted = '';
        },

        /**
         * Hentikan hardware track mikrofon
         */
        cleanupMediaTracks() {
            if (this.mediaStream) {
                this.mediaStream.getTracks().forEach(track => {
                    track.stop();
                });
                this.mediaStream = null;
            }
            if (this.audioContext && this.audioContext.state !== 'closed') {
                try {
                    this.audioContext.close();
                } catch (e) {}
                this.audioContext = null;
            }
        },

        /**
         * Bersihkan seluruh sumber daya (audio playback, hardware mic, timer, visualizer)
         */
        cleanup() {
            this.stopAllAudioPlayback();
            this.cleanupMediaTracks();
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
                this.animationFrame = null;
            }
        }
    };
}

