document.addEventListener('DOMContentLoaded', () => {
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

    // Add universal click interaction for cards across all sliders
    const allClickableCards = document.querySelectorAll('.slider-section .poster-card, .slider-section .rank-item, .slider-section .landscape-card');

    allClickableCards.forEach(card => {
        card.addEventListener('click', function () {
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
        });

        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                this.click();
            }
        });
    });

    let isAnimating = false;

    function updateLandscapeCard(slider) {
        const newActive = slider.querySelector('.active-landscape') || slider.querySelector('.active');
        if (!newActive) return;

        const allCardsCurrent = Array.from(slider.querySelectorAll('.poster-card, .landscape-card, .rank-card'));
        const currentIndex = allCardsCurrent.indexOf(newActive) + 1;

        const section = slider.closest('.slider-section');
        if (section) {
            const detailsContainer = section.querySelector('.details-content');
            if (detailsContainer) {
                const title = newActive.getAttribute('data-title');
                const meta = newActive.getAttribute('data-meta');
                const desc = newActive.getAttribute('data-desc');



                if (title && meta && desc) {
                    detailsContainer.innerHTML = `
                        <p class="meta">${meta}</p>
                        <p class="desc">${desc}</p>
                    `;
                }
            }
        }
    }

    document.addEventListener('keydown', (e) => {
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
                if (currentRowIndex > 0) {
                    const prevContainer = rowContainers[currentRowIndex - 1];
                    let target = null;
                    // Special rule: if moving up from play button to header, jump to the active Home link first
                    if (currentRowIndex === 1 && prevContainer.classList.contains('header')) {
                        target = prevContainer.querySelector('.nav-menu a.active') || prevContainer.querySelector('a[href]');
                    }
                    if (!target) target = prevContainer.querySelector('[tabindex="0"]') || prevContainer.querySelector('a[href], button');

                    if (target) {
                        target.focus({ preventScroll: true });
                        if (prevContainer.classList.contains('header')) {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            // Scroll section so title is visible below fixed header
                            prevContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }
                }
            } else if (key === 'ArrowDown') {
                if (currentRowIndex < rowContainers.length - 1) {
                    const nextContainer = rowContainers[currentRowIndex + 1];
                    const target = nextContainer.querySelector('[tabindex="0"]') || nextContainer.querySelector('a[href], button');
                    if (target) {
                        target.focus({ preventScroll: true });
                        // Scroll section so title appears below fixed header
                        nextContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
                                card.classList.remove('active-landscape', 'active');
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
                                nextCard.classList.add('active-landscape', 'active');
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
                                card.classList.remove('active-landscape', 'active');
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
                                lastCard.classList.add('active-landscape', 'active');
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
                                card.classList.remove('active-landscape', 'active');
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
        const isDOMShuffleSlider = ['movie-slider', 'drama-slider', 'variety-slider', 'rank-movie-slider', 'rank-drama-slider'].includes(slider.id);
        if (isDOMShuffleSlider) {
            // Clip cards that slide off the edge
            const wrapper = slider.closest('.slider-wrapper');
            if (wrapper) wrapper.style.overflow = 'hidden';

            const originals = Array.from(slider.children);
            originals.forEach(child => {
                const clone = child.cloneNode(true);
                const cloneCard = clone.classList.contains('card') ? clone : clone.querySelector('.card');
                if (cloneCard) {
                    cloneCard.classList.remove('active-landscape', 'active');
                    cloneCard.removeAttribute('tabindex');
                }
                slider.appendChild(clone);
            });
        }

        // Define initial focus targets mapping only index 0 as landscape for explicitly marked sliders
        const children = Array.from(slider.children);
        if (children.length > 0) {
            children.forEach(child => {
                const card = child.classList.contains('card') ? child : child.querySelector('.card');
                if (card) {
                    card.classList.remove('active-landscape', 'active');
                    card.removeAttribute('tabindex');
                }
            });
            const firstCard = children[0].classList.contains('card') ? children[0] : children[0].querySelector('.card');
            if (firstCard) {
                firstCard.classList.add('active');
                if (isDOMShuffleSlider) {
                    firstCard.classList.add('active-landscape');
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
            const target = slider.querySelector('.active-landscape, .card');
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
