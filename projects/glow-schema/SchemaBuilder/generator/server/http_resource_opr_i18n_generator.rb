require_relative './http_resource_opr_generator.rb'

# oprリソースのi18nを生成するクラス
# 出力先が違うため、クラスを分けている
class HttpResourceOprI18nGenerator < HttpResourceOprGenerator
  def translate(schema_yaml, base_schema_yaml)
    # 親クラスの処理結果を受け取る
    schema_yaml = super(schema_yaml, base_schema_yaml)

    # i18nとわけるため、namespaceを設定する
    schema_yaml["namespace"] = "Operationi18ndata"

    return schema_yaml
  end

  def filter(name)
    # 先頭がOprで、かつ末尾がI18nの場合に対象とする (falseを返す)
    return !(name.match?(/^Opr/) && name.match?(/I18n$/))
  end
end
