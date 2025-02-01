<template>
  <div class="ql-container">
    <div ref="editor"></div>
  </div>
</template>

<script setup lang="ts">
import {ref, onMounted, defineProps, defineEmits, watch} from 'vue';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// Props Interface
interface Props {
  modelValue: string;
  disabled?: boolean;
  append?: string | null;  // New append prop to insert text dynamically
}

interface Emits {
  (e: 'update:modelValue', value: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const editor = ref<HTMLDivElement | null>(null);
let quillInstance: Quill;

const toolbarOptions = [
  ['bold', 'italic', 'underline'],
  [{'header': 1}, {'header': 2}, {'header': 3}, {'header': 4}],
  [{'list': 'ordered'}, {'list': 'bullet'}],
  [{'color': []}, {'background': []}],
  [{'align': []}],
  ['link', 'image'],
  ['clean']
];

// Initialize Quill on mount
onMounted(() => {
  if (editor.value) {
    quillInstance = new Quill(editor.value, {
      theme: 'snow',
      modules: {
        toolbar: toolbarOptions,
      },
    });

    // Set initial content from modelValue
    quillInstance.root.innerHTML = props.modelValue;

    // Listen to Quill content changes
    quillInstance.on('text-change', () => {
      emit('update:modelValue', quillInstance.root.innerHTML);
    });

    // Disable Quill if necessary
    if (props.disabled) {
      quillInstance.enable(false);
      quillInstance.root.setAttribute('contenteditable', 'false');
    }

    // Watch for disabled state and adjust editor
    watch(() => props.disabled, (newVal) => {
      if (quillInstance) {
        quillInstance.enable(!newVal);
        quillInstance.root.setAttribute('contenteditable', newVal ? 'false' : 'true');
      }
    }, {immediate: true});

    // Watch for modelValue updates and sync with Quill
    watch(
        () => props.modelValue,
        (newValue) => {
          if (quillInstance.root.innerHTML !== newValue) {
            quillInstance.root.innerHTML = newValue;
          }
        },
        {immediate: true}
    );

    // Watch for append prop and insert at cursor
    watch(
        () => props.append,
        (newValue) => {
          if (newValue && newValue.length > 0) {
            insertTextAtCursor(newValue);
            resetAppend();
          }
        }
    );
  }
});

// Insert text at the current cursor position
const insertTextAtCursor = (text: string) => {
  const range = quillInstance.getSelection(true);  // Get current selection range
  if (range) {
    quillInstance.insertText(range.index, text);  // Insert text at cursor
    quillInstance.setSelection(range.index + text.length);  // Move cursor after inserted text
  } else {
    // Fallback if no cursor: Append at the end
    const length = quillInstance.getLength();
    quillInstance.insertText(length, text);
  }
};

// Reset the append prop to avoid repeat insertions
const resetAppend = () => {
  emit('update:modelValue', quillInstance.root.innerHTML);
};
</script>

<style scoped>
.ql-container {
  min-height: 300px;
  border: 1px solid #cccccc;
}
</style>