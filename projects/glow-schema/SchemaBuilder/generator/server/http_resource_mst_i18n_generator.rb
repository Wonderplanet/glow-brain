require_relative './http_resource_mst_generator.rb'

# mstリソースのi18nを生成するクラス
# 出力先が違うため、クラスを分けている
class HttpResourceMstI18nGenerator < HttpResourceMstGenerator
  def translate(schema_yaml, base_schema_yaml)
    # 親クラスの処理結果を受け取る
    schema_yaml = super(schema_yaml, base_schema_yaml)

    # i18nとわけるため、namespaceを設定する
    schema_yaml["namespace"] = "Masteri18ndata"

    return schema_yaml
  end

  def filter(name)
    # 先頭がMstで、かつ末尾がI18nの場合に対象とする (falseを返す)
    return !(name.match?(/^Mst/) && name.match?(/I18n$/))
  end
end
