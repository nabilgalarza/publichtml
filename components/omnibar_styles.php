<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
    }
    .omni-input-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 6px 6px 6px 16px;
        border-radius: 100px;
        transition: all 0.3s ease;
    }
    .omni-input-wrapper:focus-within {
        box-shadow: 0 15px 50px rgba(27, 38, 59, 0.2);
        border-color: rgba(27, 38, 59, 0.4);
    }
    .omni-input {
        flex-grow: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 14px;
        color: #0f172a;
        font-family: 'Inter', system-ui, sans-serif;
        min-width: 0;
    }
    .omni-input::placeholder { color: #94a3b8; }
    .btn-send {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #1B263B;
        color: white;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: 0.2s;
        box-shadow: 0 4px 12px rgba(27, 38, 59, 0.3);
        flex-shrink: 0;
    }
    .btn-send:hover { transform: scale(1.05); background: #0E75AE; }
    .btn-send:disabled { opacity: 0.6; cursor: wait; transform: none; }

    .omni-bar-container {
        position: fixed;
        left: 50%;
        transform: translateX(-50%);
        width: 95%;
        max-width: 600px;
        z-index: 1985;
        bottom: max(1.25rem, env(safe-area-inset-bottom, 0px));
        pointer-events: none;
    }
    .omni-bar-container .glass-panel { pointer-events: auto; }
    @media (min-width: 768px) {
        .omni-bar-container { display: none !important; }
    }

    .ai-bubble {
        position: fixed;
        bottom: calc(5rem + env(safe-area-inset-bottom, 0px));
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        width: 95%;
        max-width: 450px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(24px);
        border: 1px solid rgba(14, 117, 174, 0.25);
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
        z-index: 1990;
        opacity: 0;
        pointer-events: none;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .ai-bubble.show {
        opacity: 1;
        pointer-events: auto;
        transform: translateX(-50%) translateY(0);
    }
    .ai-bubble::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 10px solid transparent;
        border-right: 10px solid transparent;
        border-top: 10px solid rgba(255, 255, 255, 0.95);
    }
    @media (min-width: 768px) {
        .ai-bubble {
            top: 88px;
            bottom: auto;
            transform: translateX(-50%) translateY(-20px);
        }
        .ai-bubble.show { transform: translateX(-50%) translateY(0); }
        .ai-bubble::after {
            top: -8px;
            bottom: auto;
            border-top: none;
            border-bottom: 10px solid rgba(255, 255, 255, 0.95);
        }
    }
    .ai-bubble-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 10px;
    }
    .ai-bubble-title {
        font-size: 12px;
        font-weight: 800;
        color: #1B263B;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ai-bubble-close {
        color: #94a3b8;
        cursor: pointer;
        background: none;
        border: none;
        padding: 4px;
    }
    .ai-bubble-close:hover { color: #f43f5e; }
    .ai-bubble-text {
        font-size: 13px;
        color: #0f172a;
        line-height: 1.5;
        font-weight: 500;
    }
</style>
