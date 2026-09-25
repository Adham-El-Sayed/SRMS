<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Services\MenuService;
use App\Support\DishArt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * A full menu for an Italian restaurant of the kind you find in Alexandria:
 * ten courses, seventy dishes, prices in Egyptian pounds.
 *
 * Names carry both languages because the menu is stored once and read in
 * either, the way Egyptian menus are printed. Descriptions are in Arabic,
 * where most of this restaurant's guests will read them.
 *
 * Running it twice changes nothing: every dish is matched on its name. What
 * the menu had before is switched off rather than deleted, so nothing that
 * was already ordered loses the record of what it was.
 */
class ItalianMenuSeeder extends Seeder
{
    public function run(): void
    {
        // The menu is ordered by a column a migration adds; without it this
        // would fail somewhere unreadable instead of saying what is wrong.
        foreach (['categories', 'products'] as $table) {
            if (! Schema::hasColumn($table, 'sort_order')) {
                $this->command?->error('The database is behind: ' . $table . '.sort_order is missing.');
                $this->command?->warn('Run this first:  php artisan migrate');
                return;
            }
        }

        $this->retireTheOldMenu();

        foreach ($this->menu() as $order => $course) {
            $category = Category::updateOrCreate(
                ['name' => $course['name']],
                [
                    'description' => $course['note'],
                    'sort_order' => $order + 1,
                    'is_active' => true,
                    'image' => DishArt::make($course['art'], $course['name'], 'menu/categories'),
                ]
            );

            foreach ($course['dishes'] as $place => $dish) {
                Product::updateOrCreate(
                    ['name' => $dish[0]],
                    [
                        'category_id' => $category->id,
                        'description' => $dish[1],
                        'price' => $dish[2],
                        'sort_order' => $place + 1,
                        'is_active' => true,
                        'sold_out_at' => null,
                        'image' => DishArt::make($dish[3] ?? $course['art'], $dish[0]),
                    ]
                );
            }

            $this->command?->info('  ' . $course['name'] . ' — ' . count($course['dishes']) . ' dishes');
        }

        MenuService::forget();

        $this->command?->info('Menu ready: ' . Category::where('is_active', true)->count()
            . ' courses, ' . Product::where('is_active', true)->count() . ' dishes.');
    }

    /**
     * Whatever the menu held before this runs is switched off, not removed:
     * an old order still names the dish it was, and staff can bring any of
     * it back from Menu Management.
     */
    private function retireTheOldMenu(): void
    {
        $keep = collect($this->menu())->pluck('name');

        // Every course that is not part of this menu, whether or not it was
        // already switched off — a second run still tidies it away.
        $retired = Category::whereNotIn('name', $keep)->get();

        foreach ($retired as $category) {
            // Sent to the bottom of Menu Management as well as switched off,
            // so what is actually being served is what staff see first.
            $category->update(['is_active' => false, 'sort_order' => 900]);
            Product::where('category_id', $category->id)->update(['is_active' => false]);
        }

        if ($retired->isNotEmpty()) {
            $this->command?->warn('Switched off: ' . $retired->pluck('name')->implode(', '));
        }

    }

    /** name · note · art family · [dish name, description, price, art?] */
    private function menu(): array
    {
        return [
            [
                'name' => 'المقبلات · Starters',
                'note' => 'حاجة خفيفة قبل الطبق الأساسي · Something light before the main course',
                'art' => 'starter',
                'dishes' => [
                    ['خبز بالثوم · Garlic Bread', 'عيش إيطالي طازة بالثوم والزبدة والبقدونس · Fresh Italian bread with garlic, butter and parsley', 65],
                    ['خبز بالثوم والموتزاريلا · Garlic Bread with Mozzarella', 'نفس الوصفة وفوقها موتزاريلا سايحة · The same, under melted mozzarella', 95],
                    ['بروشيتا · Bruschetta', 'طماطم وريحان وزيت زيتون على خبز محمص · Tomato, basil and olive oil on toasted bread', 110],
                    ['أصابع موتزاريلا · Mozzarella Sticks', 'ستة أصابع مقرمشة مع صلصة مارينارا · Six crisp sticks with marinara sauce', 135],
                    ['أرانشيني · Arancini', 'كرات رز محشية موتزاريلا ومقلية · Fried rice balls stuffed with mozzarella', 125],
                    ['بطاطس ودجز · Potato Wedges', 'بطاطس بقشرها بالأعشاب والبابريكا · Skin-on potatoes with herbs and paprika', 80],
                    ['أجنحة بافلو · Buffalo Wings', 'ثمن قطع بصلصة حارة وصوص بلو تشيز · Eight pieces in hot sauce with blue cheese dip', 145, 'chicken'],
                    ['كالاماري مقلي · Fried Calamari', 'حلقات كالاماري مقرمشة مع ليمون وطرطور · Crisp calamari rings with lemon and tartare', 185, 'seafood'],
                ],
            ],
            [
                'name' => 'الشوربة · Soups',
                'note' => 'شوربة سخنة تتقدم مع خبز الثوم · Served hot with garlic bread',
                'art' => 'soup',
                'dishes' => [
                    ['شوربة طماطم بالريحان · Tomato Basil Soup', 'طماطم مشوية وريحان طازة وكريمة خفيفة · Roasted tomato, fresh basil and a little cream', 75],
                    ['شوربة فطر بالكريمة · Cream of Mushroom', 'فطر طازة وكريمة وقليل من الزعتر · Fresh mushrooms, cream and a little thyme', 85],
                    ['مينيسترون · Minestrone', 'خضار وفاصوليا ومكرونة صغيرة في مرقة طماطم · Vegetables, beans and small pasta in tomato broth', 90],
                    ['شوربة عدس · Lentil Soup', 'عدس أصفر بالكمون والليمون · Yellow lentils with cumin and lemon', 65],
                    ['شوربة سي فود · Seafood Soup', 'جمبري وكالاماري وسمك في مرقة طماطم · Shrimp, calamari and fish in tomato broth', 145, 'seafood'],
                ],
            ],
            [
                'name' => 'السلطات · Salads',
                'note' => 'خضار طازة تتجهز وقت الطلب · Fresh, tossed to order',
                'art' => 'salad',
                'dishes' => [
                    ['سلطة سيزر · Caesar Salad', 'خس رومين وبارميزان وكروتون وصوص سيزر · Romaine, parmesan, croutons and Caesar dressing', 120],
                    ['سيزر بالفراخ · Chicken Caesar', 'نفس السلطة وفوقها صدور فراخ مشوية · The same, topped with grilled chicken breast', 165],
                    ['سلطة كابريزي · Caprese', 'موتزاريلا وطماطم وريحان وزيت زيتون بكر · Mozzarella, tomato, basil and extra virgin olive oil', 150],
                    ['سلطة يونانية · Greek Salad', 'خيار وطماطم وزيتون وجبنة فيتا · Cucumber, tomato, olives and feta', 130],
                    ['سلطة روكا بالبارميزان · Rocket & Parmesan', 'جرجير وشرائح بارميزان ودريسنج بلسمك · Rocket, shaved parmesan and balsamic dressing', 115],
                    ['سلطة تونة · Tuna Salad', 'تونة وخضار مشكلة وبيض مسلوق · Tuna, mixed leaves and boiled egg', 155],
                    ['كول سلو · Coleslaw', 'كرنب وجزر بصوص كريمي · Cabbage and carrot in a creamy dressing', 55],
                ],
            ],
            [
                'name' => 'البيتزا · Pizza',
                'note' => 'عجينة تترد كل يوم وتتخبز في فرن حجري · Dough made daily, baked in a stone oven',
                'art' => 'pizza',
                'dishes' => [
                    ['بيتزا مارجريتا · Margherita', 'صلصة طماطم، موتزاريلا، ريحان طازة · Tomato sauce, mozzarella, fresh basil', 165],
                    ['بيتزا خضار · Vegetarian', 'فلفل ألوان وفطر وزيتون وبصل وذرة · Peppers, mushrooms, olives, onion and corn', 185],
                    ['بيتزا بيبروني · Pepperoni', 'شرائح بيبروني وموتزاريلا وأوريجانو · Pepperoni, mozzarella and oregano', 210],
                    ['بيتزا سجق إيطالي · Italian Sausage', 'سجق إيطالي وفلفل رومي وبصل أحمر · Italian sausage, green pepper and red onion', 215],
                    ['بيتزا فراخ باربكيو · BBQ Chicken', 'فراخ مدخنة وصلصة باربكيو وبصل أحمر · Smoked chicken, BBQ sauce and red onion', 225],
                    ['بيتزا فراخ رانش · Chicken Ranch', 'فراخ وصوص رانش وفطر وموتزاريلا · Chicken, ranch sauce, mushrooms and mozzarella', 230],
                    ['بيتزا فور فورماجي · Four Cheese', 'موتزاريلا وبارميزان وجودة وبلو تشيز · Mozzarella, parmesan, gouda and blue cheese', 235],
                    ['كالزوني فراخ · Chicken Calzone', 'عجينة مطوية محشية فراخ وجبنة وفطر · Folded dough filled with chicken, cheese and mushrooms', 220],
                    ['بيتزا سي فود · Seafood', 'جمبري وكالاماري وبلح البحر مع ثوم وليمون · Shrimp, calamari and mussels with garlic and lemon', 275],
                    ['بيتزا الشيف · Chef\'s Special', 'لحمة وفراخ وسجق وفطر وفلفل، بحجم عيلة · Beef, chicken, sausage, mushrooms and peppers — family size', 295],
                ],
            ],
            [
                'name' => 'الباستا · Pasta',
                'note' => 'مكرونة تتسلق وقت الطلب وتتقدم مع خبز الثوم · Cooked to order, served with garlic bread',
                'art' => 'pasta',
                'dishes' => [
                    ['سباجيتي بالثوم والزيت · Aglio e Olio', 'ثوم وزيت زيتون وشطة وبقدونس · Garlic, olive oil, chilli and parsley', 150],
                    ['بيني أرابياتا · Penne Arrabbiata', 'صلصة طماطم حارة بالثوم والريحان · Spicy tomato sauce with garlic and basil', 165],
                    ['مكرونة بشاميل · Macaroni Béchamel', 'بشاميل وجبنة ولحمة مفرومة · Béchamel, cheese and minced beef', 175],
                    ['باستا بيستو · Pesto Pasta', 'صلصة ريحان وصنوبر وبارميزان · Basil, pine nuts and parmesan', 190],
                    ['سباجيتي بولونيز · Spaghetti Bolognese', 'لحمة مفرومة في صلصة طماطم على مهلها · Minced beef in a slow tomato sauce', 195],
                    ['بيني روزيه · Penne Rosé', 'طماطم وكريمة مع بارميزان · Tomato and cream with parmesan', 200],
                    ['فيتوتشيني ألفريدو · Fettuccine Alfredo', 'كريمة وزبدة وبارميزان · Cream, butter and parmesan', 205],
                    ['مكرونة بالفراخ والفطر · Chicken & Mushroom Pasta', 'فراخ مشوية وفطر في صلصة كريمي · Grilled chicken and mushrooms in a cream sauce', 215],
                    ['لازانيا باللحمة · Beef Lasagna', 'طبقات مكرونة ولحمة وبشاميل وجبنة · Layers of pasta, beef, béchamel and cheese', 235],
                    ['سي فود باستا · Seafood Pasta', 'جمبري وكالاماري بالثوم والطماطم · Shrimp and calamari with garlic and tomato', 265, 'seafood'],
                ],
            ],
            [
                'name' => 'الريزوتو · Risotto',
                'note' => 'رز أربوريو يتقلب على نار هادية · Arborio rice, stirred slowly',
                'art' => 'pasta',
                'dishes' => [
                    ['ريزوتو فطر · Mushroom Risotto', 'فطر طازة وبارميزان وزبدة · Fresh mushrooms, parmesan and butter', 230],
                    ['ريزوتو فراخ · Chicken Risotto', 'فراخ مشوية وكريمة وأعشاب · Grilled chicken, cream and herbs', 250],
                    ['ريزوتو سي فود · Seafood Risotto', 'جمبري وكالاماري وزعفران · Shrimp, calamari and saffron', 295, 'seafood'],
                ],
            ],
            [
                'name' => 'البانيني · Panini',
                'note' => 'خبز إيطالي محمص على الجريل، مع بطاطس · Grilled Italian bread, served with fries',
                'art' => 'sandwich',
                'dishes' => [
                    ['بانيني موتزاريلا وطماطم · Mozzarella & Tomato', 'موتزاريلا وطماطم وريحان وزيت زيتون · Mozzarella, tomato, basil and olive oil', 130],
                    ['بانيني خضار مشوي · Grilled Vegetables', 'كوسة وباذنجان وفلفل مشوي وجبنة · Grilled courgette, aubergine, pepper and cheese', 140],
                    ['بانيني تونة · Tuna Panini', 'تونة وزيتون وخس ومايونيز · Tuna, olives, lettuce and mayonnaise', 150],
                    ['بانيني فراخ مشوي · Grilled Chicken', 'فراخ مشوية وجبنة وصوص أعشاب · Grilled chicken, cheese and herb sauce', 165],
                    ['بانيني لحمة · Beef Panini', 'شرائح لحمة وبصل مكرمل وجبنة شيدر · Sliced beef, caramelised onion and cheddar', 185],
                ],
            ],
            [
                'name' => 'الأطباق الرئيسية · Main Courses',
                'note' => 'تتقدم مع طبق جانبي على اختيارك · Served with a side of your choice',
                'art' => 'starter',
                'dishes' => [
                    ['إسكالوب بانيه · Chicken Escalope', 'صدور فراخ مقرمشة مع بطاطس وليمون · Crisp chicken breast with fries and lemon', 255, 'chicken'],
                    ['صدور فراخ مشوية · Grilled Chicken Breast', 'فراخ متتبلة بالأعشاب مع خضار مشوي · Herb-marinated chicken with grilled vegetables', 265, 'chicken'],
                    ['فراخ بارميجيانا · Chicken Parmigiana', 'فراخ بانيه بصلصة طماطم وموتزاريلا · Breaded chicken under tomato sauce and mozzarella', 295, 'chicken'],
                    ['سمك فيليه مشوي · Grilled Fish Fillet', 'فيليه بالثوم والليمون مع أرز · Fillet with garlic and lemon, served with rice', 320, 'seafood'],
                    ['جمبري بالثوم · Garlic Shrimp', 'جمبري في زبدة الثوم مع خبز محمص · Shrimp in garlic butter with toasted bread', 385, 'seafood'],
                    ['مشويات مشكلة · Mixed Grill', 'كفتة وريش وشيش طاووق مع أرز وسلطة · Kofta, lamb chops and shish tawook with rice and salad', 450],
                    ['ستيك بصلصة الفطر · Steak with Mushroom Sauce', 'ستيك مشوي بصلصة الفطر والكريمة · Grilled steak in a mushroom cream sauce', 480],
                    ['ريش ضاني مشوية · Grilled Lamb Chops', 'ريش متتبلة بالروزماري مع بطاطس · Rosemary-marinated chops with potatoes', 520],
                ],
            ],
            [
                'name' => 'الحلو · Desserts',
                'note' => 'كله بيتعمل عندنا في المطبخ · All made in our own kitchen',
                'art' => 'dessert',
                'dishes' => [
                    ['آيس كريم · Ice Cream', 'ثلاث كور على اختيارك · Three scoops of your choice', 85],
                    ['أم علي · Om Ali', 'بالمكسرات والقشطة، تتقدم سخنة · With nuts and cream, served hot', 95],
                    ['كيك الشوكولاتة · Chocolate Cake', 'طبقات كاكاو وصوص شوكولاتة دافي · Cocoa layers with warm chocolate sauce', 110],
                    ['بانا كوتا · Panna Cotta', 'كريمة بالفانيليا وصوص فراولة · Vanilla cream with strawberry sauce', 115],
                    ['تيراميسو · Tiramisu', 'مسكربوني وقهوة وكاكاو · Mascarpone, coffee and cocoa', 120],
                    ['تشيز كيك فراولة · Strawberry Cheesecake', 'قاعدة بسكويت وفراولة طازة · Biscuit base with fresh strawberries', 130],
                ],
            ],
            [
                'name' => 'المشروبات · Drinks',
                'note' => 'العصاير كلها طازة وتتعصر وقت الطلب · Juices squeezed to order',
                'art' => 'drink',
                'dishes' => [
                    ['مياه معدنية · Mineral Water', 'زجاجة ٦٠٠ مل · 600 ml bottle', 20],
                    ['شاي · Tea', 'شاي كشري أو بالنعناع · Plain or with mint', 30],
                    ['مشروبات غازية · Soft Drink', 'كانز مثلج · Chilled can', 35],
                    ['إسبريسو · Espresso', 'سنجل أو دوبل · Single or double', 45],
                    ['ليموناضة · Lemonade', 'ليمون طازة مثلج · Fresh lemon, served iced', 50],
                    ['عصير ليمون بالنعناع · Lemon & Mint', 'مفروم مع تلج · Blended with ice', 60],
                    ['عصير برتقال · Fresh Orange Juice', 'برتقال طازة متعصر دلوقتي · Oranges squeezed to order', 65],
                    ['كابتشينو · Cappuccino', 'إسبريسو ولبن مخفوق · Espresso with steamed, foamed milk', 65],
                    ['لاتيه · Latte', 'إسبريسو مع لبن ساخن · Espresso with hot milk', 70],
                    ['عصير مانجو · Mango Juice', 'مانجو طازة في موسمها · Fresh mango, in season', 75],
                    ['موهيتو · Mojito', 'ليمون ونعناع وصودا · Lime, mint and soda', 85],
                ],
            ],
        ];
    }
}
