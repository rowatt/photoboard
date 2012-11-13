# Require any additional compass plugins here.
Dir.chdir('/Library/Ruby/Gems/1.8/gems/bootstrap-sass-2.1.0.0/lib/')
require "bootstrap-sass.rb"

live = true

# Set this to the root of your project when deployed:
http_path = "/"
css_dir = "css"
sass_dir = "sass"
images_dir = "img"
javascripts_dir = "js"

# To enable relative paths to assets via compass helper functions. Uncomment:
# relative_assets = true

color_output = false

if live
	output_style = :compressed
	line_comments = false
	environment = :production
else
	output_style = :expanded
	sass_options = {:debug_info => true}
	line_comments = true
	environment = :development
end

# If you prefer the indented syntax, you might want to regenerate this
# project again passing --syntax sass, or you can uncomment this:
# preferred_syntax = :sass
# and then run:
# sass-convert -R --from scss --to sass sass scss && rm -rf sass && mv scss sass

=begin
require 'fileutils'
on_stylesheet_saved do |file|
  if File.exists?(file) && File.basename(file) == "style.css"
    FileUtils.mv(file, File.dirname(file) + "/../" + File.basename(file))
  end
end
=end