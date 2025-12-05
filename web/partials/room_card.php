<?php
// partial/room_card.php
$featuresList = isset($room['features']) ? explode(',', $room['features']) : [];
?>

<div class="group relative bg-surface border border-white/5 rounded-xl overflow-hidden hover:border-gold/30 hover:shadow-gold-glow transition-all duration-500">
    
    <div class="h-64 overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-t from-surface to-transparent z-10"></div>
        <?php if(!empty($room['image'])): ?>
            <img src="/assets/rooms/<?= htmlspecialchars($room['image']) ?>" 
                 class="w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-105 transition-all duration-700" 
                 alt="Room">
        <?php else: ?>
             <div class="w-full h-full bg-void flex items-center justify-center text-gray-800 font-serif">NO IMAGE</div>
        <?php endif; ?>
        
        <div class="absolute top-4 right-4 bg-gold/10 backdrop-blur-md border border-gold/20 text-gold px-4 py-2 rounded-full z-20">
            <span class="font-serif font-bold text-lg"><?= (int)$room['price'] ?></span>
            <span class="text-[9px] uppercase tracking-wide ml-1">Credits</span>
        </div>
    </div>

    <div class="p-6 relative z-20 -mt-12">
        <div class="text-[10px] text-gray-500 font-bold tracking-[0.2em] uppercase mb-1">
            Suite <?= htmlspecialchars($room['number']) ?>
        </div>
        
        <h3 class="font-serif text-2xl text-white mb-3 group-hover:text-gold transition-colors">
            <?= htmlspecialchars($room['title']) ?>
        </h3>

        <p class="text-gray-400 text-sm leading-relaxed mb-6 line-clamp-2 font-light">
            <?= htmlspecialchars($room['description']) ?>
        </p>

        <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach($featuresList as $feat): ?>
                <span class="text-[10px] uppercase tracking-wide text-gray-500 border border-white/10 px-2 py-1 rounded">
                    <?= htmlspecialchars(trim($feat)) ?>
                </span>
            <?php endforeach; ?>
        </div>

        <button hx-post="/api/room/book?id=<?= $room['id'] ?>"
                hx-swap="outerHTML"
                class="w-full py-3 bg-white/5 hover:bg-gold hover:text-black text-gold border border-gold/20 hover:border-gold transition-all rounded-lg uppercase text-xs font-bold tracking-[0.2em]">
            <?= $ui['book_btn'] ?? 'Reserve' ?>
        </button>
    </div>
</div>