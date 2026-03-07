require_relative '../generator.rb'

# 各dataの構造体クラスで、yamlから生成しない特殊ファイルを出力する
#  そのためgenerateメソッドも変更する
class HttpStructOthresGenerator < Generator
  def generate(schema_path, output_root_path)
    # template_pathにある特定のファイルを処理する
    # 出力するファイルとテンプレートのパスを指定する
    outputs = [
      { file_name: "HeadOK.php", template_path: "/http_struct_head_ok.php.erb" },
      { file_name: "Base/BaseHeadOK.php", template_path: "/http_struct_base_head_ok.php.erb" },
    ]

    # ファイルの出力
    outputs.each do |output|
      template = File.read(@template_path + output[:template_path])

      file_path = File.join(output_root_path, output[:file_name])
      create_directory(file_path)
      File.open(file_path, mode = "w:utf-8") do |cs|
        cs.write(ERB.new(template, nil, '-').result(binding))
      end
    end
  end
end
