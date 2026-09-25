<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Services\MenuService;
use App\Support\DishArt;
use Illuminate\Database\Seeder;

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
                'note' => 'حاجة خفيفة قبل الطبق الأساسي',
                'art' => 'starter',
                'dishes' => [
                    ['خبز بالثوم · Garlic Bread', 'عيش إيطالي طازة بالثوم والزبدة والبقدونس', 65],
                    ['خبز بالثوم والموتزاريلا · Garlic Bread with Mozzarella', 'نفس الوصفة وفوقها موتزاريلا سايحة', 95],
                    ['بروشيتا · Bruschetta', 'طماطم وريحان وزيت زيتون على خبز محمص', 110],
                    ['أصابع موتزاريلا · Mozzarella Sticks', 'ستة أصابع مقرمشة مع صلصة مارينارا', 135],
                    ['أرانشيني · Arancini', 'كرات رز محشية موتزاريلا ومقلية', 125],
                    ['بطاطس ودجز · Potato Wedges', 'بطاطس بقشرها بالأعشاب والبابريكا', 80],
                    ['أجنحة بافلو · Buffalo Wings', 'ثمن قطع بصلصة حارة وصوص بلو تشيز', 145, 'chicken'],
                    ['كالاماري مقلي · Fried Calamari', 'حلقات كالاماري مقرمشة مع ليمون وطرطور', 185, 'seafood'],
                ],
            ],
            [
                'name' => 'الشوربة · Soups',
                'note' => 'شوربة سخنة تتقدم مع خبز الثوم',
                'art' => 'soup',
                'dishes' => [
                    ['شوربة طماطم بالريحان · Tomato Basil Soup', 'طماطم مشوية وريحان طازة وكريمة خفيفة', 75],
                    ['شوربة فطر بالكريمة · Cream of Mushroom', 'فطر طازة وكريمة وقليل من الزعتر', 85],
                    ['مينيسترون · Minestrone', 'خضار وفاصوليا ومكرونة صغيرة في مرقة طماطم', 90],
                    ['شوربة عدس · Lentil Soup', 'عدس أصفر بالكمون والليمون', 65],
                    ['شوربة سي فود · Seafood Soup', 'جمبري وكالاماري وسمك في مرقة طماطم', 145, 'seafood'],
                ],
            ],
            [
                'name' => 'السلطات · Salads',
                'note' => 'خضار طازة تتجهز وقت الطلب',
                'art' => 'salad',
                'dishes' => [
                    ['سلطة سيزر · Caesar Salad', 'خس رومين وبارميزان وكروتون وصوص سيزر', 120],
                    ['سيزر بالفراخ · Chicken Caesar', 'نفس السلطة وفوقها صدور فراخ مشوية', 165],
                    ['سلطة كابريزي · Caprese', 'موتزاريلا وطماطم وريحان وزيت زيتون بكر', 150],
                    ['سلطة يونانية · Greek Salad', 'خيار وطماطم وزيتون وجبنة فيتا', 130],
                    ['سلطة روكا بالبارميزان · Rocket & Parmesan', 'جرجير وشرائح بارميزان ودريسنج بلسمك', 115],
                    ['سلطة تونة · Tuna Salad', 'تونة وخضار مشكلة وبيض مسلوق', 155],
                    ['كول سلو · Coleslaw', 'كرنب وجزر بصوص كريمي', 55],
                ],
            ],
            [
                'name' => 'البيتزا · Pizza',
                'note' => 'عجينة تترد كل يوم وتتخبز في فرن حجري',
                'art' => 'pizza',
                'dishes' => [
                    ['بيتزا مارجريتا · Margherita', 'صلصة طماطم، موتزاريلا، ريحان طازة', 165],
                    ['بيتزا خضار · Vegetarian', 'فلفل ألوان وفطر وزيتون وبصل وذرة', 185],
                    ['بيتزا بيبروني · Pepperoni', 'شرائح بيبروني وموتزاريلا وأوريجانو', 210],
                    ['بيتزا سجق إيطالي · Italian Sausage', 'سجق إيطالي وفلفل رومي وبصل أحمر', 215],
                    ['بيتزا فراخ باربكيو · BBQ Chicken', 'فراخ مدخنة وصلصة باربكيو وبصل أحمر', 225],
                    ['بيتزا فراخ رانش · Chicken Ranch', 'فراخ وصوص رانش وفطر وموتزاريلا', 230],
                    ['بيتزا فور فورماجي · Four Cheese', 'موتزاريلا وبارميزان وجودة وبلو تشيز', 235],
                    ['كالزوني فراخ · Chicken Calzone', 'عجينة مطوية محشية فراخ وجبنة وفطر', 220],
                    ['بيتزا سي فود · Seafood', 'جمبري وكالاماري وبلح البحر مع ثوم وليمون', 275],
                    ['بيتزا الشيف · Chef\'s Special', 'لحمة وفراخ وسجق وفطر وفلفل، بحجم عيلة', 295],
                ],
            ],
            [
                'name' => 'الباستا · Pasta',
                'note' => 'مكرونة تتسلق وقت الطلب وتتقدم مع خبز الثوم',
                'art' => 'pasta',
                'dishes' => [
                    ['سباجيتي بالثوم والزيت · Aglio e Olio', 'ثوم وزيت زيتون وشطة وبقدونس', 150],
                    ['بيني أرابياتا · Penne Arrabbiata', 'صلصة طماطم حارة بالثوم والريحان', 165],
                    ['مكرونة بشاميل · Macaroni Béchamel', 'بشاميل وجبنة ولحمة مفرومة', 175],
                    ['باستا بيستو · Pesto Pasta', 'صلصة ريحان وصنوبر وبارميزان', 190],
                    ['سباجيتي بولونيز · Spaghetti Bolognese', 'لحمة مفرومة في صلصة طماطم على مهلها', 195],
                    ['بيني روزيه · Penne Rosé', 'طماطم وكريمة مع بارميزان', 200],
                    ['فيتوتشيني ألفريدو · Fettuccine Alfredo', 'كريمة وزبدة وبارميزان', 205],
                    ['مكرونة بالفراخ والفطر · Chicken & Mushroom Pasta', 'فراخ مشوية وفطر في صلصة كريمي', 215],
                    ['لازانيا باللحمة · Beef Lasagna', 'طبقات مكرونة ولحمة وبشاميل وجبنة', 235],
                    ['سي فود باستا · Seafood Pasta', 'جمبري وكالاماري بالثوم والطماطم', 265, 'seafood'],
                ],
            ],
            [
                'name' => 'الريزوتو · Risotto',
                'note' => 'رز أربوريو يتقلب على نار هادية',
                'art' => 'pasta',
                'dishes' => [
                    ['ريزوتو فطر · Mushroom Risotto', 'فطر طازة وبارميزان وزبدة', 230],
                    ['ريزوتو فراخ · Chicken Risotto', 'فراخ مشوية وكريمة وأعشاب', 250],
                    ['ريزوتو سي فود · Seafood Risotto', 'جمبري وكالاماري وزعفران', 295, 'seafood'],
                ],
            ],
            [
                'name' => 'البانيني · Panini',
                'note' => 'خبز إيطالي محمص على الجريل، مع بطاطس',
                'art' => 'sandwich',
                'dishes' => [
                    ['بانيني موتزاريلا وطماطم · Mozzarella & Tomato', 'موتزاريلا وطماطم وريحان وزيت زيتون', 130],
                    ['بانيني خضار مشوي · Grilled Vegetables', 'كوسة وباذنجان وفلفل مشوي وجبنة', 140],
                    ['بانيني تونة · Tuna Panini', 'تونة وزيتون وخس ومايونيز', 150],
                    ['بانيني فراخ مشوي · Grilled Chicken', 'فراخ مشوية وجبنة وصوص أعشاب', 165],
                    ['بانيني لحمة · Beef Panini', 'شرائح لحمة وبصل مكرمل وجبنة شيدر', 185],
                ],
            ],
            [
                'name' => 'الأطباق الرئيسية · Main Courses',
                'note' => 'تتقدم مع طبق جانبي على اختيارك',
                'art' => 'starter',
                'dishes' => [
                    ['إسكالوب بانيه · Chicken Escalope', 'صدور فراخ مقرمشة مع بطاطس وليمون', 255, 'chicken'],
                    ['صدور فراخ مشوية · Grilled Chicken Breast', 'فراخ متتبلة بالأعشاب مع خضار مشوي', 265, 'chicken'],
                    ['فراخ بارميجيانا · Chicken Parmigiana', 'فراخ بانيه بصلصة طماطم وموتزاريلا', 295, 'chicken'],
                    ['سمك فيليه مشوي · Grilled Fish Fillet', 'فيليه بالثوم والليمون مع أرز', 320, 'seafood'],
                    ['جمبري بالثوم · Garlic Shrimp', 'جمبري في زبدة الثوم مع خبز محمص', 385, 'seafood'],
                    ['مشويات مشكلة · Mixed Grill', 'كفتة وريش وشيش طاووق مع أرز وسلطة', 450],
                    ['ستيك بصلصة الفطر · Steak with Mushroom Sauce', 'ستيك مشوي بصلصة الفطر والكريمة', 480],
                    ['ريش ضاني مشوية · Grilled Lamb Chops', 'ريش متتبلة بالروزماري مع بطاطس', 520],
                ],
            ],
            [
                'name' => 'الحلو · Desserts',
                'note' => 'كله بيتعمل عندنا في المطبخ',
                'art' => 'dessert',
                'dishes' => [
                    ['آيس كريم · Ice Cream', 'ثلاث كور على اختيارك', 85],
                    ['أم علي · Om Ali', 'بالمكسرات والقشطة، تتقدم سخنة', 95],
                    ['كيك الشوكولاتة · Chocolate Cake', 'طبقات كاكاو وصوص شوكولاتة دافي', 110],
                    ['بانا كوتا · Panna Cotta', 'كريمة بالفانيليا وصوص فراولة', 115],
                    ['تيراميسو · Tiramisu', 'مسكربوني وقهوة وكاكاو', 120],
                    ['تشيز كيك فراولة · Strawberry Cheesecake', 'قاعدة بسكويت وفراولة طازة', 130],
                ],
            ],
            [
                'name' => 'المشروبات · Drinks',
                'note' => 'العصاير كلها طازة وتتعصر وقت الطلب',
                'art' => 'drink',
                'dishes' => [
                    ['مياه معدنية · Mineral Water', 'زجاجة ٦٠٠ مل', 20],
                    ['شاي · Tea', 'شاي كشري أو بالنعناع', 30],
                    ['مشروبات غازية · Soft Drink', 'كانز مثلج', 35],
                    ['إسبريسو · Espresso', 'سنجل أو دوبل', 45],
                    ['ليموناضة · Lemonade', 'ليمون طازة مثلج', 50],
                    ['عصير ليمون بالنعناع · Lemon & Mint', 'مفروم مع تلج', 60],
                    ['عصير برتقال · Fresh Orange Juice', 'برتقال طازة متعصر دلوقتي', 65],
                    ['كابتشينو · Cappuccino', 'إسبريسو ولبن مخفوق', 65],
                    ['لاتيه · Latte', 'إسبريسو مع لبن ساخن', 70],
                    ['عصير مانجو · Mango Juice', 'مانجو طازة في موسمها', 75],
                    ['موهيتو · Mojito', 'ليمون ونعناع وصودا', 85],
                ],
            ],
        ];
    }
}
