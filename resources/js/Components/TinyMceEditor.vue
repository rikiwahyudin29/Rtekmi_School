<script>
// Global promise to prevent multiple script tags when mounting multiple editors at once
let scriptPromise = null;
const loadTinyMCE = () => {
    if (window.tinymce) return Promise.resolve();
    if (scriptPromise) return scriptPromise;
    
    scriptPromise = new Promise((resolve) => {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.7/tinymce.min.js';
        script.onload = () => resolve();
        document.head.appendChild(script);
    });
    return scriptPromise;
};
</script>

<script setup>
import { onMounted, onUnmounted, watch, ref, nextTick } from 'vue';

const props = defineProps({
    modelValue: String,
    placeholder: String,
    id: String,
    height: {
        type: Number,
        default: 200
    }
});

const emit = defineEmits(['update:modelValue']);

const editorId = ref(props.id || 'editor-' + Math.random().toString(36).substr(2, 9));
let editorInstance = null;

const initEditor = () => {
    if (!window.tinymce) return;
    
    // Destroy previous instance if exists to avoid conflicts
    const existing = window.tinymce.get(editorId.value);
    if (existing) {
        existing.remove();
    }

    window.tinymce.init({
        selector: `#${editorId.value}`,
        height: props.height,
        menubar: 'file edit view insert format tools table help',
        branding: false,
        statusbar: true,
        external_plugins: {
            'formula': '/vendors/tinymce-formula/plugin.min.js'
        },
        formula: {
            mlang: 'mml' // MathML language for formula plugin
        },
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount formula'
        ],
        toolbar: 'undo redo | formatselect | formula | ' +
        'bold italic strikethrough forecolor backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'link image media table | removeformat | help | code',
        images_upload_handler: function (blobInfo, success, failure) {
            let formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            
            // Menggunakan axios bawaan Laravel agar otomatis menyertakan CSRF token
            window.axios.post('/admin/cbt/upload-image', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(response => {
                if (response.data && response.data.location) {
                    success(response.data.location);
                } else {
                    failure('Gagal upload: URL gambar tidak ditemukan.');
                }
            })
            .catch(error => {
                let msg = error.response?.data?.error || error.message;
                failure('Gagal upload: ' + msg);
            });
        },
        setup: (editor) => {
            // Setup events
            editorInstance = editor;
            editor.on('init', () => {
                editor.setContent(props.modelValue || '');
            });
            editor.on('Change KeyUp Undo Redo', () => {
                emit('update:modelValue', editor.getContent());
            });
        }
    });
};

onMounted(() => {
    loadTinyMCE().then(() => {
        nextTick(() => {
            setTimeout(initEditor, 50); // slight delay to ensure DOM is fully ready
        });
    });
});

onUnmounted(() => {
    if (editorInstance) {
        editorInstance.remove();
        editorInstance = null;
    }
});

watch(() => props.modelValue, (newVal) => {
    if (editorInstance && newVal !== editorInstance.getContent()) {
        editorInstance.setContent(newVal || '');
    }
});
</script>

<template>
    <div class="tinymce-wrapper w-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
        <textarea :id="editorId" :placeholder="placeholder" class="w-full min-h-[150px] border-none"></textarea>
    </div>
</template>

<style>
/* Adjust TinyMCE for dark mode and border radius */
.tinymce-wrapper .tox-tinymce {
    border: none !important;
    border-radius: 0.75rem !important;
}
</style>
