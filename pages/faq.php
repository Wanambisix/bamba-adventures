<?php
require_once __DIR__ . '/../includes/functions.php';
$pageTitle = 'FAQ | Bamba Adventures';
$pageDescription = 'Find answers to common questions about our tours, bookings, and policies.';
include __DIR__ . '/../includes/header.php';
?>

<style>
    .page-hero { min-height: 300px; position: relative; display: flex; align-items: flex-end; padding-top: 75px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
    .page-hero-content { position: relative; z-index: 2; padding: 4rem 5% 3.5rem; max-width: 1400px; margin: 0 auto; width: 100%; }
    .page-tag { display: inline-block; background: var(--accent); color: var(--text-dark); font-size: 0.78rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; padding: 0.35rem 1rem; border-radius: 20px; margin-bottom: 1rem; }
    .page-hero-content h1 { font-size: clamp(2.2rem, 5vw, 3.8rem); color: var(--white); line-height: 1.1; text-shadow: 2px 2px 12px rgba(0,0,0,0.4); margin-bottom: 0.5rem; }
    .page-hero-content p { font-size: 1.1rem; color: rgba(255,255,255,0.9); max-width: 600px; }

    .faq-search-wrap { background: var(--white); padding: 2rem 5%; border-bottom: 1px solid #eee; }
    .faq-search-inner { max-width: 700px; margin: 0 auto; position: relative; }
    .faq-search-inner input { width: 100%; padding: 1rem 1.25rem 1rem 3.2rem; border: 2px solid #e0e0e0; border-radius: 50px; font-family: inherit; font-size: 0.95rem; outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
    .faq-search-inner input:focus { border-color: var(--primary-dark); box-shadow: 0 0 0 4px rgba(132,30,34,0.08); }
    .faq-search-inner i { position: absolute; left: 1.1rem; top: 50%; transform: translateY(-50%); color: var(--primary); font-size: 1.05rem; }

    .faq-outer { max-width: 1200px; margin: 0 auto; padding: 4rem 5%; display: grid; grid-template-columns: 240px 1fr; gap: 4rem; align-items: start; }

    .faq-sidebar { position: sticky; top: 95px; }
    .faq-sidebar h4 { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: var(--text-light); margin-bottom: 1rem; }
    .faq-cat-list { list-style: none; }
    .faq-cat-list li { margin-bottom: 0.35rem; }
    .faq-cat-list a { text-decoration: none; color: var(--text-dark); font-size: 0.9rem; padding: 0.5rem 0.9rem; border-radius: 8px; display: flex; align-items: center; gap: 0.6rem; transition: all 0.2s; border-left: 3px solid transparent; }
    .faq-cat-list a:hover, .faq-cat-list a.active { background: var(--bg-light); color: var(--primary-dark); border-left-color: var(--accent); font-weight: 600; }
    .faq-cat-list a i { width: 18px; color: var(--primary); font-size: 0.88rem; }

    .faq-main { min-width: 0; }
    .faq-section { margin-bottom: 3.5rem; }
    .faq-section-header { display: flex; align-items: center; gap: 0.9rem; margin-bottom: 1.75rem; padding-bottom: 1rem; border-bottom: 2px solid var(--bg-light); }
    .faq-section-icon { width: 46px; height: 46px; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--white); font-size: 1.1rem; flex-shrink: 0; }
    .faq-section-header h2 { font-size: 1.55rem; color: var(--primary-dark); }

    .faq-item { border: 1px solid #eee; border-radius: 14px; margin-bottom: 0.75rem; overflow: hidden; transition: box-shadow 0.2s; }
    .faq-item:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.07); }
    .faq-item.open { box-shadow: 0 6px 24px rgba(132,30,34,0.1); border-color: rgba(132,30,34,0.2); }
    .faq-question { width: 100%; background: var(--white); border: none; padding: 1.25rem 1.5rem; text-align: left; font-family: 'Poppins', sans-serif; font-size: 0.95rem; font-weight: 600; color: var(--text-dark); cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 1rem; transition: background 0.2s; }
    .faq-item.open .faq-question { background: var(--bg-light); color: var(--primary-dark); }
    .faq-question:hover { background: var(--bg-light); }
    .faq-chevron { font-size: 0.85rem; color: var(--primary); transition: transform 0.3s ease; flex-shrink: 0; }
    .faq-item.open .faq-chevron { transform: rotate(180deg); color: var(--primary-dark); }
    .faq-answer { display: none; padding: 0 1.5rem 1.4rem; color: var(--text-light); font-size: 0.93rem; line-height: 1.85; }
    .faq-item.open .faq-answer { display: block; }
    .faq-answer a { color: var(--primary-dark); font-weight: 600; }
    .faq-answer a:hover { text-decoration: underline; }
    .faq-answer ul { margin: 0.75rem 0 0.75rem 1.25rem; }
    .faq-answer ul li { margin-bottom: 0.4rem; }

    .still-questions { background: linear-gradient(135deg, var(--primary-dark), var(--primary)); border-radius: 20px; padding: 3rem; text-align: center; color: var(--white); margin-top: 1rem; }
    .still-questions h3 { font-size: 1.8rem; margin-bottom: 0.75rem; }
    .still-questions p { opacity: 0.88; margin-bottom: 2rem; font-size: 1rem; max-width: 500px; margin-left: auto; margin-right: auto; }
    .contact-options { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
    .contact-btn { padding: 0.85rem 1.75rem; border-radius: 30px; text-decoration: none; font-weight: 600; font-size: 0.93rem; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; }
    .contact-btn.gold { background: var(--accent); color: var(--text-dark); }
    .contact-btn.gold:hover { transform: translateY(-2px); }
    .contact-btn.outline { background: transparent; color: var(--white); border: 2px solid rgba(255,255,255,0.6); }
    .contact-btn.outline:hover { background: rgba(255,255,255,0.12); transform: translateY(-2px); }

    .fade-in { opacity: 0; transform: translateY(28px); transition: all 0.8s ease; }
    .fade-in.visible { opacity: 1; transform: translateY(0); }

    @media(max-width: 968px) { .faq-outer { grid-template-columns: 1fr; } .faq-sidebar { position: static; } }
    @media(max-width: 640px) { .contact-options { flex-direction: column; align-items: center; } }
</style>

<div class="page-hero">
    <div class="page-hero-content">
        <span class="page-tag">Help Centre</span>
        <h1>Frequently Asked Questions</h1>
        <p>Everything you need to know before, during, and after your journey with Bamba Adventures</p>
    </div>
</div>

<div class="faq-search-wrap">
    <div class="faq-search-inner">
        <i class="fas fa-search"></i>
        <input type="text" id="faqSearch" placeholder="Search frequently asked questions..." oninput="filterFAQ(this.value)">
    </div>
</div>

<div class="faq-outer">
    <aside class="faq-sidebar">
        <h4>Categories</h4>
        <ul class="faq-cat-list">
            <li><a href="#booking" class="cat-link active"><i class="fas fa-calendar-check"></i> Booking &amp; Payments</a></li>
            <li><a href="#tours" class="cat-link"><i class="fas fa-route"></i> Tours &amp; Itineraries</a></li>
            <li><a href="#travel-docs" class="cat-link"><i class="fas fa-passport"></i> Travel Documents</a></li>
            <li><a href="#health-safety" class="cat-link"><i class="fas fa-shield-alt"></i> Health &amp; Safety</a></li>
            <li><a href="#accommodation" class="cat-link"><i class="fas fa-bed"></i> Accommodation</a></li>
            <li><a href="#safari" class="cat-link"><i class="fas fa-paw"></i> Safari &amp; Wildlife</a></li>
            <li><a href="#accessibility" class="cat-link"><i class="fas fa-wheelchair"></i> Accessibility</a></li>
            <li><a href="#cancellation" class="cat-link"><i class="fas fa-undo"></i> Cancellations &amp; Refunds</a></li>
        </ul>
    </aside>

    <main class="faq-main">

        <!-- BOOKING & PAYMENTS -->
        <div class="faq-section fade-in" id="booking">
            <div class="faq-section-header">
                <div class="faq-section-icon"><i class="fas fa-calendar-check"></i></div>
                <h2>Booking &amp; Payments</h2>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">How do I book a tour with Bamba Adventures?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Booking is simple. You can:</p>
                    <ul>
                        <li>Click <strong>Book Now</strong> on any tour page and complete the booking form</li>
                        <li>Send an enquiry using the <strong>Send Enquiry</strong> button on any tour &mdash; our team responds within 24 hours</li>
                        <li>Call or WhatsApp us directly on <a href="tel:+254706606606">+254 706 606 606</a></li>
                        <li>Email us at <a href="mailto:info@bambaadventures.co.ke">info@bambaadventures.co.ke</a></li>
                    </ul>
                    <p>We recommend enquiring first for custom or multi-destination itineraries so we can tailor everything to your needs.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What deposit is required to confirm a booking?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>We typically require a <strong>25% deposit</strong> to secure your booking, with the balance due 60 days before departure. For bookings made within 60 days of travel, full payment is required at the time of booking. For group bookings of 8 or more, different terms may apply &mdash; contact us for details.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What payment methods do you accept?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>We accept the following payment methods:</p>
                    <ul>
                        <li>Bank transfer (preferred for larger amounts)</li>
                        <li>M-Pesa (for Kenya-based clients)</li>
                        <li>Credit / debit card via our secure payment gateway</li>
                        <li>PayPal (subject to applicable fees)</li>
                    </ul>
                    <p>All prices are quoted in USD. Payments in KES are converted at the prevailing rate on the day of payment.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Can I get a custom quote for a private or group tour?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Absolutely. All of our tours can be customised &mdash; dates, accommodation tier, group size, and itinerary. Simply use the <a href="/book">Request a Custom Package</a> form or call our specialists. Private tours can be arranged for as few as 2 people.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Are prices per person or for the whole group?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>All listed prices are <strong>per person</strong> based on shared double/twin occupancy. Single supplements apply for solo travellers. Prices for groups of 6 or more often carry a discount &mdash; contact us to get a group quote.</p>
                </div>
            </div>
        </div>

        <!-- TOURS & ITINERARIES -->
        <div class="faq-section fade-in" id="tours">
            <div class="faq-section-header">
                <div class="faq-section-icon"><i class="fas fa-route"></i></div>
                <h2>Tours &amp; Itineraries</h2>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Can I customise an existing itinerary?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Yes &mdash; every itinerary we publish is a starting point, not a fixed programme. We can adjust durations, swap accommodation, add or remove destinations, change activity types, or combine multiple itineraries into one seamless journey. Just tell us your preferences and we'll build the perfect trip.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What is the best time of year to visit Kenya for a safari?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Kenya offers excellent wildlife viewing year-round, but the best periods are:</p>
                    <ul>
                        <li><strong>July &ndash; October:</strong> Peak season. The wildebeest migration crosses the Mara River from late July, offering dramatic wildlife drama.</li>
                        <li><strong>January &ndash; March:</strong> Dry, hot, and excellent game viewing with fewer crowds. Calving season in the Serengeti border areas.</li>
                        <li><strong>November &ndash; December:</strong> Short rains bring green landscapes and some of the best photographic light, with lower prices.</li>
                    </ul>
                    <p>The long rains (April&ndash;June) can limit some game drives but are ideal for birding and budget travel.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Are international flights included in tour prices?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>International flights are <strong>not included</strong> in our standard tour prices unless explicitly stated. However, our air ticketing team can source competitive fares and handle all flight arrangements for you &mdash; simply ask when enquiring. All ground transfers within the tour destination are included.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What is the minimum group size for a tour?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Most of our tours operate from <strong>2 people</strong> minimum. Many private safari and beach packages can be arranged for solo travellers. Group departures on shared itineraries typically require a minimum of 6. We'll always confirm the minimum for your specific tour when you enquire.</p>
                </div>
            </div>
        </div>

        <!-- TRAVEL DOCUMENTS -->
        <div class="faq-section fade-in" id="travel-docs">
            <div class="faq-section-header">
                <div class="faq-section-icon"><i class="fas fa-passport"></i></div>
                <h2>Travel Documents &amp; Visas</h2>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Do I need a visa to visit Kenya?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Most nationalities require a visa to enter Kenya. Kenya operates an <strong>eVisa system</strong> &mdash; apply online at the official Kenya eCitizen portal before travel. The eVisa is typically processed within 3&ndash;5 business days. East African Community members and certain treaty nations may be exempt. Our team can advise you on requirements for your specific nationality and assist with the application process.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What vaccinations do I need for Kenya and East Africa?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Consult a travel health clinic or your GP 6&ndash;8 weeks before departure. Typically recommended vaccinations include:</p>
                    <ul>
                        <li>Hepatitis A and Typhoid (food and water)</li>
                        <li>Yellow Fever (required if arriving from a yellow fever endemic country)</li>
                        <li>Tetanus, Diphtheria, Polio (routine boosters)</li>
                        <li>Malaria prophylaxis (strongly recommended for most regions)</li>
                    </ul>
                    <p>Requirements vary by destination &mdash; always get personalised advice from a certified travel health professional.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Is travel insurance mandatory?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>We <strong>strongly recommend</strong> comprehensive travel insurance for all travellers. It should cover medical emergencies (including medical evacuation), trip cancellation, lost baggage, and personal liability. For adventure activities such as trekking or safari, ensure your policy covers these activities specifically. We can recommend insurance providers on request.</p>
                </div>
            </div>
        </div>

        <!-- HEALTH & SAFETY -->
        <div class="faq-section fade-in" id="health-safety">
            <div class="faq-section-header">
                <div class="faq-section-icon"><i class="fas fa-shield-alt"></i></div>
                <h2>Health &amp; Safety</h2>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">How safe is it to travel in Kenya and East Africa?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Safety is our top priority. Kenya's major national parks and tourist areas are well managed and millions of visitors travel safely every year. Our guides are trained in first aid and emergency response, and we maintain 24/7 in-country support for all guests. We monitor government travel advisories continuously and will advise you promptly if conditions change for your destination.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What should I pack for a safari?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Essential safari packing tips:</p>
                    <ul>
                        <li><strong>Clothing:</strong> Neutral tones (khaki, olive, beige) &mdash; avoid white and bright colours. Layers are key as mornings and evenings can be cool.</li>
                        <li><strong>Sun protection:</strong> High-SPF sunscreen, sunglasses, and a wide-brimmed hat</li>
                        <li><strong>Footwear:</strong> Comfortable walking shoes or boots for bush walks</li>
                        <li><strong>Binoculars</strong> for game viewing &mdash; a minimum 8&ndash;42 magnification is recommended</li>
                        <li><strong>Camera:</strong> Bring extra memory cards and batteries; a dust bag is advised</li>
                        <li><strong>Medications:</strong> Malaria prophylaxis, antihistamines, rehydration sachets</li>
                    </ul>
                    <p>We send every confirmed guest a detailed packing list tailored to their specific itinerary.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Is it safe to drink tap water in East Africa?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>We advise against drinking tap water in most East African destinations. All reputable lodges and camps provide safe drinking water, and bottled water is widely available. Our vehicles carry bottled water on all game drives. If you are trekking or camping in remote areas, water purification tablets or a filtration system are recommended.</p>
                </div>
            </div>
        </div>

        <!-- ACCOMMODATION -->
        <div class="faq-section fade-in" id="accommodation">
            <div class="faq-section-header">
                <div class="faq-section-icon"><i class="fas fa-bed"></i></div>
                <h2>Accommodation</h2>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What types of accommodation do you offer?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>We work across all accommodation tiers to suit every budget and preference:</p>
                    <ul>
                        <li><strong>Budget / Camping:</strong> Permanent tented camps and mobile camping &mdash; ideal for adventurous travellers</li>
                        <li><strong>Mid-range Lodges:</strong> Comfortable lodges and fixed camps with en-suite facilities</li>
                        <li><strong>Luxury Camps &amp; Lodges:</strong> Premium properties with exceptional service, gourmet dining, and exclusive locations</li>
                        <li><strong>Ultra-Luxury / Private Camps:</strong> Exclusive-use camps and boutique properties with full concierge service</li>
                    </ul>
                    <p>You can specify your preference when booking and we'll match you with the best available properties for your dates and destination.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Can you accommodate solo travellers?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Yes. We welcome solo travellers and can arrange single occupancy rooms or tents. A <strong>single supplement</strong> applies as lodges base their rates on shared occupancy. In some cases we can match solo travellers with others in the same group to avoid the supplement &mdash; ask us when enquiring.</p>
                </div>
            </div>
        </div>

        <!-- SAFARI & WILDLIFE -->
        <div class="faq-section fade-in" id="safari">
            <div class="faq-section-header">
                <div class="faq-section-icon"><i class="fas fa-paw"></i></div>
                <h2>Safari &amp; Wildlife</h2>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">When is the Great Migration in Kenya?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>The wildebeest migration is a year-round circular movement between Tanzania's Serengeti and Kenya's Maasai Mara. Key moments in Kenya include:</p>
                    <ul>
                        <li><strong>July &ndash; August:</strong> The herds begin crossing the Mara River from the Serengeti &mdash; the most dramatic and sought-after spectacle</li>
                        <li><strong>August &ndash; October:</strong> Peak viewing as massive herds graze the Maasai Mara grasslands</li>
                        <li><strong>November:</strong> The herds begin moving south again as the short rains arrive</li>
                    </ul>
                    <p>River crossings are unpredictable and depend on water levels and herd movements &mdash; no two crossings are the same.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Are children allowed on safari?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Many of our properties and safaris welcome children, and a well-organised safari can be a truly life-changing experience for young travellers. However, age restrictions vary by property &mdash; some luxury bush camps require guests to be 12 or older for safety reasons. We'll confirm suitability for children when planning your itinerary. We also offer dedicated family safari itineraries designed with younger travellers in mind.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What vehicles do you use for game drives?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>We use purpose-built 4&times;4 safari vehicles &mdash; typically Toyota Land Cruisers or Land Rover Defenders &mdash; with pop-up roof hatches for unobstructed 360-degree wildlife viewing. Vehicles seat 6&ndash;7 guests and carry water, snacks, first aid equipment, and communication devices. For private tours, you will have the vehicle exclusively to yourself and your group.</p>
                </div>
            </div>
        </div>

        <!-- ACCESSIBILITY -->
        <div class="faq-section fade-in" id="accessibility">
            <div class="faq-section-header">
                <div class="faq-section-icon"><i class="fas fa-wheelchair"></i></div>
                <h2>Accessibility</h2>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Do you cater for travellers with disabilities or mobility requirements?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Yes. We are committed to making our trips as accessible as possible. When booking, please use the disability/mobility checkbox on our tour booking form and describe your specific requirements. Our team will contact you to discuss:</p>
                    <ul>
                        <li>Wheelchair-accessible vehicles and lodges</li>
                        <li>Modified activity options and alternative arrangements</li>
                        <li>Medical equipment transport and storage</li>
                        <li>Proximity to medical facilities for itinerary planning</li>
                    </ul>
                    <p>The more detail you share with us, the better we can tailor your experience to ensure it is safe, comfortable, and fulfilling.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Are there tours suitable for elderly travellers?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Absolutely. Many of our tours are well-suited to older travellers, particularly our <strong>lodge-based safari packages</strong> and <strong>beach escapes</strong> which involve minimal walking and physical exertion. Game drives are conducted from comfortable vehicles, and luxury lodges provide exceptional personal service. We can tailor any itinerary to ensure appropriate pacing, rest periods, and medical facility access.</p>
                </div>
            </div>
        </div>

        <!-- CANCELLATIONS & REFUNDS -->
        <div class="faq-section fade-in" id="cancellation">
            <div class="faq-section-header">
                <div class="faq-section-icon"><i class="fas fa-undo"></i></div>
                <h2>Cancellations &amp; Refunds</h2>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What is your cancellation policy?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Our standard cancellation policy is as follows:</p>
                    <ul>
                        <li><strong>90+ days before departure:</strong> Deposit forfeited; balance refunded in full</li>
                        <li><strong>60&ndash;89 days before departure:</strong> 50% of total tour cost charged</li>
                        <li><strong>30&ndash;59 days before departure:</strong> 75% of total tour cost charged</li>
                        <li><strong>Under 30 days:</strong> No refund applicable</li>
                    </ul>
                    <p>We strongly recommend comprehensive travel insurance that includes trip cancellation cover. For cancellations due to serious illness or other exceptional circumstances, we will do our best to work with you on a fair resolution.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">Can I reschedule my booking to different dates?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>Date changes are accommodated where possible, subject to availability and any supplier change fees. Changes requested more than 90 days before departure can usually be made at no charge. Changes within 60 days may incur fees depending on the accommodation and operator. Contact your booking consultant as early as possible if you need to adjust your dates.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" onclick="toggleFAQ(this)">What happens if Bamba Adventures cancels my tour?<i class="fas fa-chevron-down faq-chevron"></i></button>
                <div class="faq-answer">
                    <p>In the rare event that we cancel a tour due to circumstances within our control, you will receive a <strong>full refund</strong> or the option to reschedule to alternative dates at no extra cost. If cancellation is due to force majeure (natural disaster, government travel ban, etc.), we will work with you to find the best possible resolution including credit towards future travel, alternative dates, or a partial refund where supplier terms allow.</p>
                </div>
            </div>
        </div>

        <!-- Still have questions -->
        <div class="still-questions fade-in">
            <h3>Still Have Questions?</h3>
            <p>Our travel specialists are available Monday to Saturday, 8am to 6pm EAT, and are always happy to help you plan the perfect trip.</p>
            <div class="contact-options">
                <a href="/book" class="contact-btn gold"><i class="fas fa-calendar-check"></i> Start Planning</a>
                <a href="https://wa.me/254706606606?text=Hello%20Bamba%20Adventures!%20I%20have%20a%20question." class="contact-btn outline" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i> WhatsApp Us</a>
                <a href="mailto:info@bambaadventures.co.ke" class="contact-btn outline"><i class="fas fa-envelope"></i> Email Us</a>
            </div>
        </div>

    </main>
</div>

<script>
function toggleFAQ(btn) {
    var item = btn.closest('.faq-item');
    var isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item.open').forEach(function(el){ el.classList.remove('open'); });
    if (!isOpen) item.classList.add('open');
}

function filterFAQ(query) {
    var q = query.toLowerCase().trim();
    document.querySelectorAll('.faq-item').forEach(function(item){
        var text = item.textContent.toLowerCase();
        item.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
    document.querySelectorAll('.faq-section').forEach(function(sec){
        var visible = Array.from(sec.querySelectorAll('.faq-item')).some(function(i){ return i.style.display !== 'none'; });
        sec.style.display = visible ? '' : 'none';
    });
}

var sections = document.querySelectorAll('.faq-section');
var catLinks = document.querySelectorAll('.cat-link');
window.addEventListener('scroll', function(){
    var scrollY = window.scrollY + 120;
    sections.forEach(function(sec){
        if (scrollY >= sec.offsetTop && scrollY < sec.offsetTop + sec.offsetHeight) {
            catLinks.forEach(function(l){ l.classList.remove('active'); });
            var active = document.querySelector('.cat-link[href="#' + sec.id + '"]');
            if (active) active.classList.add('active');
        }
    });
});

var observer = new IntersectionObserver(function(entries){ entries.forEach(function(e){ if(e.isIntersecting) e.target.classList.add('visible'); }); }, {threshold:0.1});
document.querySelectorAll('.fade-in').forEach(function(el){ observer.observe(el); });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
