#!/usr/bin/ruby

require 'dotenv'
require 'optparse'
require 'fileutils'
require_relative './generator/client/data_generator.rb'
require_relative './generator/client/enum_generator.rb'
require_relative './generator/client/api_generator.rb'
require_relative './generator/client/const_generator.rb'
require_relative './generator/server/http_resource_generator.rb'
require_relative './generator/server/http_resource_base_generator.rb'
require_relative './generator/server/http_resource_mst_generator.rb'
require_relative './generator/server/http_resource_mst_i18n_generator.rb'
require_relative './generator/server/http_resource_opr_generator.rb'
require_relative './generator/server/http_resource_opr_i18n_generator.rb'
require_relative './generator/server/http_request_generator.rb'
require_relative './generator/server/http_struct_generator.rb'
require_relative './generator/server/http_struct_base_generator.rb'
require_relative './generator/server/http_struct_othres_generator.rb'
require_relative './generator/server/http_enum_generator.rb'

ActiveSupport::Inflector.inflections(:en) do |inflect|
#   inflect.plural /^(ox)$/i, '\1en'
#   inflect.singular /^(ox)en/i, '\1'
#   inflect.irregular 'person', 'people'
#   inflect.uncountable %w( fish sheep )
  inflect.irregular 'bonus', 'bonuses'
  inflect.irregular 'live', 'lives'
end

build_client = true
build_server = true

opt = OptionParser.new
opt.on('-c', "generate client code only") { build_server = false}
opt.on('-s', "generate server code only") { build_client = false}
opt.parse(ARGV)

Dotenv.load
#Dir.chdir(File.expand_path(File.dirname(__FILE__)))

schema_base_dir = ENV["SCHEMA_BASE_DIR"]
template_base_dir = ENV["TEMPLATE_BASE_DIR"]

data_output_base_dir = ENV["DATA_OUTPUT_BASE_DIR"]
enum_output_base_dir = ENV["ENUM_OUTPUT_BASE_DIR"]
api_output_base_dir = ENV["API_OUTPUT_BASE_DIR"]
# const_output_base_dir = ENV["CONST_OUTPUT_BASE_DIR"]

http_resource_output_base_dir = ENV["HTTP_RESOURCE_OUTPUT_BASE_DIR"]
http_resource_mst_output_base_dir = ENV["HTTP_RESOURCE_OUTPUT_BASE_DIR"] + "/Masterdata"
http_resource_mst_i18n_output_base_dir = ENV["HTTP_RESOURCE_OUTPUT_BASE_DIR"] + "/Masteri18ndata"
http_resource_opr_output_base_dir = ENV["HTTP_RESOURCE_OUTPUT_BASE_DIR"] + "/Operationdata"
http_resource_opr_i18n_output_base_dir = ENV["HTTP_RESOURCE_OUTPUT_BASE_DIR"] + "/Operationi18ndata"
http_request_output_base_dir = ENV["HTTP_REQUEST_OUTPUT_BASE_DIR"]
http_struct_output_base_dir = ENV["HTTP_STRUCT_OUTPUT_BASE_DIR"]
http_enum_output_base_dir = ENV["HTTP_ENUM_OUTPUT_BASE_DIR"]

# Clean
if build_client
  FileUtils.rm(Dir.glob(data_output_base_dir + "/**/*.cs"))
  FileUtils.rm(Dir.glob(enum_output_base_dir + "/**/*.cs"))
  FileUtils.rm(Dir.glob(api_output_base_dir + "/**/*.cs"))
  # FileUtils.rm(Dir.glob(const_output_base_dir + "/**/*.cs"))
end

if build_server
  FileUtils.rm(Dir.glob(http_resource_output_base_dir + "/**/Base/*.php")) # left Concerns
  FileUtils.rm(Dir.glob(http_resource_mst_output_base_dir + "/*.php")) # left Concerns
  FileUtils.rm(Dir.glob(http_resource_mst_i18n_output_base_dir + "/*.php")) # left Concerns
  FileUtils.rm(Dir.glob(http_resource_opr_output_base_dir + "/*.php")) # left Concerns
  FileUtils.rm(Dir.glob(http_resource_opr_i18n_output_base_dir + "/*.php")) # left Concerns
  # FileUtils.rm(Dir.glob(http_request_output_base_dir + "/**/*.php")) # left Concerns
  # FileUtils.rm(Dir.glob(http_struct_output_base_dir + "/Base/*.php")) # left Concerns
  # FileUtils.rm(Dir.glob(http_enum_output_base_dir + "/*.php"))
end

# Generate
data_generator = DataGenerator.new(template_base_dir + "/data.cs.erb")
enum_generator = EnumGenerator.new(template_base_dir + "/enum.cs.erb")
api_generator = ApiGenerator.new(template_base_dir + "/api.cs.erb")
# const_generator = ConstGenerator.new(template_base_dir + "/const.cs.erb")

# リソースクラスの出力
http_resource_generator = HttpResourceGenerator.new(template_base_dir + "/http_resource.php.erb")
http_resource_base_generator = HttpResourceBaseGenerator.new(template_base_dir + "/http_resource_base.php.erb")
http_resource_mst_generator = HttpResourceMstGenerator.new(template_base_dir + "/http_resource_mst.php.erb")
http_resource_mst_i18n_generator = HttpResourceMstI18nGenerator.new(template_base_dir + "/http_resource_mst.php.erb")
http_resource_opr_generator = HttpResourceOprGenerator.new(template_base_dir + "/http_resource_opr.php.erb")
http_resource_opr_i18n_generator = HttpResourceOprI18nGenerator.new(template_base_dir + "/http_resource_opr.php.erb")

# # リクエストクラスの出力
# http_request_generator = HttpRequestGenerator.new(template_base_dir + "/http_request.php.erb")

# # 構造体クラスの出力
# http_struct_generator = HttpStructGenerator.new(template_base_dir + "/http_struct.php.erb")
# http_struct_base_generator = HttpStructBaseGenerator.new(template_base_dir + "/http_struct_base.php.erb")
# http_struct_othres_generator = HttpStructOthresGenerator.new(template_base_dir)

# # Enumの出力
# http_enum_generator = HttpEnumGenerator.new(template_base_dir + "/http_enum.php.erb")

schema_pathes = Dir.glob(schema_base_dir + "/**/*.yml")

# enumタイプの先読み
# phpはenumの場所が異なるため、enumとなるタイプを先に取得しておく
# グローバル変数に格納しておき、タイプの判断などで使用する
puts "load enum types"
Generator.load_enum_types(schema_pathes)

# 各スキーマの出力
schema_pathes.each do |schema_path|
  puts "generate: " + schema_path

  # generate client code
  if build_client
    data_generator.generate(schema_path, data_output_base_dir)
    enum_generator.generate(schema_path, enum_output_base_dir)
    api_generator.generate(schema_path, api_output_base_dir)
    # const_generator.generate(schema_path, const_output_base_dir)
  end

  # generate server code
  if build_server
    # リソースクラスの出力
    # http_resource_generator.generate(schema_path, http_resource_output_base_dir)
    # http_resource_base_generator.generate(schema_path, http_resource_output_base_dir)

    # マスタデータのリソースクラスの出力
    http_resource_mst_generator.generate(schema_path, http_resource_mst_output_base_dir)
    http_resource_mst_i18n_generator.generate(schema_path, http_resource_mst_i18n_output_base_dir)
    http_resource_opr_generator.generate(schema_path, http_resource_opr_output_base_dir)
    http_resource_opr_i18n_generator.generate(schema_path, http_resource_opr_i18n_output_base_dir)

    # # リクエストクラスの出力
    # http_request_generator.generate(schema_path, http_request_output_base_dir)

    # # 構造体クラスの出力
    # http_struct_generator.generate(schema_path, http_struct_output_base_dir)
    # http_struct_base_generator.generate(schema_path, http_struct_output_base_dir)

    # # Enumの出力
    # http_enum_generator.generate(schema_path, http_enum_output_base_dir)
  end
end

# # 特殊ファイルの出力
# if build_server
#   http_struct_othres_generator.generate("", http_struct_output_base_dir)
# end
