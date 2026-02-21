require_relative '../generator.rb'

# 各dataの構造体クラスを出力する
#  Baseを継承した構造体クラスを出力する
#  各プロダクトが参照するのはこのクラスになる
class HttpStructGenerator < Generator
  def yaml_element_name
    return "data"
  end

  def file_name(name, elements)
    return "#{name}Data.php"
  end

  # Structはファイルが存在してたら上書きしない
  #   プロダクト側でカスタマイズされている可能性があるため
  def is_file_overwrite?
    return false
  end

  def translate(schema_yaml, base_schema_yaml)
    return schema_yaml
  end
end
