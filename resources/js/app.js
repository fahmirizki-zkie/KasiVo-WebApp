import { audioRecorder } from './audio-recorder.js';

window.audioRecorder = audioRecorder;

document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        window.Alpine.data('audioRecorder', audioRecorder);
    }
});

if (window.Alpine) {
    window.Alpine.data('audioRecorder', audioRecorder);
}
