

@push('styles')
<style>
    #phScrollTop:focus { outline: 3px solid rgba(111,70,255,0.35); }
    #phScrollTop.highlight { background: linear-gradient(180deg,#7e56ff,#5b2bff); transform:translateY(-4px); }

<button id="phScrollTop" aria-label="Back to top" title="Back to top"
        style="position:fixed; right:18px; bottom:18px; z-index:1100; display:none; border:none; background:#6f46ff; color:#fff; width:52px; height:52px; border-radius:12px; box-shadow:0 6px 18px rgba(79,56,255,0.25); cursor:pointer; align-items:center; justify-content:center;"
        class="d-flex">
    <i class="fas fa-arrow-up" style="font-size:18px;"></i>
</button> 

</style>
@endpush



@push('scripts')
<script>
    (function(){
        const btn = document.getElementById('phScrollTop');
        if(!btn) return;

        function updateVisibility() {
            const y = window.scrollY || window.pageYOffset;
            // Show once user scrolls down a bit; stays visible while scrolling
            btn.style.display = y > 80 ? 'flex' : 'none';
        }

        // highlight when near bottom
        function updateHighlight() {
            const scrolledFromTop = window.scrollY + window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const nearBottom = (documentHeight - scrolledFromTop) < 160;
            btn.classList.toggle('highlight', nearBottom);
        }

        window.addEventListener('scroll', function(){ updateVisibility(); updateHighlight(); }, { passive: true });
        window.addEventListener('resize', function(){ updateVisibility(); updateHighlight(); });

        // Initial state
        updateVisibility();
        updateHighlight();

        btn.addEventListener('click', function(){
            window.scrollTo({ top: 0, behavior: 'smooth' });
            // flash highlight after click
            btn.classList.add('highlight');
            setTimeout(()=> btn.classList.remove('highlight'), 800);
        });
    })();
</script>
@endpush

