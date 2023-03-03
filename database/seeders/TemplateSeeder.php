<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            ['id' => 1, 'name' => 'Article Generator', 'icon' => '<i class="fa-solid fa-file-lines main-icon"></i>', 'description' => 'Turn a title and outline text into a fully complete high quality article within seconds', 'template_code' => 'KPAQQ', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'article-generator'],
            ['id' => 2, 'name' => 'Content Rewriter', 'icon' => '<i class="fa-solid fa-square-check main-icon"></i>', 'description' => 'Take a piece of content and rewrite it to make it more interesting, creative, and engaging', 'template_code' => 'WCZGL', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'content-rewriter'],
            ['id' => 3, 'name' => 'Paragraph Generator', 'icon' => '<i class="fa-solid fa-line-columns main-icon"></i>', 'description' => 'Generate paragraphs about any topic including a keyword and in a specific tone of voice', 'template_code' => 'JXRZB', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'paragraph-generator'],
            ['id' => 4, 'name' => 'Talking Points', 'icon' => '<i class="fa-solid fa-list-check main-icon"></i>', 'description' => 'Write short, simple and informative points for the subheadings of your article', 'template_code' => 'VFWSQ', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'talking-points'],
            ['id' => 5, 'name' => 'Pros & Cons', 'icon' => '<i class="fa-solid fa-code-compare main-icon"></i>', 'description' => 'Write the pros and cons of a product, service or website for your blog article', 'template_code' => 'OPYAB', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'pros-and-cons'],
            ['id' => 6, 'name' => 'Blog Titles', 'icon' => '<i class="fa-solid fa-message-text blog-icon"></i>', 'description' => 'Nobody wants to read boring blog titles, generate catchy blog titles with this tool', 'template_code' => 'WGKYP', 'status' => true, 'professional' => false, 'group' => 'blog', 'slug' => 'blog-titles'],
            ['id' => 7, 'name' => 'Blog Section', 'icon' => '<i class="fa-solid fa-message-lines blog-icon"></i>', 'description' => 'Write a full blog section (few paragraphs) about a subheading of your article', 'template_code' => 'EEKZF', 'status' => true, 'professional' => false, 'group' => 'blog', 'slug' => 'blog-section'],
            ['id' => 8, 'name' => 'Blog Ideas', 'icon' => '<i class="fa-solid fa-message-dots blog-icon"></i>', 'description' => 'The perfect tool to start writing great articles. Generate creative ideas for your next post', 'template_code' => 'KDGOX', 'status' => true, 'professional' => false, 'group' => 'blog', 'slug' => 'blog-ideas'],
            ['id' => 9, 'name' => 'Blog Intros', 'icon' => '<i class="fa-solid fa-message-exclamation blog-icon"></i>', 'description' => 'Write an intro that will entice your visitors to read more about your article', 'template_code' => 'TZTYR', 'status' => true, 'professional' => false, 'group' => 'blog', 'slug' => 'blog-intros'],
            ['id' => 10, 'name' => 'Blog Conclusion', 'icon' => '<i class="fa-solid fa-message-check blog-icon"></i>', 'description' => 'End your blog articles with an engaging conclusion paragraph', 'template_code' => 'ZGUKM', 'status' => true, 'professional' => false, 'group' => 'blog', 'slug' => 'blog-conclusion'],
            ['id' => 11, 'name' => 'Summarize Text', 'icon' => '<i class="fa-solid fa-file-contract main-icon"></i>', 'description' => 'Summmarize any text in a short and easy to understand concise way', 'template_code' => 'OMMEI', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'summarize-text'],
            ['id' => 12, 'name' => 'Product Description', 'icon' => '<i class="fa-solid fa-list-check main-icon"></i>', 'description' => 'Write the description about your product and why it worth it', 'template_code' => 'HXLNA', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'product-description'],
            ['id' => 13, 'name' => 'Startup Name Generator', 'icon' => '<i class="fa-solid fa-lightbulb-on main-icon"></i>', 'description' => 'Generate cool, creative, and catchy names for your startup in seconds', 'template_code' => 'DJSVM', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'startup-name-generator'],
            ['id' => 14, 'name' => 'Product Name Generator', 'icon' => '<i class="fa-solid fa-box-circle-check main-icon"></i>', 'description' => 'Create creative product names from examples words', 'template_code' => 'IXKBE', 'status' => true, 'professional' => false, 'group' => 'text', 'slug' => 'product-name-generator'],
            ['id' => 15, 'name' => 'Meta Description', 'icon' => '<i class="fa-solid fa-memo-circle-info web-icon"></i>', 'description' => 'Write SEO-optimized meta description based on a description', 'template_code' => 'JCDIK', 'status' => true, 'professional' => false, 'group' => 'web', 'slug' => 'meta-description'],
            ['id' => 16, 'name' => 'FAQs', 'icon' => '<i class="fa-solid fa-message-question web-icon"></i>', 'description' => 'Generate frequently asked questions based on your product description', 'template_code' => 'SZAUF', 'status' => true, 'professional' => false, 'group' => 'web', 'slug' => 'faqs'],
            ['id' => 17, 'name' => 'FAQ Answers', 'icon' => '<i class="fa-solid fa-messages-question web-icon"></i>', 'description' => 'Generate creative answers to questions (FAQs) about your business or website', 'template_code' => 'BFENK', 'status' => true, 'professional' => false, 'group' => 'web', 'slug' => 'faq-answers'],
            ['id' => 18, 'name' => 'Testimonials / Reviews', 'icon' => '<i class="fa-solid fa-star-sharp-half-stroke web-icon"></i>', 'description' => 'Add social proof to your website by generating user testimonials', 'template_code' => 'XLGPP', 'status' => true, 'professional' => false, 'group' => 'web', 'slug' => 'testimonials'],
            ['id' => 19, 'name' => 'Facebook Ads', 'icon' => '<i class="fa-brands fa-facebook social-icon"></i>', 'description' => 'Write Facebook ads that engage your audience and deliver a high conversion rate', 'template_code' => 'CTMNI', 'status' => true, 'professional' => false, 'group' => 'social', 'slug' => 'facebook-ads'],

        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(['id' => $template['id']], $template);
        }
    }
}
