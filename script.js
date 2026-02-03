const burgerBtn = document.getElementById('burgerBtn');
        const sidebar = document.getElementById('sidebar');
        
        burgerBtn.addEventListener('click', () => {
            burgerBtn.classList.toggle('active');
            sidebar.classList.toggle('active');
        });
        
        sidebar.addEventListener('click', (e) => {
            if (e.target.tagName === 'A') {
                burgerBtn.classList.remove('active');
                sidebar.classList.remove('active');
            }
        });