<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = $this->getCitiesData();

        foreach ($cities as $cityData) {
            City::updateOrCreate(
                ['slug' => $cityData['slug']],
                $cityData
            );
        }

        $this->command->info('✅ تم إضافة ' . count($cities) . ' مدينة بنجاح');
    }

    /**
     * Get all Egyptian cities data
     */
    private function getCitiesData(): array
    {
        $egyptCities = [
            'القاهرة' => [
                'Cairo' => ['القاهرة', 'Cairo'],
                'Heliopolis' => ['مصر الجديدة', 'Heliopolis'],
                'Nasr City' => ['مدينة نصر', 'Nasr City'],
                'Maadi' => ['المعادي', 'Maadi'],
                'Zamalek' => ['الزمالك', 'Zamalek'],
                'New Cairo' => ['القاهرة الجديدة', 'New Cairo'],
                'Helwan' => ['حلوان', 'Helwan'],
                'Shubra' => ['شبرا', 'Shubra'],
                '6th of October' => ['السادس من أكتوبر', '6th of October'],
                'Sheikh Zayed' => ['الشيخ زايد', 'Sheikh Zayed'],
                'New Administrative Capital' => ['العاصمة الإدارية الجديدة', 'New Administrative Capital'],
                'Fifth Settlement' => ['التجمع الخامس', 'Fifth Settlement'],
                'Madinaty' => ['مدينتي', 'Madinaty'],
                'Shorouk City' => ['مدينة الشروق', 'Shorouk City'],
                'Mokattam' => ['المقطم', 'Mokattam'],
                'Katameya' => ['القطامية', 'Katameya'],
                'Future City' => ['مدينة المستقبل', 'Future City'],
                'Badr City' => ['مدينة بدر', 'Badr City'],
                'New Heliopolis' => ['هيليوبوليس الجديدة', 'New Heliopolis'],
            ],
            'الجيزة' => [
                'Giza' => ['الجيزة', 'Giza'],
                'Dokki' => ['الدقي', 'Dokki'],
                'Mohandessin' => ['المهندسين', 'Mohandessin'],
                'Agouza' => ['العجوزة', 'Agouza'],
                'Faisal' => ['فيصل', 'Faisal'],
                'Haram' => ['الهرم', 'Haram'],
                'October Gardens' => ['حدائق الأهرام', 'October Gardens'],
                'Smart Village' => ['القرية الذكية', 'Smart Village'],
                'October Gardens 2' => ['حدائق اكتوبر', 'October Gardens 2'],
                'New Sphinx' => ['سفنكس الجديدة', 'New Sphinx'],
            ],
            'الإسكندرية' => [
                'Alexandria' => ['الإسكندرية', 'Alexandria'],
                'Smouha' => ['سموحة', 'Smouha'],
                'Sidi Gaber' => ['سيدي جابر', 'Sidi Gaber'],
                'Miami' => ['ميامي', 'Miami'],
                'Stanley' => ['ستانلي', 'Stanley'],
                'Montaza' => ['المنتزه', 'Montaza'],
                'Sidi Bishr' => ['سيدي بشر', 'Sidi Bishr'],
                'Borg El Arab' => ['برج العرب', 'Borg El Arab'],
            ],
            'الدقهلية' => [
                'Mansoura' => ['المنصورة', 'Mansoura'],
                'Talkha' => ['طلخا', 'Talkha'],
                'Mit Ghamr' => ['ميت غمر', 'Mit Ghamr'],
                'Belqas' => ['بلقاس', 'Belqas'],
                'Dekernes' => ['دكرنس', 'Dekernes'],
                'Aga' => ['أجا', 'Aga'],
                'Sherbin' => ['شربين', 'Sherbin'],
                'Gamasa' => ['جمصة', 'Gamasa'],
                'New Mansoura' => ['نيو منصورة', 'New Mansoura'],
            ],
            'الشرقية' => [
                'Zagazig' => ['الزقازيق', 'Zagazig'],
                '10th of Ramadan' => ['العاشر من رمضان', '10th of Ramadan'],
                'Belbeis' => ['بلبيس', 'Belbeis'],
                'Abu Hammad' => ['أبو حماد', 'Abu Hammad'],
                'Faqous' => ['فاقوس', 'Faqous'],
                'Hehya' => ['ههيا', 'Hehya'],
                'Abu Kabir' => ['أبو كبير', 'Abu Kabir'],
            ],
            'البحيرة' => [
                'Damanhour' => ['دمنهور', 'Damanhour'],
                'Kafr El Dawwar' => ['كفر الدوار', 'Kafr El Dawwar'],
                'Rosetta' => ['رشيد', 'Rosetta'],
                'Edku' => ['إدكو', 'Edku'],
                'Abu Hummus' => ['أبو حمص', 'Abu Hummus'],
            ],
            'الغربية' => [
                'Tanta' => ['طنطا', 'Tanta'],
                'Mahalla El Kubra' => ['المحلة الكبرى', 'Mahalla El Kubra'],
                'Kafr El Zayat' => ['كفر الزيات', 'Kafr El Zayat'],
                'Samanoud' => ['سمنود', 'Samanoud'],
                'Zefta' => ['زفتى', 'Zefta'],
            ],
            'المنوفية' => [
                'Shibin El Kom' => ['شبين الكوم', 'Shibin El Kom'],
                'Menouf' => ['منوف', 'Menouf'],
                'Ashmoun' => ['أشمون', 'Ashmoun'],
                'Quesna' => ['قويسنا', 'Quesna'],
                'Tala' => ['تلا', 'Tala'],
                'Sadat City' => ['مدينة السادات', 'Sadat City'],
            ],
            'القليوبية' => [
                'Banha' => ['بنها', 'Banha'],
                'Qalyub' => ['قليوب', 'Qalyub'],
                'Shubra El Kheima' => ['شبرا الخيمة', 'Shubra El Kheima'],
                'Qaha' => ['قها', 'Qaha'],
                'Obour City' => ['مدينة العبور', 'Obour City'],
                'Khanka' => ['الخانكة', 'Khanka'],
            ],
            'كفر الشيخ' => [
                'Kafr El Sheikh' => ['كفر الشيخ', 'Kafr El Sheikh'],
                'Desouk' => ['دسوق', 'Desouk'],
                'Baltim' => ['بلطيم', 'Baltim'],
                'Metobas' => ['مطوبس', 'Metobas'],
                'Fuwwah' => ['فوه', 'Fuwwah'],
            ],
            'دمياط' => [
                'Damietta' => ['دمياط', 'Damietta'],
                'New Damietta' => ['دمياط الجديدة', 'New Damietta'],
                'Ras El Bar' => ['رأس البر', 'Ras El Bar'],
                'Faraskur' => ['فارسكور', 'Faraskur'],
            ],
            'بورسعيد' => [
                'Port Said' => ['بورسعيد', 'Port Said'],
                'Port Fouad' => ['بور فؤاد', 'Port Fouad'],
            ],
            'الإسماعيلية' => [
                'Ismailia' => ['الإسماعيلية', 'Ismailia'],
                'Fayed' => ['فايد', 'Fayed'],
                'Abu Suwair' => ['أبو صوير', 'Abu Suwair'],
            ],
            'السويس' => [
                'Suez' => ['السويس', 'Suez'],
                'Ataka' => ['عتاقة', 'Ataka'],
                'Ain Sokhna' => ['العين السخنة', 'Ain Sokhna'],
            ],
            'شمال سيناء' => [
                'Arish' => ['العريش', 'Arish'],
                'Sheikh Zuweid' => ['الشيخ زويد', 'Sheikh Zuweid'],
                'Rafah' => ['رفح', 'Rafah'],
                'Bir al-Abed' => ['بئر العبد', 'Bir al-Abed'],
            ],
            'جنوب سيناء' => [
                'Sharm El Sheikh' => ['شرم الشيخ', 'Sharm El Sheikh'],
                'Dahab' => ['دهب', 'Dahab'],
                'Nuweiba' => ['نويبع', 'Nuweiba'],
                'Taba' => ['طابا', 'Taba'],
                'Saint Catherine' => ['سانت كاترين', 'Saint Catherine'],
                'Ras Sidr' => ['رأس سدر', 'Ras Sidr'],
                'Sahl Hasheesh' => ['سهل حشيش', 'Sahl Hasheesh'],
            ],
            'المنيا' => [
                'Minya' => ['المنيا', 'Minya'],
                'Mallawi' => ['ملوي', 'Mallawi'],
                'Samalut' => ['سمالوط', 'Samalut'],
                'Beni Mazar' => ['بني مزار', 'Beni Mazar'],
                'Matay' => ['مطاي', 'Matay'],
            ],
            'الفيوم' => [
                'Fayoum' => ['الفيوم', 'Fayoum'],
                'Ibshaway' => ['إبشواي', 'Ibshaway'],
                'Tamiya' => ['طامية', 'Tamiya'],
                'Sinnuris' => ['سنورس', 'Sinnuris'],
            ],
            'بني سويف' => [
                'Beni Suef' => ['بني سويف', 'Beni Suef'],
                'Fashn' => ['الفشن', 'Fashn'],
                'Nasser' => ['ناصر', 'Nasser'],
                'Beba' => ['ببا', 'Beba'],
            ],
            'أسيوط' => [
                'Asyut' => ['أسيوط', 'Asyut'],
                'Abnub' => ['أبنوب', 'Abnub'],
                'Manfalut' => ['منفلوط', 'Manfalut'],
                'Abou Tig' => ['أبو تيج', 'Abou Tig'],
            ],
            'سوهاج' => [
                'Sohag' => ['سوهاج', 'Sohag'],
                'Akhmim' => ['أخميم', 'Akhmim'],
                'Girga' => ['جرجا', 'Girga'],
                'Dar El Salam' => ['دار السلام', 'Dar El Salam'],
            ],
            'قنا' => [
                'Qena' => ['قنا', 'Qena'],
                'Nag Hammadi' => ['نجع حمادي', 'Nag Hammadi'],
                'Qus' => ['قوص', 'Qus'],
                'Dishna' => ['دشنا', 'Dishna'],
            ],
            'الأقصر' => [
                'Luxor' => ['الأقصر', 'Luxor'],
                'Esna' => ['إسنا', 'Esna'],
                'Armant' => ['أرمنت', 'Armant'],
            ],
            'أسوان' => [
                'Aswan' => ['أسوان', 'Aswan'],
                'Kom Ombo' => ['كوم أمبو', 'Kom Ombo'],
                'Edfu' => ['إدفو', 'Edfu'],
                'Daraw' => ['دراو', 'Daraw'],
            ],
            'البحر الأحمر' => [
                'Hurghada' => ['الغردقة', 'Hurghada'],
                'Safaga' => ['سفاجا', 'Safaga'],
                'Marsa Alam' => ['مرسى علم', 'Marsa Alam'],
                'Qusair' => ['القصير', 'Qusair'],
                'El Gouna' => ['الجونة', 'El Gouna'],
                'Soma Bay' => ['سوما باي', 'Soma Bay'],
                'Makadi Bay' => ['خليج مكادي', 'Makadi Bay'],
            ],
            'الوادي الجديد' => [
                'Kharga' => ['الخارجة', 'Kharga'],
                'Dakhla' => ['الداخلة', 'Dakhla'],
                'Farafra' => ['الفرافرة', 'Farafra'],
                'Baris' => ['باريس', 'Baris'],
            ],
            'مطروح' => [
                'Marsa Matrouh' => ['مرسى مطروح', 'Marsa Matrouh'],
                'Alamein' => ['العلمين', 'Alamein'],
                'Sidi Barrani' => ['سيدي براني', 'Sidi Barrani'],
                'Siwa' => ['سيوة', 'Siwa'],
                'North Coast' => ['الساحل الشمالي', 'North Coast'],
                'Sidi Abdel Rahman' => ['سيدي عبد الرحمن', 'Sidi Abdel Rahman'],
                'Ras El Hekma' => ['رأس الحكمة', 'Ras El Hekma'],
                'Fouka Bay' => ['فوكا باي', 'Fouka Bay'],
                'Hacienda Bay' => ['هاسيندا باي', 'Hacienda Bay'],
                'Ghazala Bay' => ['غزالة باي', 'Ghazala Bay'],
                'El Dabaa' => ['الضبعة', 'El Dabaa'],
            ],
        ];

        $result = [];
        foreach ($egyptCities as $governorateAr => $cities) {
            foreach ($cities as $nameEn => $names) {
                $nameAr = $names[0];
                $name = $nameAr;

                $result[] = [
                    'name' => $name,
                    'name_ar' => $nameAr,
                    'name_en' => $nameEn,
                    'slug' => Str::slug($name),
                    'governorate' => $nameEn,
                    'governorate_ar' => $governorateAr,
                ];
            }
        }

        return $result;
    }
}
