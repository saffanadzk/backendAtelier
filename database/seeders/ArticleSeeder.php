<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $editor = User::where('role', 'editor')->first();
        $contributor = User::where('role', 'contributor')->first();

        $catArchitecture = Category::where('slug', 'architecture-spaces')->first();
        $catInterior = Category::where('slug', 'interior-living')->first();
        $catArt = Category::where('slug', 'art-culture')->first();
        $catProduct = Category::where('slug', 'product-object-design')->first();
        $catVisual = Category::where('slug', 'visual-creative')->first();

        $articles = [
            [
                'title' => 'The Tectonic Silence: Monolithic Concrete and Coastal Topography',
                'slug' => 'the-tectonic-silence-monolithic-concrete-coastal-topography',
                'category_id' => $catArchitecture->id,
                'author_id' => $editor->id,
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'image_filename' => 'monolithic-concrete.jpg',
                'image_source' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Monolithic board-formed concrete pavilion perched against a coastal cliff edge at dusk',
                'excerpt' => 'An inquiry into how brutalist material honesty converges with coastal atmospheric light, forming quiet shelters that anchor humanity to the horizon.',
                'content' => "## The Poetry of Heaviness\n\nArchitecture begins where weight meets gravity with deliberate intention. Situated along a windswept coastline, this residence reinterprets the vernacular of coastal watchtowers through board-formed concrete and weathered Douglas fir.\n\n> \"To build in raw terrain is not to conquer the topography, but to create a frame through which its elemental forces can be observed with reverence.\"\n\n### Material Sincerity\n\nThe exterior envelope utilizes an aggregate blend infused with local volcanic sand, allowing the facade to shift in tone from pale chalk at dawn to deep charcoal when swept by sea mist. The thermal mass of the concrete maintains internal equilibrium, sheltering the living quarters from gale-force oceanic winds while framing curated vistas through recessed floor-to-ceiling glazing.\n\nInternally, joinery crafted from reclaimed cedar brings olfactory warmth, contrasting the cool permanence of the poured surfaces. The tactile resonance between smooth hand-troweled screed and textured board impressions creates an atmosphere of contemplation and slow living.",
                'seo_title' => 'The Tectonic Silence: Monolithic Concrete & Coastal Architecture | ATELIER',
                'seo_description' => 'Explore how raw concrete materiality and coastal light converge in this contemporary architectural study of monolithic spatial design.',
            ],
            [
                'title' => 'Tactile Sanctuary: Earth Pigments and the Art of Quiet Dwelling',
                'slug' => 'tactile-sanctuary-earth-pigments-quiet-dwelling',
                'category_id' => $catInterior->id,
                'author_id' => $contributor->id,
                'status' => 'published',
                'published_at' => now()->subDays(4),
                'image_filename' => 'tactile-sanctuary.jpg',
                'image_source' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Warm minimalist living space featuring textured lime plaster walls, low linen seating, and filtered sunlight',
                'excerpt' => 'Inside an urban refuge where natural lime plasters, unlacquered bronze, and woven linens cultivate an acoustic and visual sanctuary.',
                'content' => "## Restraint as Luxury\n\nIn an age inundated by relentless visual stimulation, modern dwelling seeks respite in restraint. This interior study examines an apartment renovation where color palettes are derived entirely from organic minerals, chalk plasters, and unbleached Belgian linens.\n\n### Spatial Hierarchy and Light Modulation\n\nInstead of open-plan expanses, the layout is partitioned by sliding shoji-inspired screens framed in quarter-sawn white oak. These partitions filter direct western exposure into a luminous, diffused glow, softening shadows and highlighting the nuanced hand-troweled texture of the lime wash.\n\n- **Lime-based Wall Finishes:** Formulated without synthetic binders, breathing naturally with ambient humidity.\n- **Unlacquered Hardware:** Patinating organically through daily touch, embedding time into the physical fabric of the home.\n- **Low Seating Formations:** Grounding the inhabitant’s perspective, invoking a traditional Japanese sense of proximity to the floor.",
                'seo_title' => 'Tactile Sanctuary: Earth Pigments & Minimalist Interior Living | ATELIER',
                'seo_description' => 'A curated look at mineral plasters, bespoke joinery, and warm minimalism shaping acoustic and spatial serenity in contemporary homes.',
            ],
            [
                'title' => 'Form as Memory: Sculptural Ceramics and Spatial Dialogue',
                'slug' => 'form-as-memory-sculptural-ceramics-spatial-dialogue',
                'category_id' => $catArt->id,
                'author_id' => $admin->id,
                'status' => 'published',
                'published_at' => now()->subDays(6),
                'image_filename' => 'sculptural-ceramics.jpg',
                'image_source' => 'https://images.unsplash.com/photo-1579783902614-a3fb3927b675?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Artisanal sculptural stoneware vessels displayed on a rough stone plinth in a gallery setting',
                'excerpt' => 'Exploring the tension between functional pottery and pure spatial sculpture through the works of contemporary ceramic masters.',
                'content' => "## The Vessel Beyond Purpose\n\nWhen a ceramic vessel sheds its utilitarian imperative, it becomes an instrument of spatial cadence. The recent exhibition at Atelier Galerie interrogates how negative space surrounds and occupies hand-coiled stoneware.\n\n### Wood-fired Alchemy\n\nFired in an anagama kiln for seven continuous days, the pieces bear the violent, unpredictable marks of flying ash, flame paths, and localized reduction. These natural scorch patterns remind the observer of geological time, bridging the boundary between human craft and tectonic circumstance.\n\nEach curve captures a breath of the maker, establishing an immediate visceral intimacy that machine-pressed porcelain simply cannot replicate.",
                'seo_title' => 'Form as Memory: Sculptural Ceramics & Spatial Dialogue | ATELIER',
                'seo_description' => 'Delving into contemporary wood-fired ceramics, tactile stoneware, and sculptural interventions in modern architectural spaces.',
            ],
            [
                'title' => 'The Honest Joint: Precision Joinery and Timber Longevity',
                'slug' => 'the-honest-joint-precision-joinery-timber-longevity',
                'category_id' => $catProduct->id,
                'author_id' => $editor->id,
                'status' => 'published',
                'published_at' => now()->subDays(8),
                'image_filename' => 'timber-joinery.jpg',
                'image_source' => 'https://images.unsplash.com/photo-1540518614846-7ede433c4ef4?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Exposed interlocking mortise and tenon wood joinery on an artisanal solid walnut dining table',
                'excerpt' => 'A technical yet lyrical exploration into interlocking wooden joints that eliminate screws and celebrate tensile craftsmanship.',
                'content' => "## Structural Honesty\n\nTrue furniture design does not conceal its mechanics. In the workshops of Scandinavia and Japan, traditional interlocking joints—such as the *kanawa tsugi* and pinned bridle—remain the zenith of structural resilience.\n\n### Designing for Lifetimes\n\nUnlike mechanical fasteners that loosen under moisture expansion, precision-cut wood joints expand and contract symbiotically with the seasons. By respecting grain direction and fiber tension, objects built this way outlive their creators, passing through generations with an enduring patina of dignity.",
                'seo_title' => 'The Honest Joint: Precision Joinery & Object Craft | ATELIER',
                'seo_description' => 'Analyzing the engineering, craftsmanship, and aesthetic integrity of traditional timber joinery in modern product design.',
            ],
            [
                'title' => 'Shadows of the Solitary Pavilion: Light in the High Alps',
                'slug' => 'shadows-solitary-pavilion-light-high-alps',
                'category_id' => $catVisual->id,
                'author_id' => $contributor->id,
                'status' => 'published',
                'published_at' => now()->subDays(10),
                'image_filename' => 'alpine-pavilion.jpg',
                'image_source' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'A solitary timber mountain pavilion framed by dramatic mountain peaks and cascading mist',
                'excerpt' => 'A visual essay capturing alpine light gradients and how architectural thresholds mediate between freezing wilderness and domestic warmth.',
                'content' => "## The High Altitude Horizon\n\nAt 2,400 meters above sea level, sunlight carries an acute, razor-sharp clarity. Photographed over the course of three winter solstices, this pavilion study documents the choreography of alpine shadows across charred larch cladding.\n\n### The Window as Vignette\n\nDeep window reveals create transitional thresholds where snow glare is softened before entering the intimate timber interior. Here, the photographic lens documents not merely walls and beams, but the emotional climate of solitude.",
                'seo_title' => 'Shadows of the Solitary Pavilion: Alpine Light Study | ATELIER',
                'seo_description' => 'A photographic essay exploring high-altitude architectural enclosures and seasonal light transitions in the Swiss Alps.',
            ],
            // PENDING ARTICLE FOR EDITORIAL WORKFLOW TESTING
            [
                'title' => 'Raw Earth and Rammed Terroir: Regenerative Architecture in the Valley',
                'slug' => 'raw-earth-rammed-terroir-regenerative-architecture',
                'category_id' => $catArchitecture->id,
                'author_id' => $contributor->id,
                'status' => 'pending',
                'published_at' => null,
                'image_filename' => 'rammed-earth.jpg',
                'image_source' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Rammed earth layered wall with textured strata under bright morning light',
                'excerpt' => 'Utilizing excavated subsoil to compress walls that store solar warmth and dissolve back into the earth after centuries.',
                'content' => "## Earth as Substance\n\nRammed earth construction synthesizes the ancient with the modern. By blending local clays, gravels, and minimal stabilizers, walls achieve extraordinary compressive strength alongside exceptional thermal capacitance.\n\nThis manuscript details a new community winery built entirely from the earth excavated from its own root cellars.",
                'seo_title' => 'Raw Earth & Rammed Terroir: Regenerative Architecture | ATELIER',
                'seo_description' => 'Investigating rammed earth building techniques, soil stratigraphy, and low-embodied-energy construction.',
            ],
            // DRAFT ARTICLE FOR CONTRIBUTOR TESTING
            [
                'title' => 'Material Notes: The Tactility of Honed Basalt and Cast Glass',
                'slug' => 'material-notes-tactility-honed-basalt-cast-glass',
                'category_id' => $catProduct->id,
                'author_id' => $contributor->id,
                'status' => 'draft',
                'published_at' => null,
                'image_filename' => 'basalt-glass.jpg',
                'image_source' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Experimental pairing of dark honed basalt stone and thick translucent cast glass',
                'excerpt' => 'Initial notes and studio experiments testing the optical refraction of thick poured glass blocks anchored into volcanic stone.',
                'content' => "## Studio Explorations\n\nWorking draft regarding experimental furniture pieces combining dark volcanic basalt bases with kiln-cast optic glass surfaces...",
                'seo_title' => 'Material Notes: Honed Basalt and Cast Glass | ATELIER',
                'seo_description' => 'Draft observations on stone and glass materiality in contemporary furniture design.',
            ],
            // REJECTED ARTICLE FOR EDITORIAL FEEDBACK TESTING
            [
                'title' => 'Commercial Trends in Residential Real Estate Interiors 2026',
                'slug' => 'commercial-trends-residential-interiors-2026',
                'category_id' => $catInterior->id,
                'author_id' => $contributor->id,
                'status' => 'rejected',
                'rejection_note' => 'The article reads too much like a generic sales trend list rather than our editorial journal focus on spatial philosophy, craftsmanship, and architectural materiality. Please rewrite focusing on specific artisanal techniques or spatial case studies.',
                'published_at' => null,
                'image_filename' => 'commercial-interior.jpg',
                'image_source' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1600&q=80',
                'image_alt' => 'Modern staged apartment interior',
                'excerpt' => 'A review of popular commercial interior design trends sweeping luxury developments this year.',
                'content' => "This piece discusses color trends and popular furniture models currently trending on social media...",
                'seo_title' => 'Commercial Trends in Residential Interiors 2026 | ATELIER',
                'seo_description' => 'Overview of trending interior designs.',
            ],
        ];

        $storagePath = storage_path('app/public/articles');
        if (!File::isDirectory($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        foreach ($articles as $art) {
            $localImagePath = 'articles/' . $art['image_filename'];
            $fullLocalPath = storage_path('app/public/' . $localImagePath);

            if (!File::exists($fullLocalPath)) {
                // Try downloading high-res curated image, or create an SVG fallback
                try {
                    $response = Http::timeout(6)->get($art['image_source']);
                    if ($response->successful()) {
                        File::put($fullLocalPath, $response->body());
                    } else {
                        $this->createFallbackImage($fullLocalPath, $art['title']);
                    }
                } catch (\Throwable $e) {
                    $this->createFallbackImage($fullLocalPath, $art['title']);
                }
            }

            Article::updateOrCreate(
                ['slug' => $art['slug']],
                [
                    'title' => $art['title'],
                    'category_id' => $art['category_id'],
                    'author_id' => $art['author_id'],
                    'status' => $art['status'],
                    'published_at' => $art['published_at'],
                    'image' => $localImagePath,
                    'image_alt' => $art['image_alt'],
                    'excerpt' => $art['excerpt'],
                    'content' => $art['content'],
                    'rejection_note' => $art['rejection_note'] ?? null,
                    'seo_title' => $art['seo_title'],
                    'seo_description' => $art['seo_description'],
                ]
            );
        }
    }

    private function createFallbackImage(string $path, string $title): void
    {
        // Simple geometric SVG with neutral warm tone for architectural aesthetic
        $shortTitle = htmlspecialchars(Str::limit($title, 40));
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1066" viewBox="0 0 1600 1066">
    <rect width="1600" height="1066" fill="#EAE7DF"/>
    <rect x="120" y="120" width="1360" height="826" fill="#DFD9CE" stroke="#CCC6BA" stroke-width="2"/>
    <line x1="800" y1="120" x2="800" y2="946" stroke="#CCC6BA" stroke-width="1.5" stroke-dasharray="8 8"/>
    <text x="800" y="520" font-family="Georgia, serif" font-size="36" fill="#2C2A29" text-anchor="middle" letter-spacing="2">ATELIER JOURNAL</text>
    <text x="800" y="580" font-family="'Helvetica Neue', sans-serif" font-size="20" fill="#706B65" text-anchor="middle" letter-spacing="1">{$shortTitle}</text>
</svg>
SVG;
        File::put($path, $svg);
    }
}
