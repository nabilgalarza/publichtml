        .wishlist-dropdown { display: none; position: absolute; top: 55px; right: 0; width: 300px; background: rgba(255,255,255,0.95); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.9); border-radius: 16px; box-shadow: 0 15px 50px rgba(0,0,0,0.1); z-index: 250; flex-direction: column; overflow: hidden; transform-origin: top right; }
        .wishlist-dropdown.show { display: flex; animation: improgypWishlistDropDown 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes improgypWishlistDropDown { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .wishlist-header { padding: 14px; border-bottom: 1px solid #f1f5f9; font-weight: 800; font-size: 14px; color: var(--text-dark, #0f172a); display: flex; justify-content: space-between; align-items: center; }
        .wishlist-items { max-height: 280px; overflow-y: auto; }
        .wishlist-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; transition: background 0.2s; }
        .wishlist-item:hover { background: #f8fafc; }
        .wishlist-item img { width: 40px; height: 40px; object-fit: contain; border-radius: 8px; background: #fff; border: 1px solid #f1f5f9; padding: 2px; flex-shrink: 0; cursor: pointer; }
        .wishlist-item-info { flex-grow: 1; min-width: 0; cursor: pointer; }
        .wishlist-item-title { font-size: 11px; font-weight: 700; color: var(--text-dark, #0f172a); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2; margin-bottom: 3px; }
        .wishlist-item-ref { font-size: 9px; color: #94a3b8; font-weight: 700; }
        .wishlist-item-price { font-size: 11px; color: var(--theme-green, #1B263B); font-weight: 800; }
        .wishlist-item-actions { display: flex; gap: 4px; flex-shrink: 0; }
        .wishlist-item-actions button { background: #f1f5f9; color: #94a3b8; padding: 8px; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s, color 0.2s; line-height: 1; }
        .wishlist-item-actions button:hover { background: #f43f5e; color: #fff; }
        .wishlist-item-actions button.wishlist-add-cart { background: rgba(27, 38, 59, 0.1); color: #1B263B; }
        .wishlist-item-actions button.wishlist-add-cart:hover { background: #1B263B; color: #fff; }
        .wishlist-footer { padding: 10px 14px; background: #f8fafc; text-align: center; border-top: 1px solid #f1f5f9; }
        .wishlist-footer a { font-size: 11px; font-weight: 800; color: var(--theme-green, #1B263B); text-decoration: none; transition: color 0.2s; display: inline-flex; align-items: center; gap: 4px; }
        .wishlist-empty { padding: 30px 14px; text-align: center; color: var(--text-muted, #64748b); font-size: 12px; font-weight: 500; }
