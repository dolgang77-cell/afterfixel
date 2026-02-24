document.addEventListener('DOMContentLoaded', () => {
    // Dropdown toggles
    const tabItems = document.querySelectorAll('.tab-item');
    tabItems.forEach(item => {
        const trigger = item.querySelector('.tab-trigger');
        const dropdown = item.querySelector('.dropdown-content');

        if (trigger && dropdown) {
            // click to toggle
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                
                // close other dropdowns first
                document.querySelectorAll('.dropdown-content').forEach(content => {
                    if(content !== dropdown) content.classList.remove('active');
                });

                dropdown.classList.toggle('active');
            });

            // Prevent closing when clicking inside dropdown
            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    });

    // Close when clicking outside
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-content').forEach(content => {
            content.classList.remove('active');
        });
    });

    // Handle Parallax Background manually from inside script.js instead of inline if needed,
    // though the logic in index.html for scroll is intact.
});
