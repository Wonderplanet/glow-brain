require 'yaml'
require 'erb'
require 'fileutils'
require 'active_support/core_ext/string/inflections'

class Generator
  def initialize(template_path)
    @template_path = template_path
  end

  def generate(schema_path, output_root_path)
    template = File.read(@template_path)
    schema_data = File.read(schema_path)
    yaml = YAML.load(schema_data)

    return if yaml[yaml_element_name].nil?

    yaml[yaml_element_name].each do |base_schema_yaml|
      next if filter base_schema_yaml["name"]
      next if base_schema_yaml["server_only"]

      # 出力対象が複数の場合に対応
      #  例: apiにあるAPIごとにファイルを出力したい場合など
      #  デフォルトではschema_yamlだけの配列が返されるため、動作は変わらない
      target_elements(base_schema_yaml).each do |schema_yaml|
        file_path = File.join(output_root_path, file_name(base_schema_yaml["name"], schema_yaml))
        create_directory(file_path)

        # ファイルを上書きするかどうか
        #  ファイルが存在していて、上書きしない場合はスキップ
        next if File.exist?(file_path) && !is_file_overwrite?

        schema = translate(schema_yaml, base_schema_yaml)
        next if schema.nil?
        File.open(file_path, mode = "w:utf-8") do |cs|
          cs.write(ERB.new(template, nil, '-').result(binding))
        end
      end
    end
  end

  # enum型の事前読み込み
  #  判定に使用するため、クラス変数に格納する
  #  schema_pathes ... enum型の事前読み込みを行うyamlのパス
  def self.load_enum_types(schema_pathes)
    @@enum_types = []
    schema_pathes.each do |schema_path|
      schema_data = File.read(schema_path)
      yaml = YAML.load(schema_data)
      next if yaml["enum"].nil?

      yaml["enum"].each do |enum|
        @@enum_types.push(enum["name"])
      end
    end
  end

  private

  def create_directory(file_path)
      directory_path = File.dirname(file_path)
      FileUtils.mkdir_p(directory_path) unless Dir.exist?(directory_path)
  end

  # enum型かどうかの判定
  #  $enum_typesはbuild.rbで事前に読み込まれている
  def is_enum_type(type)
    return @@enum_types.include?(type)
  end

  ########################################################
  # 各ジェネレーターで実装する
  ########################################################

  # 対象にするyamlの要素名
  # data, apiなど一番先頭の要素名を記載
  def yaml_element_name
    raise NotImplementedError.new("You must implement #{self.class}##{__method__}")
  end

  # 出力するファイル名
  # name ... yaml[yaml_element_name]のname要素
  # elemnts ... 処理しているyamlのエレメント
  def file_name(name, elements)
    raise NotImplementedError.new("You must implement #{self.class}##{__method__}")
  end

  # ファイルが存在していたときに上書きするかどうか
  # デフォルトは上書きする
  #  true ... 上書きする
  #  false ... 上書きしない
  def is_file_overwrite?
    return true
  end

  # 対象にするyamlのエレメントを抽出
  # ファイル出力のループを、yaml_element_nameの下にあるものを対象にしたい場合、ここで検出する
  # デフォルトではyaml_elementsを1回だけ処理したいので、それだけの配列を作って返す
  def target_elements(yaml_elements)
    return [yaml_elements]
  end

  # ファイルを置き換えるためのデータを生成
  # yamlのパラメータに補正をかける場合はここで行う
  #  scheam_yaml ... 処理対象のyaml
  #  base_schema_yaml ... 処理の大元になっているyaml (yaml_element_nameで取得したyaml)
  #                       target_elementsで処理をわけていない場合、schema_yamltと同じものが渡される
  def translate(schema_yaml, base_schema_yaml)
    raise NotImplementedError.new("You must implement #{self.class}##{__method__}")
  end

  # 名前でフィルタリングする
  # trueだった場合にスキップする
  def filter(name)
    false;
  end

  ########################################################
  # サーバー処理向けユーティリティ
  ########################################################

  # クラスに付随するgetterのメソッド名を作成
  # getName()のような形式
  #  name ... パラメータ名
  def server_method_getter_name(name)
    method_name = name.dup
    method_name[0] = method_name[0].upcase
    return "get#{method_name}"
  end

  # クラスに付随するsetterのメソッド名を作成
  # setName()のような形式
  #  name ... パラメータ名
  def server_method_setter_name(name)
    method_name = name.dup
    method_name[0] = method_name[0].upcase
    return "set#{method_name}"
  end  

  # リクエスト/レスポンスのメソッド記述用の型変換
  def server_method_type(type)
    # 最後が[]の場合はCollectionとして判定
    if type.match?(/\[\]$/)
      return "\\Illuminate\\Support\\Collection<" + server_cast_type(type.gsub(/\[\]$/, "")) + ">"
    end

    # その他はcast_typeに任せる
    return server_cast_type(type)
  end

  # サーバーのパラメータ名を作成する
  # 先頭を小文字にする
  #   name ... パラメータ名
  def server_param_name(name)
    param_name = name.dup
    param_name[0] = param_name[0].downcase
    return param_name
  end

  # float型か
  # type ... 型
  def server_is_float(type)
    if type === "float"
      return true
    end
    return false
  end

  # リクエスト/レスポンスの型変換
  def server_cast_type(type)
    type = type.dup
    
    # 最後が[]の場合は配列として判定
    if type.match?(/\[\]$/)
      return server_cast_type(type.gsub(/\[\]$/, "")) + "[]"
    end

    # 末尾「?」はnull許可になるため、「|null」を定義に追加する
    # それを抜いた状態で判定
    is_nullable = delete_nullable_chr(type)
    
    # typeから型を判断
    case type
    when "int"
      cast_type_string = "int"
    when "long"
      cast_type_string = "int"
    when "string"
      # stringの場合、null許可をする
      is_nullable = true
      cast_type_string = "string"
    when "bool"
      cast_type_string = "bool"
    when "float"
      # float型はstring型として扱う
      is_nullable = true
      cast_type_string = "string"
    when "DateTimeOffset"
      # DateTimeOffsetはクライアントで日付を示すデータ定義のため、
      cast_type_string = "string"
    when "decimal"
      # decimal型はstring型として扱う
      cast_type_string = "string"
    else
      # enumかどうかで型を変更する
      if is_enum_type(type)
        cast_type_string = "\\App\\Http\\Enums\\Api\\#{type}"
      else
        # その他のクラスの場合、指定なくてもnull許可をする
        is_nullable = true
        cast_type_string = "\\App\\Http\\Structs\\Api\\#{type}"
      end
    end

    # null許可されている場合は「|null」を定義に追加
    if is_nullable
      return append_null(cast_type_string)
    else
      return cast_type_string
    end

  end

  # サーバーリソースファイルに記述する、列の型指定を、ymlのタイプから変換
  def cast_data_type_yml_to_server_resource(s)
    is_nullable = delete_nullable_chr(s)

    if s == "long"
      s = "int"
    elsif s != "int" && s != "string"
      s = "string"
    end

    if is_nullable
      s = append_null(s)
    end
    
    return s
  end

  # キャメルケースをスネークケースに変換
  def camel_to_snake(str)
    str.gsub(/([a-z])([A-Z])/,'\1_\2').downcase
  end

  # 末尾の「?」を削除
  # 削除できたかを返す
  def delete_nullable_chr(str)
    deleted = false
    if str.match(/\?$/)
      deleted = true
      str.delete_suffix!("?")
    end
    return deleted
  end

  # 末尾に「|null」を付与
  def append_null(str)
    return str + "|null"
  end

end
