require_relative '../generator.rb'

# 各enumの構造体クラスを出力する
# phpのenumは継承ができないため、そのまま出力する
#
# クライアント側の期待としては、enum型の文字列そのままを返して欲しい
# ただ現状のJSONパーサーの機構として、アッパー/ローワーキャメルの違いは吸収している
#
# その他の特殊対応が必要な場合は、クライアントで別途対応になる
# (たとえばZh-HantをZh_Hantに読み替えるなど)
class HttpEnumGenerator < Generator
  def yaml_element_name
    return "enum"
  end

  # enumはTypeそのままのファイル名で出力する
  def file_name(name, elements)
    return "#{name}.php"
  end

  def translate(schema_yaml, base_schema_yaml)
    return schema_yaml
  end

  # trueだった場合にスキップする
  def filter(name)
    # DBのテーブルを対象にしない
    name.match?(/^Mst/) || name.match?(/^Opr/)
  end
end
