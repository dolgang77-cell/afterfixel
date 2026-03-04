document.addEventListener('DOMContentLoaded', () => {
    // Add Movie Popup HTML dynamically
    const popupOverlay = document.createElement('div');
    popupOverlay.className = 'movie-popup-overlay';
    popupOverlay.innerHTML = `
        <div class="movie-popup-content movie-popup-v2">
            <div class="popup-hero">
                <div class="popup-hd">HD</div>
                <video id="popup-video" autoplay muted loop playsinline
                    src="https://cdnbigfilepreview.flexcloud.co.kr/preview/mp4_dna_sd/6112/6112724aec7b1bd7f27993826ffadddc_565871304.mp4?ucode=&st=tjGsNxpujvYwCAjbUZFcuQ&e=1772593364"
                    style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;">
                </video>
                <div class="popup-hero-overlay">
                    <div class="popup-hero-title" id="popup-title"></div>
                    <button class="popup-play-btn"><i class="fa-solid fa-play"></i> 재생</button>
                </div>
            </div>
            <div class="popup-body">
                <div class="popup-meta" id="popup-meta"></div>
                <div class="popup-episodes">
                    <h3 class="episodes-title">에피소드 보기</h3>
                    <ul class="episode-list">
                        <li class="episode-item">
                            <span class="ep-name" id="popup-ep1"></span>
                            <button class="ep-play-btn"><i class="fa-solid fa-play"></i> 재생</button>
                        </li>
                        <li class="episode-item">
                            <span class="ep-name" id="popup-ep2"></span>
                            <button class="ep-play-btn"><i class="fa-solid fa-play"></i> 재생</button>
                        </li>
                        <li class="episode-item">
                            <span class="ep-name" id="popup-ep3"></span>
                            <button class="ep-play-btn"><i class="fa-solid fa-play"></i> 재생</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(popupOverlay);

    const popupVideo = popupOverlay.querySelector('#popup-video');

    function closeMoviePopup() {
        popupOverlay.classList.remove('active');
        if (popupVideo) { popupVideo.pause(); popupVideo.currentTime = 0; }
        if (popupOverlay.activeCard) {
            popupOverlay.activeCard.focus();
        }
    }

    const popupCloseBtn = popupOverlay.querySelector('.popup-close');
    if (popupCloseBtn) popupCloseBtn.addEventListener('click', closeMoviePopup);
    popupOverlay.addEventListener('click', (e) => {
        if (e.target === popupOverlay) closeMoviePopup();
    });

    // Search Button Navigation
    const searchBtns = document.querySelectorAll('.search-btn');
    searchBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            window.location.href = 'surch.html';
        });
        btn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                window.location.href = 'surch.html';
            }
        });
    });

    // Make sure movie.html starts at the very top by default
    // Make sure movie.html starts at the very top by default
    if (window.location.pathname.includes('movie.html') || window.location.pathname.includes('drama.html')) {
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        // Brute force scroll lock for the first 500ms to defeat Chrome/Edge F5 scroll memory
        let scrollLockUntil = performance.now() + 500;
        function forceTopScroll() {
            window.scrollTo(0, 0);
            if (performance.now() < scrollLockUntil) {
                requestAnimationFrame(forceTopScroll);
            } else {
                const activeNav = document.querySelector('.nav-menu a.active');
                if (activeNav) activeNav.focus({ preventScroll: true });
            }
        }
        forceTopScroll();
    }

    window.openMoviePopup = function (card) {
        const title = card.getAttribute('data-title') || '';
        const meta = card.getAttribute('data-meta') || '';
        const desc = card.getAttribute('data-desc') || '';
        // Use the card's thumbnail src as video poster
        const img = card.querySelector('img');
        const imgSrc = img ? img.src : '';

        // Show title if available, otherwise use desc as fallback title
        const displayTitle = title || desc.split('.')[0] || meta;
        document.getElementById('popup-title').innerText = displayTitle;
        document.getElementById('popup-meta').innerText = meta;
        // Seed episodes with a filename derived from meta (placeholder)
        const epName = meta ? meta.replace(/\|/g, '.').replace(/\s+/g, '').substring(0, 30) + '.720p.mp4' : '파일명.720p.mp4';
        ['popup-ep1', 'popup-ep2', 'popup-ep3'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = epName;
        });

        if (popupVideo) {
            popupVideo.poster = imgSrc;
            popupVideo.play().catch(() => { }); // Autoplay
        }

        popupOverlay.classList.add('active');
        popupOverlay.activeCard = card;
    };

    // Only Hero action falls to global click now
    // 3. Handle Hero section '에피소드 보기' (btn-info) opening the popup
    document.addEventListener('click', (e) => {
        const heroInfoBtn = e.target.closest('.hero-actions .btn-info');
        if (heroInfoBtn && typeof openMoviePopup === 'function') {
            const heroSection = heroInfoBtn.closest('.hero');
            const heroTitleEl = heroSection ? heroSection.querySelector('.hero-title') : null;
            const heroImgEl = heroSection ? heroSection.querySelector('.hero-img') : null;

            // Create a pseudo-card element to feed into openMoviePopup
            const pseudoCard = document.createElement('div');
            // Clean up the title text slightly (remove the 'N' badge text)
            let titleText = heroTitleEl ? heroTitleEl.innerText.replace(/N\s*/, '').trim() : '추천 콘텐츠';

            pseudoCard.setAttribute('data-title', titleText);
            pseudoCard.setAttribute('data-meta', '에피소드 | 초고화질');
            pseudoCard.setAttribute('data-desc', titleText);

            if (heroImgEl) {
                const img = document.createElement('img');
                img.src = heroImgEl.src;
                pseudoCard.appendChild(img);
            }

            openMoviePopup(pseudoCard);
        }
    });

    // Header background change on scroll
    const header = document.getElementById('header');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Automate making interactive elements focusable
    const addFocusable = document.querySelectorAll('.card, .profile-icon, .icon-btn');
    addFocusable.forEach(el => el.setAttribute('tabindex', '0'));

    // Define a reusable function to bind card interactions
    function initCardInteractions(card) {
        card.addEventListener('click', function (e) {
            // Check if card was already active BEFORE we process the click
            const wasActive = this.classList.contains('active') || (this.querySelector('.card') && this.querySelector('.card').classList.contains('active'));

            // Focus visually integrates with memory logic
            this.focus();

            const slider = this.closest('.slider');
            if (slider) {
                // Remove generic active state from peers
                const cardsInSlider = slider.querySelectorAll('.card, .rank-item');
                cardsInSlider.forEach(c => c.classList.remove('active'));

                const targetCard = this.classList.contains('card') ? this : this.querySelector('.card');
                if (targetCard) targetCard.classList.add('active');

                // Trigger the fade animation and update text content generically
                const section = slider.closest('.slider-section');
                if (section) {
                    const detailsView = section.querySelector('.expanded-details');
                    if (detailsView) {
                        detailsView.classList.add('active-details');
                        detailsView.style.animation = 'none';
                        detailsView.offsetHeight; /* trigger reflow */
                        detailsView.style.animation = null;
                    }
                }
                updateLandscapeCard(slider);
            }

            // Prevent the global document click listener from firing the popup if it wasn't already active
            if (!wasActive) {
                e.stopPropagation();
                return; // stop execution, card was just clicked to be focused and is now active, await next click
            }

            // At this point, wasActive is true.
            // Trigger popup programmatically if the click made it here (was active horizontally).
            if (wasActive && typeof openMoviePopup === 'function') {
                openMoviePopup(this);
            }
        });

        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const isActive = this.classList.contains('active') || (this.querySelector('.card') && this.querySelector('.card').classList.contains('active'));

                if (isActive) {
                    if (typeof openMoviePopup === 'function') {
                        openMoviePopup(this);
                    }
                } else {
                    this.click();
                }
            }
        });
    }

    // Add universal click interaction for ALL cards
    const allClickableCards = document.querySelectorAll('.slider-section .poster-card, .movie-card, .slider-section .rank-item, .slider-section .landscape-card');
    allClickableCards.forEach(initCardInteractions);

    let isAnimating = false;

    function updateLandscapeCard(slider) {
        const newActive = slider.querySelector('.active');
        if (!newActive) return;

        const allCardsCurrent = Array.from(slider.querySelectorAll('.poster-card, .movie-card, .movie-card, .landscape-card, .rank-card'));
        const currentIndex = allCardsCurrent.indexOf(newActive) + 1;

        const section = slider.closest('.slider-section');
        if (section) {
            const detailsContainer = section.querySelector('.details-content');
            if (detailsContainer) {

                const meta = newActive.getAttribute('data-meta');
                const desc = newActive.getAttribute('data-desc');



                if (meta && desc) {
                    detailsContainer.innerHTML = `
                        <p class="meta">${meta}</p>
                        <p class="desc">${desc}</p>
                    `;
                }
            }
        }
    }

    document.addEventListener('keydown', (e) => {
        const popupEl = document.querySelector('.movie-popup-overlay');
        if (popupEl && popupEl.classList.contains('active')) {
            // Backspace / Escape: close popup
            if (e.key === 'Escape' || e.key === 'Backspace') {
                e.preventDefault();
                closeMoviePopup();
                return;
            }
            // Arrow keys: navigate between play buttons only
            if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
                const btns = Array.from(popupEl.querySelectorAll('.popup-play-btn, .ep-play-btn'));
                if (btns.length === 0) return;
                const focused = document.activeElement;
                const idx = btns.indexOf(focused);
                if (e.key === 'ArrowDown') {
                    const next = idx + 1 < btns.length ? btns[idx + 1] : btns[0];
                    next.focus();
                } else {
                    const prev = idx - 1 >= 0 ? btns[idx - 1] : btns[btns.length - 1];
                    prev.focus();
                }
                return;
            }
            // Block all other arrow keys from affecting the page behind
            if (['ArrowLeft', 'ArrowRight', 'Enter'].includes(e.key)) {
                e.preventDefault();
                return;
            }
        }

        const key = e.key;
        if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(key)) return;

        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }

        const currentFocused = document.activeElement;

        // Ensure proper rows
        const rowContainers = Array.from(document.querySelectorAll('.header, .hero, .slider-section'));
        let currentRowIndex = rowContainers.findIndex(container => container.contains(currentFocused));

        // If nothing is focused, setup focus safely
        if (currentRowIndex === -1 && document.querySelectorAll('[tabindex="0"], a[href]').length > 0) {
            document.querySelectorAll('[tabindex="0"], a[href]')[0].focus({ preventScroll: true });
            return;
        }

        if (currentRowIndex !== -1) {
            // Up / Down Navigation
            if (key === 'ArrowUp') {
                for (let i = currentRowIndex - 1; i >= 0; i--) {
                    const prevContainer = rowContainers[i];
                    let target = null;

                    // Special rule: if moving up to header, jump to the active Home link first
                    if (prevContainer.classList.contains('header')) {
                        target = prevContainer.querySelector('.nav-menu a.active') || prevContainer.querySelector('a[href]');
                    }
                    if (!target) target = prevContainer.querySelector('[tabindex="0"], a[href], button');

                    if (target) {
                        target.focus({ preventScroll: true });
                        if (prevContainer.classList.contains('header')) {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            // Scroll section so title is visible below fixed header
                            prevContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                        break;
                    }
                }
            } else if (key === 'ArrowDown') {
                for (let i = currentRowIndex + 1; i < rowContainers.length; i++) {
                    const nextContainer = rowContainers[i];
                    const target = nextContainer.querySelector('[tabindex="0"], a[href], button');

                    if (target) {
                        target.focus({ preventScroll: true });
                        // Scroll section so title appears below fixed header
                        nextContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        break;
                    }
                }
            }
            // Left / Right Navigation (Carousel Scrolling/Looping)
            else if (key === 'ArrowLeft' || key === 'ArrowRight') {
                const slider = currentFocused.closest('.slider');

                if (slider) {
                    if (['movie-slider', 'drama-slider', 'variety-slider', 'rank-movie-slider', 'rank-drama-slider'].includes(slider.id)) {
                        // Complex DOM Shuffle Infinite Loop strictly for designated Sliders
                        if (slider.dataset.isAnimating === 'true') return;

                        // Dynamic Gap Calculation so Con1 (10px) and Con2 (5vw) both slide correctly
                        const computedGap = window.getComputedStyle(slider).gap;
                        // Provide a fallback of 10 if computed is empty or normal
                        const gap = computedGap && computedGap !== 'normal' ? parseFloat(computedGap) : 10;
                        slider.dataset.isAnimating = 'true';

                        if (key === 'ArrowRight') {
                            const currentItem = slider.children[0];
                            const nextItem = slider.children[1];

                            const currentCard = currentItem.classList.contains('card') ? currentItem : currentItem.querySelector('.card');
                            const nextCard = nextItem.classList.contains('card') ? nextItem : nextItem.querySelector('.card');

                            // Strip all active classes before assigning
                            const allCards = Array.from(slider.querySelectorAll('.card'));
                            allCards.forEach(card => {
                                card.classList.remove('active');
                                card.removeAttribute('tabindex');
                            });

                            // 1. Get current card's actual width before classes are stripped
                            const referencePortrait = slider.children[2] || slider.children[1];
                            const exitWidth = currentItem.offsetWidth;
                            const shiftDist = referencePortrait ? referencePortrait.offsetWidth + gap : exitWidth + gap;

                            // 2. Visually slide entire row LEFT using exact negative margin
                            // animate ONLY margin-left so image width/height snaps instantly
                            currentItem.style.transition = 'margin-left 0.5s ease-in-out';
                            currentItem.style.marginLeft = `-${shiftDist}px`;

                            // 3. Expand the next card synchronously
                            if (nextCard) {
                                nextCard.classList.add('active');
                            }

                            setTimeout(() => {
                                currentItem.style.transition = 'none';
                                currentItem.style.marginLeft = '0px';
                                slider.appendChild(currentItem);
                                void currentItem.offsetWidth;
                                currentItem.style.transition = '';

                                if (nextCard) {
                                    nextCard.setAttribute('tabindex', '0');
                                    nextCard.focus({ preventScroll: true });
                                }

                                updateLandscapeCard(slider);
                                slider.dataset.isAnimating = 'false';
                            }, 500);

                        } else if (key === 'ArrowLeft') {
                            const lastItem = slider.lastElementChild;
                            const currentItem = slider.children[0];

                            const lastCard = lastItem.classList.contains('card') ? lastItem : lastItem.querySelector('.card');
                            const currentCard = currentItem.classList.contains('card') ? currentItem : currentItem.querySelector('.card');

                            // 1. Prepare Transitions BEFORE stripping classes
                            // animate ONLY margin-left so image size snaps instantly
                            lastItem.style.transition = 'margin-left 0.5s ease-in-out';
                            if (currentCard) {
                                currentCard.style.transition = 'none';
                            }

                            // 2. Strip all active classes
                            const allCards = Array.from(slider.querySelectorAll('.card'));
                            allCards.forEach(card => {
                                card.classList.remove('active');
                                card.removeAttribute('tabindex');
                            });

                            // 3. Calculate slide distance and apply margin shift
                            const referencePortrait = slider.children[1];
                            const shiftDist = referencePortrait ? referencePortrait.offsetWidth + gap : currentItem.offsetWidth + gap;

                            slider.prepend(lastItem);
                            lastItem.style.transition = 'none';
                            lastItem.style.marginLeft = `-${shiftDist}px`;

                            void lastItem.offsetWidth; // reflow

                            // 4. Slide inward (animate ONLY margin-left)
                            lastItem.style.transition = 'margin-left 0.5s ease-in-out';
                            lastItem.style.marginLeft = '0px';

                            // 5. Expand the target synchronously so it flawlessly matches the slide easing
                            if (lastCard) {
                                lastCard.classList.add('active');
                            }

                            setTimeout(() => {
                                lastItem.style.transition = '';
                                if (currentCard) {
                                    currentCard.style.transition = '';
                                }

                                if (lastCard) {
                                    lastCard.setAttribute('tabindex', '0');
                                    lastCard.focus({ preventScroll: true });
                                }

                                updateLandscapeCard(slider);
                                slider.dataset.isAnimating = 'false';
                            }, 500);
                        }
                    } else {
                        // Standard Native Scroll for all other generic sliders
                        const items = Array.from(slider.querySelectorAll('.card, [tabindex="0"], button, a[href]')).filter(el => getComputedStyle(el).display !== 'none');
                        const idx = items.indexOf(currentFocused);

                        let nextItem = null;
                        if (key === 'ArrowRight' && idx + 1 < items.length) {
                            nextItem = items[idx + 1];
                        } else if (key === 'ArrowLeft' && idx - 1 >= 0) {
                            nextItem = items[idx - 1];
                        }

                        if (nextItem) {
                            // Strip active classes from everything in this slider
                            const allCards = Array.from(slider.querySelectorAll('.card'));
                            allCards.forEach(card => {
                                card.classList.remove('active');
                                card.removeAttribute('tabindex');
                            });

                            // Add classes exclusively to the new target
                            const targetCard = nextItem.classList.contains('card') ? nextItem : nextItem.querySelector('.card');
                            if (targetCard) {
                                targetCard.classList.add('active'); // IMPORTANT: Native generic scroll does not scale to landscape, layout remains stable
                                targetCard.setAttribute('tabindex', '0');
                            }

                            nextItem.focus({ preventScroll: true });
                            // Save Focus State
                            sessionStorage.setItem('lastFocusedElementClass', targetCard ? targetCard.className : nextItem.className);
                            sessionStorage.setItem('lastFocusedSectionIndex', currentRowIndex);

                            // Scroll slider cleanly so this element aligns as the leftmost visible item
                            const sliderStyle = window.getComputedStyle(slider);
                            const paddingLeft = parseInt(sliderStyle.paddingLeft || '0');

                            slider.scrollTo({
                                left: nextItem.offsetLeft - slider.offsetLeft - paddingLeft,
                                behavior: 'smooth'
                            });

                            // Sync UI text
                            updateLandscapeCard(slider);
                        }
                    }
                } else {
                    // Header/Hero horizontal menu navigation fallback
                    const currentContainer = rowContainers[currentRowIndex];
                    const items = Array.from(currentContainer.querySelectorAll('button, a[href], [tabindex="0"]')).filter(el => getComputedStyle(el).display !== 'none');
                    const idx = items.indexOf(currentFocused);
                    if (key === 'ArrowRight' && idx + 1 < items.length) items[idx + 1].focus();
                    if (key === 'ArrowLeft' && idx - 1 >= 0) items[idx - 1].focus();
                }
            }
        }
    });

    // Horizontal scroll on slider with mouse wheel
    const sliders = document.querySelectorAll('.slider');
    sliders.forEach(slider => {
        // Enforce basic layout behavior
        slider.style.overflow = 'hidden';
        slider.scrollLeft = 0;

        // For DOM shuffle sliders: clone all original cards once as right-edge buffer
        // This prevents the seam gap during the 500ms animation before a card is re-appended
        const isDOMShuffleSlider = ['movie-slider', 'drama-slider', 'variety-slider', 'rank-movie-slider', 'rank-drama-slider'].includes(slider.id) || slider.classList.contains('shuffle-slider');
        if (isDOMShuffleSlider) {
            // Clip cards that slide off the edge
            const wrapper = slider.closest('.slider-wrapper');
            if (wrapper) wrapper.style.overflow = 'hidden';

            const originals = Array.from(slider.children);
            originals.forEach(child => {
                const clone = child.cloneNode(true);
                const cloneCard = clone.classList.contains('card') ? clone : clone.querySelector('.card');
                if (cloneCard) {
                    cloneCard.classList.remove('active');
                    cloneCard.removeAttribute('tabindex');
                }
                slider.appendChild(clone);
                // Also bind click and enter events to the newly generated clone
                initCardInteractions(clone);
            });
        }

        // Define initial focus targets mapping only index 0 as landscape for explicitly marked sliders
        const children = Array.from(slider.children);
        if (children.length > 0) {
            children.forEach(child => {
                const card = child.classList.contains('card') ? child : child.querySelector('.card');
                if (card) {
                    card.classList.remove('active');
                    card.removeAttribute('tabindex');
                }
            });
            const firstCard = children[0].classList.contains('card') ? children[0] : children[0].querySelector('.card');
            if (firstCard) {
                firstCard.classList.add('active');
                if (isDOMShuffleSlider) {
                    firstCard.classList.add('active');
                }
                firstCard.setAttribute('tabindex', '0');
            }
        }

        // Initialize dynamic text content for the first item
        updateLandscapeCard(slider);

        // Mouse wheel smoothly maps to keydown standard logic without DOM mutations
        slider.addEventListener('wheel', (evt) => {
            evt.preventDefault();
            const simulatedEvent = new KeyboardEvent('keydown', {
                key: evt.deltaY > 0 ? 'ArrowRight' : 'ArrowLeft',
                bubbles: true
            });

            // Temporarily focus slider's card to fulfill matching requirements
            const target = slider.querySelector('.card.active, .card');
            if (target && target !== document.activeElement) target.focus({ preventScroll: true });
            document.dispatchEvent(simulatedEvent);
        }, { passive: false });
    });

    // Global Focus Tracker: Precisely record coordinates of what the user is looking at
    document.addEventListener('focusin', (e) => {
        const target = e.target;
        if (!target || target === document.body) return;

        const rowContainers = Array.from(document.querySelectorAll('.header, .hero, .slider-section'));
        const parentSection = target.closest('.slider-section, .hero, .header');

        if (parentSection) {
            const sectionIdx = rowContainers.indexOf(parentSection);

            // Get all focusable elements in this section
            const focusables = Array.from(parentSection.querySelectorAll('[tabindex="0"], a[href], button'));
            const elementIdx = focusables.indexOf(target);

            if (sectionIdx !== -1 && elementIdx !== -1) {
                sessionStorage.setItem('lastFocusedSectionIndex', sectionIdx);
                sessionStorage.setItem('lastFocusedElementIndex', elementIdx);
            }
        }
    });

    // Restore focus state on page load exactly using the index map
    const savedSectionIdx = sessionStorage.getItem('lastFocusedSectionIndex');
    const savedElementIdx = sessionStorage.getItem('lastFocusedElementIndex');

    if (savedSectionIdx !== null && savedElementIdx !== null) {
        setTimeout(() => {
            const rowContainers = Array.from(document.querySelectorAll('.header, .hero, .slider-section'));
            const container = rowContainers[parseInt(savedSectionIdx)];

            if (container) {
                const focusables = Array.from(container.querySelectorAll('[tabindex="0"], a[href], button'));
                const target = focusables[parseInt(savedElementIdx)] || focusables[0];
                if (target) {
                    target.focus({ preventScroll: true });
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }, 150);
    }
});

// ------------------------------------------------------------------
// Moved from cert.html inline script:
if (window.location.pathname.includes('cert.html')) {
    // Redirect to adult.html after 2 seconds (2000 ms)
    setTimeout(() => {
        window.location.href = 'adult.html';
    }, 2000);
}

// ------------------------------------------------------------------
// Moved from surch.html inline script:
if (window.location.pathname.includes('surch.html')) {
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchInput');
        const kbBtns = document.querySelectorAll('.kb-btn');
        const kbSpace = document.getElementById('kbSpace');
        const kbDelete = document.getElementById('kbDelete');
        const keyboardGrid = document.getElementById('keyboardGrid');
        const kbToggleLang = document.getElementById('kbToggleLang');

        // Set initially value to existing input value "화려한 날들"
        // Actually, per user request, it should be empty initially, 
        // and only filled when selecting history items (like '화려한 날들').
        searchInput.value = "";
        let kbBuffer = [];

        // Keyboard Toggle Logic via "abc123" button
        let isEnLayout = false;
        if (kbToggleLang && keyboardGrid) {
            kbToggleLang.addEventListener('click', () => {
                isEnLayout = !isEnLayout;
                if (isEnLayout) {
                    keyboardGrid.classList.add('en-layout');
                    kbToggleLang.innerText = "가나다";
                } else {
                    keyboardGrid.classList.remove('en-layout');
                    kbToggleLang.innerText = "abc123";
                }
            });
        }

        function updateSearchInput() {
            if (window.Hangul) {
                searchInput.value = Hangul.assemble(kbBuffer);
            } else {
                searchInput.value = kbBuffer.join(''); // Fallback if library fails to load
            }
        }

        kbBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (e.target.style.visibility === 'hidden') return;
                const char = e.target.innerText.trim();
                if (char) {
                    kbBuffer.push(char);
                    updateSearchInput();
                }
            });
        });

        if (kbSpace) {
            kbSpace.addEventListener('click', () => {
                kbBuffer.push(" ");
                updateSearchInput();
            });
        }

        if (kbDelete) {
            kbDelete.addEventListener('click', () => {
                if (kbBuffer.length > 0) {
                    kbBuffer.pop();
                    updateSearchInput();
                }
            });
        }

        // Sync History Items with Search Input
        const historyItems = document.querySelectorAll('.history-item');
        historyItems.forEach(item => {
            const triggerItem = () => {
                const text = item.innerText.trim();
                if (window.Hangul) {
                    kbBuffer = Hangul.disassemble(text);
                } else {
                    kbBuffer = text.split('');
                }
                updateSearchInput();
            };

            // Click
            item.addEventListener('click', triggerItem);

            // Enter key
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    triggerItem();
                }
            });
        });

        // --- Full Search Page Arrow Navigation ---
        // app.js handles navigation for .hero and .slider-section, which don't exist here.
        // We need a custom 2D spatial or logical navigator for surch.html

        // Define areas:
        // 0: Header (.nav-menu a, button)
        // 1: Keyboard Input Area (.keyboard-input-area button)
        // 2: Keyboard Grid (.keyboard-grid .kb-btn:visible, .kb-toggle-btn)
        // 3: Search History (.search-history-list .history-item)
        // 4: Results Grid (.result-grid .result-thumbnail)

        function getVisibleFocusables(selector) {
            return Array.from(document.querySelectorAll(selector)).filter(el => {
                const compStyle = getComputedStyle(el);
                return compStyle.display !== 'none' && compStyle.visibility !== 'hidden';
            });
        }

        let lastKbFocused = null;

        document.addEventListener('keydown', (e) => {
            const key = e.key;
            if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(key)) return;

            // If popup is open, let app.js handle it
            if (document.querySelector('.movie-popup-overlay.active')) return;

            e.preventDefault(); // Stop default scroll and app.js interference
            e.stopPropagation();

            const currentEl = document.activeElement;

            const headerItems = getVisibleFocusables('.nav-menu a, .nav-menu button');
            const inputItems = getVisibleFocusables('.keyboard-area-top button');
            const kbItems = getVisibleFocusables('.keyboard-grid button');
            const historyItems = getVisibleFocusables('.search-history-list .history-item');
            const resultItems = getVisibleFocusables('.result-grid .result-thumbnail');

            // Helper to find current area
            const isHeader = headerItems.includes(currentEl);
            const isInput = inputItems.includes(currentEl);
            const isKb = kbItems.includes(currentEl);
            const isHistory = historyItems.includes(currentEl);
            const isResult = resultItems.includes(currentEl);

            // Initial Focus Fallback
            if (!isHeader && !isInput && !isKb && !isHistory && !isResult) {
                if (inputItems.length > 0) inputItems[0].focus();
                return;
            }

            // 1. Header Navigation
            if (isHeader) {
                const idx = headerItems.indexOf(currentEl);
                if (key === 'ArrowRight' && idx < headerItems.length - 1) headerItems[idx + 1].focus();
                else if (key === 'ArrowLeft' && idx > 0) headerItems[idx - 1].focus();
                else if (key === 'ArrowDown') {
                    if (inputItems.length > 0) {
                        inputItems[0].focus();
                        inputItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    else if (resultItems.length > 0) {
                        resultItems[0].focus();
                        resultItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }

            // 2. Input Area Navigation
            else if (isInput) {
                const idx = inputItems.indexOf(currentEl);
                if (key === 'ArrowRight') {
                    if (idx < inputItems.length - 1) inputItems[idx + 1].focus();
                    else if (resultItems.length > 0) {
                        resultItems[0].focus(); // jump to results
                        resultItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                else if (key === 'ArrowLeft' && idx > 0) inputItems[idx - 1].focus();
                else if (key === 'ArrowUp') {
                    if (headerItems.length > 0) {
                        headerItems[0].focus();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
                else if (key === 'ArrowDown') {
                    if (kbItems.length > 0) {
                        // Smart down mapping matching exact geometric span
                        if (idx === 0) kbItems[2].focus(); // toggler to ㄴ
                        else if (idx === 1) kbItems[3].focus(); // space to ㄷ
                        else if (idx === 2) kbItems[4].focus(); // delete to ㄸ
                        else kbItems[0].focus();

                        document.activeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            }

            // 3. Keyboard Grid Navigation
            else if (isKb) {
                const idx = kbItems.indexOf(currentEl);
                const cols = 5;

                // Always remember we were on this keyboard button before moving
                lastKbFocused = currentEl;

                const allBtnsIncludingHidden = Array.from(keyboardGrid.querySelectorAll('.kb-btn'));
                const domIdx = allBtnsIncludingHidden.indexOf(currentEl);
                const col = domIdx % cols;
                const row = Math.floor(domIdx / cols);

                if (key === 'ArrowRight') {
                    let isRightEdge = false;
                    if (currentEl.classList.contains('kb-toggle-btn')) {
                        isRightEdge = true;
                    } else {
                        // User explicitly requested these keys to jump to thumbnails on ArrowRight
                        const rightEdgeKeys = ['ㄸ', 'ㅅ', 'ㅊ', 'ㅎ', 'ㅓ', 'ㅛ', 'ㅣ', '5', '0', 'e', 'j', 'o', 't', 'y', 'z'];
                        if (rightEdgeKeys.includes(currentEl.textContent.trim())) {
                            isRightEdge = true;
                        } else if (currentEl.classList.contains('en-key')) {
                            // For English layout, maintain dynamic edge detection
                            let nextBtnInDom = allBtnsIncludingHidden[domIdx + 1];
                            if (col === 4) {
                                isRightEdge = false;
                            } else if (col === 3) {
                                if (!nextBtnInDom || nextBtnInDom.style.visibility === 'hidden' || getComputedStyle(nextBtnInDom).display === 'none') {
                                    isRightEdge = true;
                                }
                            }
                        }
                    }

                    if (isRightEdge && resultItems.length > 0) {
                        resultItems[0].focus();
                        resultItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else if (!isRightEdge && idx < kbItems.length - 1) {
                        // If they press Right on ㅊ(col 4), they go sequentially to ㅋ.
                        kbItems[idx + 1].focus();
                    } else if (idx === kbItems.length - 1 && resultItems.length > 0) {
                        resultItems[0].focus();
                        resultItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                else if (key === 'ArrowLeft') {
                    if (idx > 0) kbItems[idx - 1].focus();
                }
                else if (key === 'ArrowDown') {
                    // User explicitly requested: ㅊ->ㅎ, ㅛ->ㅣ
                    if (currentEl.textContent.trim() === 'ㅊ') {
                        const targetBtn = Array.from(document.querySelectorAll('.kr-key')).find(k => k.textContent.trim() === 'ㅎ');
                        if (targetBtn && targetBtn.style.visibility !== 'hidden' && getComputedStyle(targetBtn).display !== 'none') {
                            targetBtn.focus();
                            return;
                        }
                    }
                    if (currentEl.textContent.trim() === 'ㅛ') {
                        const targetBtn = Array.from(document.querySelectorAll('.kr-key')).find(k => k.textContent.trim() === 'ㅣ');
                        if (targetBtn && targetBtn.style.visibility !== 'hidden' && getComputedStyle(targetBtn).display !== 'none') {
                            targetBtn.focus();
                            return;
                        }
                    }

                    let nextRow = row + 1;
                    let targetBtn = null;
                    let maxRows = Math.ceil(allBtnsIncludingHidden.length / cols);

                    while (nextRow < maxRows) {
                        let candidate = allBtnsIncludingHidden[nextRow * cols + col];
                        if (candidate && candidate.style.visibility !== 'hidden' && getComputedStyle(candidate).display !== 'none') {
                            targetBtn = candidate;
                            break;
                        } else {
                            // check leftwards in the same row
                            let found = false;
                            for (let c = col - 1; c >= 0; c--) {
                                let fb = allBtnsIncludingHidden[nextRow * cols + c];
                                if (fb && fb.style.visibility !== 'hidden' && getComputedStyle(fb).display !== 'none') {
                                    targetBtn = fb;
                                    found = true;
                                    break;
                                }
                            }

                            // Fallback: If still not found, just grab the absolute latest visible button on that row
                            if (!found) {
                                let rowStartIndex = nextRow * cols;
                                let flexTarget = allBtnsIncludingHidden.slice(rowStartIndex, rowStartIndex + cols).reverse().find(b => b && b.style.visibility !== 'hidden' && getComputedStyle(b).display !== 'none');
                                if (flexTarget) {
                                    targetBtn = flexTarget;
                                    found = true;
                                }
                            }

                            if (found) break;
                        }
                        nextRow++;
                    }

                    if (targetBtn) {
                        targetBtn.focus();
                    } else {
                        if (historyItems.length > 0) {
                            historyItems[0].focus();
                            historyItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                }
                else if (key === 'ArrowUp') {
                    let prevRow = row - 1;
                    let targetBtn = null;

                    while (prevRow >= 0) {
                        let candidate = allBtnsIncludingHidden[prevRow * cols + col];
                        if (candidate && candidate.style.visibility !== 'hidden' && getComputedStyle(candidate).display !== 'none') {
                            targetBtn = candidate;
                            break;
                        } else {
                            // check leftwards in the same row
                            let found = false;
                            for (let c = col - 1; c >= 0; c--) {
                                let fb = allBtnsIncludingHidden[prevRow * cols + c];
                                if (fb && fb.style.visibility !== 'hidden' && getComputedStyle(fb).display !== 'none') {
                                    targetBtn = fb;
                                    found = true;
                                    break;
                                }
                            }
                            if (found) break;
                        }
                        prevRow--;
                    }

                    if (targetBtn) {
                        targetBtn.focus();
                    } else {
                        if (inputItems.length > 0) {
                            // Smart up mapping based on visual column geometry
                            if (col <= 2) inputItems[0].focus(); // col 0,1,2 up to toggler
                            else if (col === 3) inputItems[1].focus(); // col 3 up to space
                            else if (inputItems.length > 2) inputItems[2].focus(); // col 4 up to delete
                            else inputItems[0].focus();

                            document.activeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                }
            }

            // 4. History Navigation
            else if (isHistory) {
                const idx = historyItems.indexOf(currentEl);
                if (key === 'ArrowDown' && idx < historyItems.length - 1) {
                    historyItems[idx + 1].focus();
                    historyItems[idx + 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                else if (key === 'ArrowUp') {
                    if (idx > 0) {
                        historyItems[idx - 1].focus();
                        historyItems[idx - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    else if (lastKbFocused && Array.from(document.querySelectorAll('.keyboard-grid button')).includes(lastKbFocused)) {
                        lastKbFocused.focus(); // Jump back to specifically where we left off
                        lastKbFocused.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    else if (kbItems.length > 0) {
                        kbItems[kbItems.length - 1].focus(); // fallback
                        kbItems[kbItems.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                else if (key === 'ArrowRight' && resultItems.length > 0) {
                    resultItems[0].focus();
                    resultItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            // 5. Results Grid Navigation (4 columns)
            else if (isResult) {
                const idx = resultItems.indexOf(currentEl);
                const cols = 4;

                if (key === 'ArrowRight' && idx < resultItems.length - 1) {
                    resultItems[idx + 1].focus();
                    resultItems[idx + 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                else if (key === 'ArrowLeft') {
                    if (idx % cols === 0) {
                        // Jump back to Left Sidebar explicitly revealing the input area
                        const sidebar = document.querySelector('.search-sidebar');
                        if (sidebar) sidebar.scrollTop = 0;

                        if (lastKbFocused && getVisibleFocusables('.keyboard-grid button').includes(lastKbFocused)) {
                            lastKbFocused.focus();
                            lastKbFocused.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else if (inputItems.length > 0) {
                            inputItems[inputItems.length - 1].focus();
                            inputItems[inputItems.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        } else if (kbItems.length > 0) {
                            kbItems[kbItems.length - 1].focus();
                            kbItems[kbItems.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    } else if (idx > 0) {
                        resultItems[idx - 1].focus();
                        resultItems[idx - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                else if (key === 'ArrowDown' && idx + cols < resultItems.length) {
                    resultItems[idx + cols].focus();
                    resultItems[idx + cols].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                else if (key === 'ArrowUp') {
                    if (idx - cols >= 0) {
                        resultItems[idx - cols].focus();
                        resultItems[idx - cols].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else if (inputItems.length > 0) {
                        inputItems[0].focus();
                        inputItems[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    } else if (headerItems.length > 0) {
                        headerItems[headerItems.length - 1].focus(); // Jump to header
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
            }
        }, true); // Use capture to intercept before app.js

        // Show right side input area returning to top when sidebar gets focus
        document.addEventListener('focusin', (e) => {
            const searchSidebar = document.querySelector('.search-sidebar');
            const searchContent = document.querySelector('.search-content');

            // 1. Return right side to top if left sidebar is focused
            if (searchSidebar && searchContent && searchSidebar.contains(e.target)) {
                searchContent.scrollTop = 0;
            }
        });
    });
}
